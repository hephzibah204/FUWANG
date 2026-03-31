<?php

namespace App\Services\VirtualAccounts;

use App\Models\BankDetail;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Providers\FlutterwaveVirtualAccountProvider;
use App\Services\VirtualAccounts\Providers\MonnifyVirtualAccountProvider;
use App\Services\VirtualAccounts\Providers\PalmpayVirtualAccountProvider;
use App\Services\VirtualAccounts\Providers\PaystackVirtualAccountProvider;
use App\Services\VirtualAccounts\Providers\PayvesselVirtualAccountProvider;
use App\Services\VirtualAccounts\Providers\VirtualAccountProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VirtualAccountService
{
    public const MAX_ACCOUNTS_PER_USER = 4;

    /**
     * @return array{status:bool, accounts:array<int,array<string,mixed>>, providers:array<string,mixed>}
     */
    public function ensureAccounts(User $user, bool $force = false): array
    {
        $providers = $this->providers();

        $providerStatus = [];

        DB::transaction(function () use ($user, $force, $providers, &$providerStatus) {
            if (!$force) {
                $this->importLegacyBankDetails($user);
            }

            $existing = VirtualAccount::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['active', 'pending'])
                ->lockForUpdate()
                ->get();

            $count = $existing->count();
            if ($count >= self::MAX_ACCOUNTS_PER_USER && !$force) {
                foreach ($providers as $provider) {
                    $providerStatus[$provider->name()] = ['ok' => true, 'skipped' => true, 'message' => 'Limit reached'];
                }
                return;
            }

            foreach ($providers as $provider) {
                if ($count >= self::MAX_ACCOUNTS_PER_USER) {
                    $providerStatus[$provider->name()] = ['ok' => true, 'skipped' => true, 'message' => 'Limit reached'];
                    continue;
                }

                if (!$provider->supportsVirtualAccounts()) {
                    $providerStatus[$provider->name()] = ['ok' => false, 'message' => 'Not configured or unsupported'];
                    continue;
                }

                $already = VirtualAccount::query()
                    ->where('user_id', $user->id)
                    ->where('gateway', $provider->name())
                    ->whereIn('status', ['active', 'pending'])
                    ->exists();

                if ($already && !$force) {
                    $providerStatus[$provider->name()] = ['ok' => true, 'skipped' => true];
                    continue;
                }

                if ($force) {
                    VirtualAccount::query()
                        ->where('user_id', $user->id)
                        ->where('gateway', $provider->name())
                        ->whereIn('status', ['active', 'pending'])
                        ->delete();
                }

                $providerStatus[$provider->name()] = $this->createAccount($user, $provider);
                $newCount = VirtualAccount::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['active', 'pending'])
                    ->count();
                $count = $newCount;
            }
        });

        return [
            'status' => true,
            'accounts' => $this->extractAccounts($user),
            'providers' => $providerStatus,
        ];
    }

    public function getAccountsForUser(User $user): Collection
    {
        return VirtualAccount::query()
            ->where('user_id', $user->id)
            ->orderByRaw("FIELD(status, 'active', 'pending', 'failed')")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function presentAccounts(User $user): array
    {
        return $this->extractAccounts($user);
    }

    private function createAccount(User $user, VirtualAccountProvider $provider): array
    {
        $audit = app(VirtualAccountAuditService::class);
        $audit->log(null, $user->id, $provider->name(), 'create_request', 'started');

        $result = $provider->create($user);
        if (!$result->ok) {
            $audit->log(null, $user->id, $provider->name(), 'create_failed', 'failed', $result->message);
            return ['ok' => false, 'message' => $result->message];
        }

        try {
            $account = VirtualAccount::create([
                'user_id' => $user->id,
                'gateway' => $provider->name(),
                'account_number' => $result->accountNumber,
                'bank_name' => $result->bankName,
                'account_name' => $result->accountName,
                'currency' => $result->currency ?: 'NGN',
                'status' => $result->status ?: 'pending',
                'reference' => $result->reference,
                'provider_customer_reference' => $result->providerCustomerReference,
                'provider_account_reference' => $result->providerAccountReference,
                'meta' => $result->meta,
                'activated_at' => ($result->status === 'active') ? now() : null,
                'last_synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $existing = VirtualAccount::query()
                ->where('user_id', $user->id)
                ->where('gateway', $provider->name())
                ->where('account_number', $result->accountNumber)
                ->first();

            if ($existing) {
                $account = $existing;
            } else {
                $audit->log(null, $user->id, $provider->name(), 'create_failed', 'failed', 'Unable to persist virtual account');
                return ['ok' => false, 'message' => 'Unable to store virtual account'];
            }
        }

        $audit->log($account, $user->id, $provider->name(), 'create_success', 'succeeded', null, [
            'account_number_last4' => substr((string) $account->account_number, -4),
            'bank_name' => $account->bank_name,
            'status' => $account->status,
        ]);

        return ['ok' => true];
    }

    private function extractAccounts(User $user): array
    {
        $accounts = $this->getAccountsForUser($user);

        $map = [
            'paystack' => ['group' => 'paystack', 'label' => 'Paystack'],
            'flutterwave' => ['group' => 'flutterwave', 'label' => 'Flutterwave'],
            'payvessel' => ['group' => 'payvessel', 'label' => 'PayVessel'],
            'monnify' => ['group' => 'monnify', 'label' => 'Monnify'],
            'palmpay' => ['group' => 'palmpay', 'label' => 'PalmPay'],
        ];

        return $accounts->map(function (VirtualAccount $a) use ($map) {
            $meta = $map[$a->gateway] ?? ['group' => $a->gateway, 'label' => ucfirst($a->gateway)];
            return [
                'gateway' => $a->gateway,
                'provider_group' => $meta['group'],
                'provider_group_label' => $meta['label'],
                'bank' => $a->bank_name ?: $meta['label'],
                'accountNumber' => (string) $a->account_number,
                'accountName' => $a->account_name,
                'status' => $a->status,
            ];
        })->values()->all();
    }

    private function importLegacyBankDetails(User $user): void
    {
        $hasAny = VirtualAccount::query()->where('user_id', $user->id)->exists();
        if ($hasAny) {
            return;
        }

        $detail = BankDetail::query()->where('email', $user->email)->first();
        if (!$detail) {
            return;
        }

        $candidates = [];
        if ($detail->psb9) {
            $candidates[] = ['gateway' => 'payvessel', 'bank' => '9PSB / PayVessel', 'account' => (string) $detail->psb9];
        }
        if ($detail->palmpay) {
            $candidates[] = ['gateway' => 'palmpay', 'bank' => 'PalmPay', 'account' => (string) $detail->palmpay];
        }

        $monnifyBanks = [
            ['bank' => 'GTBank', 'account' => $detail->GTBank_account],
            ['bank' => 'Moniepoint', 'account' => $detail->Moniepoint_account],
            ['bank' => 'Wema', 'account' => $detail->Wema_account],
            ['bank' => 'Sterling', 'account' => $detail->Sterling_account],
        ];
        foreach ($monnifyBanks as $row) {
            if (!empty($row['account'])) {
                $candidates[] = ['gateway' => 'monnify', 'bank' => $row['bank'], 'account' => (string) $row['account']];
                break;
            }
        }

        $audit = app(VirtualAccountAuditService::class);
        $slots = self::MAX_ACCOUNTS_PER_USER;
        foreach ($candidates as $c) {
            if ($slots <= 0) {
                break;
            }

            $exists = VirtualAccount::query()
                ->where('user_id', $user->id)
                ->where('gateway', $c['gateway'])
                ->where('account_number', $c['account'])
                ->exists();
            if ($exists) {
                continue;
            }

            $va = VirtualAccount::create([
                'user_id' => $user->id,
                'gateway' => $c['gateway'],
                'account_number' => $c['account'],
                'bank_name' => $c['bank'],
                'account_name' => $detail->account_name,
                'currency' => $detail->currency_code ?: 'NGN',
                'status' => 'active',
                'activated_at' => now(),
                'last_synced_at' => now(),
                'meta' => ['source' => 'bank_details'],
            ]);

            $audit->log($va, $user->id, $va->gateway, 'import_legacy', 'succeeded', null, [
                'bank_name' => $va->bank_name,
            ]);

            $slots--;
        }
    }

    /**
     * @return array<int,VirtualAccountProvider>
     */
    private function providers(): array
    {
        return [
            app(PayvesselVirtualAccountProvider::class),
            app(MonnifyVirtualAccountProvider::class),
            app(PalmpayVirtualAccountProvider::class),
            app(PaystackVirtualAccountProvider::class),
            app(FlutterwaveVirtualAccountProvider::class),
        ];
    }
}
