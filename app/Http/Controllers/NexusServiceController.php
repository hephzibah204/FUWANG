<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AccountBalance;
use App\Models\Transaction;
use App\Models\ApiCenter;
use App\Models\NotaryRequest;
use App\Models\LogisticsRequest;
use App\Models\VirtualCard;
use App\Models\AuctionBid;
use App\Models\EventTicket;
use App\Models\ServiceInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Models\LegalDocument;
use App\Services\WalletService;
use App\Services\VirtualCardService;
use App\Services\LegalCatalog\LegalPricingService;
use App\Models\SystemSetting;

class NexusServiceController extends Controller
{
    // ─────────────────────────────────────────────
    //  Shared helper: deduct balance & log transaction
    // ─────────────────────────────────────────────
    private function deductAndLog(
        string $orderType,
        float  $amount,
        array  $extra = []
    ): array {
        $user = Auth::user();
        $wallet = app(WalletService::class);

        $debit = $wallet->debit($user, $amount, $orderType);
        if (!$debit['ok']) {
            return ['ok' => false, 'message' => $debit['message']];
        }

        $tx = $debit['transaction'];
        if (array_key_exists('transaction_id', $extra)) {
            $tx->transaction_id = $extra['transaction_id'];
            unset($extra['transaction_id']);
        }
        if (!empty($extra)) {
            foreach ($extra as $k => $v) {
                $tx->{$k} = $v;
            }
        }
        $tx->status = 'success';
        $tx->save();

        return ['ok' => true, 'txId' => $tx->transaction_id, 'balance' => $debit['newBalance']];
    }

    // ─────────────────────────────────────────────
    //  AI LEGAL HUB
    // ─────────────────────────────────────────────
    public function legalHub()
    {
        $docTypes = DB::table('notary_settings')->get()->groupBy('category');
        $myDocuments = LegalDocument::where('user_id', Auth::id())->latest()->get();
        return view('services.legal-hub', compact('docTypes', 'myDocuments'));
    }

    // ─────────────────────────────────────────────
    //  AGENCY BANKING
    // ─────────────────────────────────────────────
    public function agencyBanking()
    {
        $agencyFee = (float) \App\Models\SystemSetting::get('agency_banking_fee', 50);
        $agencyHistory = Transaction::where('user_email', Auth::user()->email)
            ->where('order_type', 'like', 'Agency Banking%')
            ->latest()
            ->take(10)
            ->get();

        return view('services.agency-banking', compact('agencyFee', 'agencyHistory'));
    }

    public function agencyRequest(Request $request)
    {
        $request->validate([
            'service_type'   => 'required|string|in:cash_in,cash_out,transfer,account_opening',
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|string|digits:11',
            'amount'         => 'required|numeric|min:100',
        ]);

        // Dynamic service fee for agency transactions
        $fee = \App\Models\SystemSetting::get('agency_banking_fee', 50);
        $result = $this->deductAndLog('Agency Banking – ' . ucwords(str_replace('_', ' ', $request->service_type)), $fee);

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message']]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Agency request submitted successfully.',
            'ref'     => $result['txId'],
            'balance' => number_format($result['balance'], 2),
        ]);
    }

    // ─────────────────────────────────────────────
    //  AUCTIONS
    // ─────────────────────────────────────────────
    public function auctions()
    {
        $myBids = AuctionBid::where('user_id', Auth::id())->latest()->get();
        $auctionMinBid = (float) \App\Models\SystemSetting::get('auction_min_bid', 500);
        return view('services.auctions', compact('myBids', 'auctionMinBid'));
    }

    public function placeBid(Request $request)
    {
        $request->validate([
            'item_id'   => 'required|string',
            'item_name' => 'required|string|max:150',
            'bid_amount' => 'required|numeric|min:' . \App\Models\SystemSetting::get('auction_min_bid', 500),
        ]);

        $result = $this->deductAndLog('Auction Bid – ' . $request->item_name, (float) $request->bid_amount);

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message']]);
        }

        // Persist the bid
        AuctionBid::create([
            'user_id' => Auth::id(),
            'lot_id' => $request->item_id,
            'item_name' => $request->item_name,
            'bid_amount' => $request->bid_amount,
            'status' => 'winning',
            'reference' => $result['txId'],
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Bid placed successfully! You are currently the leading bidder.',
            'bid_ref' => $result['txId'],
            'balance' => number_format($result['balance'], 2),
        ]);
    }

    public function notary()
    {
        $pricing = app(\App\Services\LegalCatalog\LegalPricingService::class);
        $docTypes = DB::table('notary_settings')
            ->whereNotIn('document_type', ['branding'])
            ->get()
            ->groupBy('category')
            ->map(function ($docs) use ($pricing) {
                return $docs->map(function ($d) use ($pricing) {
                    $d->price = $pricing->priceFor($d->document_type, (float) $d->price);
                    return $d;
                });
            });
        $myRequests = NotaryRequest::where('user_id', Auth::id())->latest()->get();
        return view('services.notary', compact('docTypes', 'myRequests'));
    }

    public function notarySubmit(Request $request)
    {
        $request->validate([
            'document_type'      => 'required|string',
            'custom_type_name'   => 'required_if:document_type,custom|nullable|string|max:100',
            'parties'            => 'nullable|string|max:255',
            'description'        => 'nullable|string|max:1000',
            'form_data'          => 'required|array',
        ]);

        $isCustom = $request->document_type === 'custom';
        $docType  = $isCustom ? $request->custom_type_name : $request->document_type;

        $setting = DB::table('notary_settings')->where('document_type', $request->document_type)->first();
        
        // Use dynamic price from SystemSetting if available, else from notary_settings table
        $fee = app(LegalPricingService::class)->priceFor((string) $request->document_type, $setting ? (float) $setting->price : 5000);

        $generatedContent = app(\App\Services\LegalDrafting\LegalDraftingService::class)
            ->draftHtml(new \App\Services\LegalDrafting\LegalDraftRequest(
                $docType,
                (string) ($setting->category ?? 'General'),
                (array) $request->form_data,
                'You are a legal drafting AI specializing in Nigerian Law.'
            ))['html'] ?? '';

        // Create the request record
        $ref = 'NTR-' . strtoupper(bin2hex(random_bytes(4)));
        
        $notaryRequest = NotaryRequest::create([
            'user_id'           => Auth::id(),
            'document_type'     => $docType,
            'form_data'         => $request->form_data,
            'generated_content' => $generatedContent, // Store the AI draft
            'status'            => 'draft',
            'reference'         => $ref,
            'amount_paid'       => 0,
        ]);

        // Generate the DRAFT PDF with watermark
        $draftPath = $this->generateLegalPdf($notaryRequest, true);
        $notaryRequest->update(['draft_pdf_path' => $draftPath]);

        return response()->json([
            'status'     => true,
            'message'    => 'Draft generated successfully!',
            'request_id' => $notaryRequest->id,
            'reference'  => $ref,
            'content'    => $generatedContent, // For preview
            'pdf_url'    => Storage::url($draftPath),
            'fee'        => number_format($fee, 2),
        ]);
    }

    public function notaryPay(Request $request)
    {
        $request->validate(['request_id' => 'required|exists:notary_requests,id']);
        
        $notaryRequest = \App\Models\NotaryRequest::findOrFail($request->request_id);
        
        if ($notaryRequest->status !== 'draft') {
            return response()->json(['status' => false, 'message' => 'This request is already processed or paid.']);
        }

        $setting = DB::table('notary_settings')->where('document_type', $notaryRequest->document_type)->first();
        if (!$setting) {
            $setting = DB::table('notary_settings')->where('document_type', 'custom')->first();
        }
        $fee = (float) app(LegalPricingService::class)->priceFor((string) ($setting->document_type ?? $notaryRequest->document_type), $setting ? (float) $setting->price : 5000);

        $result = $this->deductAndLog(
            'Notary – ' . ucwords(str_replace('_', ' ', $notaryRequest->document_type)),
            $fee,
            ['transaction_id' => $notaryRequest->reference]
        );

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message']]);
        }

        $requiresStamp = (bool) ($setting->requires_court_stamp ?? false);
        $nextStatus = $requiresStamp ? 'pending_stamp' : 'completed';
        
        $notaryRequest->update([
            'status' => $nextStatus,
            'amount_paid' => $fee,
        ]);

        // If completed (internal e-stamp), we generate the final document
        if ($nextStatus === 'completed') {
            $finalPath = $this->generateLegalPdf($notaryRequest, false);
            $notaryRequest->update(['final_pdf_path' => $finalPath, 'stamped_at' => now()]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Payment successful. Your document is being processed.',
            'balance' => number_format($result['balance'], 2),
            'final_url' => $nextStatus === 'completed' ? Storage::url($notaryRequest->final_pdf_path) : null,
        ]);
    }

    /**
     * Helper to generate PDF using dompdf
     */
    private function generateLegalPdf(NotaryRequest $request, bool $isDraft)
    {
        $settings = DB::table('notary_settings')->where('document_type', $request->document_type)->first();
        if (!$settings) {
            // Check if it's a custom doc
            $settings = DB::table('notary_settings')->where('document_type', 'custom')->first();
        }

        $pricing = app(LegalPricingService::class);
        $data = [
            'reference' => $request->reference,
            'date'      => now()->format('F d, Y - H:i'),
            'content'   => $request->generated_content,
            'isDraft'   => $isDraft,
            'stamp_path' => $pricing->stampAssetPath(),
            'signature_path' => null,
            'signature_text' => $pricing->signatureText(),
        ];

        $pdf = Pdf::loadView('pdf.notary_document', $data);
        
        $folder = $isDraft ? 'notary/drafts' : 'notary/final';
        $filename = $request->reference . '.pdf';
        $path = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }


    // ─────────────────────────────────────────────
    //  TICKETING
    // ─────────────────────────────────────────────
    public function ticketing()
    {
        $myTickets = EventTicket::where('user_id', Auth::id())->latest()->get();
        $ticketPrices = [
            'regular' => (float) \App\Models\SystemSetting::get('ticket_regular_price', 2500),
            'vip' => (float) \App\Models\SystemSetting::get('ticket_vip_price', 10000),
            'vvip' => (float) \App\Models\SystemSetting::get('ticket_vvip_price', 25000),
        ];

        return view('services.ticketing', compact('myTickets', 'ticketPrices'));
    }

    public function buyTicket(Request $request)
    {
        $request->validate([
            'event_name'   => 'required|string|max:150',
            'event_date'   => 'required|date|after:today',
            'ticket_type'  => 'required|string|in:regular,vip,vvip',
            'quantity'     => 'required|integer|min:1|max:10',
            'attendee_name'=> 'required|string|max:100',
            'attendee_email'=> 'required|email',
        ]);

        $prices = [
            'regular' => (float) \App\Models\SystemSetting::get('ticket_regular_price', 2500),
            'vip' => (float) \App\Models\SystemSetting::get('ticket_vip_price', 10000),
            'vvip' => (float) \App\Models\SystemSetting::get('ticket_vvip_price', 25000),
        ];
        $unitPrice = $prices[$request->ticket_type] ?? 2500;
        $total = $unitPrice * (int) $request->quantity;

        $result = $this->deductAndLog(
            'Ticket – ' . $request->event_name . ' (' . strtoupper($request->ticket_type) . ')',
            $total
        );

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message']]);
        }

        // Persist ticket
        $ticket = EventTicket::create([
            'user_id' => Auth::id(),
            'event_name' => $request->event_name,
            'event_date' => $request->event_date,
            'ticket_type' => $request->ticket_type,
            'quantity' => $request->quantity,
            'attendee_name' => $request->attendee_name,
            'attendee_email' => $request->attendee_email,
            'amount_paid' => $total,
            'reference' => $result['txId'],
        ]);

        // Generate Ticket PDF
        $pdfPath = $this->generateTicketPdf($ticket);
        $ticket->update(['ticket_pdf_path' => $pdfPath]);

        return response()->json([
            'status'     => true,
            'message'    => 'Ticket purchased! Check your email for confirmation.',
            'ticket_ref' => $ticket->reference,
            'quantity'   => $request->quantity,
            'total'      => number_format($total, 2),
            'balance'    => number_format($result['balance'], 2),
            'qr_hint'    => 'Present your ticket reference ' . $ticket->reference . ' at the gate.',
            'pdf_url'    => Storage::url($pdfPath),
        ]);
    }

    private function generateTicketPdf(EventTicket $ticket)
    {
        $data = [
            'event_name' => $ticket->event_name,
            'event_date' => \Carbon\Carbon::parse($ticket->event_date)->format('F d, Y'),
            'attendee_name' => $ticket->attendee_name,
            'ticket_type' => $ticket->ticket_type,
            'quantity' => $ticket->quantity,
            'reference' => $ticket->reference,
        ];

        $pdf = Pdf::loadView('pdf.ticket', $data);
        $path = 'tickets/' . $ticket->reference . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    // ─────────────────────────────────────────────
    //  VIRTUAL CARDS
    // ─────────────────────────────────────────────
    public function virtualCard()
    {
        $myCards = VirtualCard::where('user_id', Auth::id())->latest()->get();
        return view('services.virtual-cards', compact('myCards'));
    }

    public function createVirtualCard(Request $request, VirtualCardService $cardService)
    {
        $request->validate([
            'card_type'   => 'required|string|in:usd,gbp,eur',
            'initial_load'=> 'required|numeric|min:10',
        ]);

        $creationFee = (float) SystemSetting::get(
            'virtual_card_creation_fee_ngn',
            SystemSetting::get('virtual_card_price', 500)
        );
        $rate = [
            'usd' => (float) SystemSetting::get('virtual_card_fx_rate_usd', SystemSetting::get('fx_rate_usd', 1550)),
            'gbp' => (float) SystemSetting::get('virtual_card_fx_rate_gbp', SystemSetting::get('fx_rate_gbp', 1950)),
            'eur' => (float) SystemSetting::get('virtual_card_fx_rate_eur', SystemSetting::get('fx_rate_eur', 1700)),
        ];
        $ngnLoad = (float) $request->initial_load * ($rate[$request->card_type] ?? $rate['usd']);
        $total = $creationFee + $ngnLoad;

        $user = Auth::user();
        $result = $this->deductAndLog(
            'Virtual Card Creation (' . strtoupper($request->card_type) . ')',
            $total
        );

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message']]);
        }

        // Call the Virtual Card Service (Real API Integration)
        $billingName = $user->fullname ?? $user->username ?? 'FUWA User';
        $providerRes = $cardService->createCard($user, $request->card_type, $request->initial_load, $billingName);

        if (!$providerRes['ok']) {
            // Refund the user if card creation fails
            app(WalletService::class)->credit($user, $total, 'Refund: Virtual Card Creation Failed', 'REF-'.$result['txId']);
            return response()->json(['status' => false, 'message' => $providerRes['message']]);
        }

        $cardData = $providerRes['data'];

        // Persist the card
        $card = VirtualCard::create([
            'user_id' => $user->id,
            'card_name' => 'Main ' . strtoupper($request->card_type) . ' Card',
            'card_number' => $cardData['card_number'],
            'expiry_date' => $cardData['expiry'],
            'cvv' => $cardData['cvv'],
            'currency' => $cardData['currency'],
            'balance' => $cardData['balance'],
            'status' => $cardData['status'],
            'reference' => $result['txId'],
            'provider_card_id' => $cardData['card_id'] ?? null,
        ]);

        return response()->json([
            'status'      => true,
            'message'     => 'Virtual card created successfully!',
            'card'        => [
                'number'    => $card->card_number,
                'expiry'    => $card->expiry_date,
                'cvv'       => $cardData['cvv'], // Returned once, never stored
                'currency'  => $card->currency,
                'balance'   => $card->balance,
            ],
            'wallet_balance' => number_format($result['balance'], 2),
            'ref'         => $card->reference,
        ]);
    }

    public function fundVirtualCard(Request $request, VirtualCardService $cardService)
    {
        $request->validate([
            'card_ref'   => 'required|string',
            'amount'     => 'required|numeric|min:10',
            'currency'   => 'required|string|in:usd,gbp,eur',
        ]);

        $card = VirtualCard::where('reference', $request->card_ref)->where('user_id', Auth::id())->firstOrFail();

        $rate  = [
            'usd' => (float) SystemSetting::get('virtual_card_fx_rate_usd', SystemSetting::get('fx_rate_usd', 1550)),
            'gbp' => (float) SystemSetting::get('virtual_card_fx_rate_gbp', SystemSetting::get('fx_rate_gbp', 1950)),
            'eur' => (float) SystemSetting::get('virtual_card_fx_rate_eur', SystemSetting::get('fx_rate_eur', 1700)),
        ];
        $ngnAmount = (float) $request->amount * ($rate[$request->currency] ?? $rate['usd']);
        $fee = (float) SystemSetting::get('virtual_card_funding_fee_ngn', 100);
        $total = $ngnAmount + $fee;

        $user = Auth::user();
        $result = $this->deductAndLog(
            'Virtual Card Funding (' . strtoupper($request->currency) . ')',
            $total
        );

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message']]);
        }

        // Fund via Provider
        if ($card->provider_card_id) {
            $providerRes = $cardService->fundCard($card->provider_card_id, $request->amount);
            if (!$providerRes['ok']) {
                app(WalletService::class)->credit($user, $total, 'Refund: Card Funding Failed', 'REF-'.$result['txId']);
                return response()->json(['status' => false, 'message' => $providerRes['message']]);
            }
        }

        $card->balance += $request->amount;
        $card->save();

        return response()->json([
            'status'  => true,
            'message' => 'Card funded successfully!',
            'funded'  => $request->amount . ' ' . strtoupper($request->currency),
            'balance' => number_format($result['balance'], 2),
            'ref'     => $result['txId'],
        ]);
    }

    // ─────────────────────────────────────────────
    //  FX / CURRENCY EXCHANGE
    // ─────────────────────────────────────────────
    public function fx()
    {
        $rates = [
            'USD' => (float) \App\Models\SystemSetting::get('fx_rate_usd', 1550),
            'GBP' => (float) \App\Models\SystemSetting::get('fx_rate_gbp', 1950),
            'EUR' => (float) \App\Models\SystemSetting::get('fx_rate_eur', 1700),
            'CAD' => (float) \App\Models\SystemSetting::get('fx_rate_cad', 1120),
            'AUD' => (float) \App\Models\SystemSetting::get('fx_rate_aud', 980),
            'CNY' => (float) \App\Models\SystemSetting::get('fx_rate_cny', 215),
        ];
        $fxHistory = Transaction::where('user_email', Auth::user()->email)
            ->where('order_type', 'like', 'FX Exchange%')
            ->latest()
            ->take(10)
            ->get();

        $fxFeePercent = (float) \App\Models\SystemSetting::get('fx_fee_percent', 1.5);

        return view('services.fx', compact('rates', 'fxHistory', 'fxFeePercent'));
    }

    public function exchangeCurrency(Request $request)
    {
        $request->validate([
            'from_currency' => 'required|string|in:USD,GBP,EUR,CAD,AUD,CNY',
            'to_currency'   => 'required|string|in:NGN',
            'amount'        => 'required|numeric|min:10',
        ]);

        $from = strtoupper($request->from_currency);
        $rate = 0;

        // Try fetching real-time rate
        try {
            $res = Http::timeout(5)->get("https://open.er-api.com/v6/latest/{$from}");
            if ($res->successful()) {
                $rates = $res->json('rates');
                if (isset($rates['NGN'])) {
                    $rate = (float) $rates['NGN'];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Exchange rate API failed, falling back to system settings', ['error' => $e->getMessage()]);
        }

        // Fallback to SystemSetting
        if ($rate <= 0) {
            $rateName = 'fx_rate_' . strtolower($from);
            $rate  = \App\Models\SystemSetting::get($rateName, match($from) {
                'GBP' => 1950,
                'EUR' => 1700,
                'CAD' => 1120,
                'AUD' => 980,
                'CNY' => 215,
                default => 1550
            });
        }

        // For now: user sells foreign currency for NGN (credit wallet)
        $ngnAmount = $request->amount * $rate;
        $fxFeePercent = \App\Models\SystemSetting::get('fx_fee_percent', 1.5) / 100;
        $fee       = $ngnAmount * $fxFeePercent; 
        $net       = $ngnAmount - $fee;

        $user   = Auth::user();
        $balRec = AccountBalance::where('email', $user->email)->firstOrCreate(
            ['email' => $user->email],
            ['user_balance' => 0, 'api_key' => 'user']
        );

        $oldBalance = (float) $balRec->user_balance;
        $newBalance = $oldBalance + $net;
        
        // Use DB transaction for thread safety and to prevent race conditions
        DB::transaction(function () use ($balRec, $newBalance, $user, $request, $oldBalance, $net, $fee, $rate) {
            // Lock the row to prevent race conditions
            $lockedBal = AccountBalance::where('id', $balRec->id)->lockForUpdate()->first();
            
            $lockedOldBalance = (float) $lockedBal->user_balance;
            $lockedNewBalance = $lockedOldBalance + $net;
            
            $lockedBal->update(['user_balance' => $lockedNewBalance]);

            $txId = 'FX-' . strtoupper(bin2hex(random_bytes(4)));
            Transaction::create([
                'user_email'     => $user->email,
                'order_type'     => 'FX Exchange – ' . $request->from_currency . ' → NGN',
                'balance_before' => $lockedOldBalance,
                'balance_after'  => $lockedNewBalance,
                'transaction_id' => $txId,
                'status'         => 'success',
            ]);
            
            // Pass txId out
            request()->merge(['txId' => $txId, 'final_balance' => $lockedNewBalance]);
        });

        return response()->json([
            'status'      => true,
            'message'     => 'Exchange completed. Wallet credited.',
            'received_ngn'=> number_format($net, 2),
            'fee'         => number_format($fee, 2),
            'rate'        => $rate,
            'balance'     => number_format(request('final_balance'), 2),
            'ref'         => request('txId'),
        ]);
    }

    public function fxRates()
    {
        $currencies = ['USD', 'GBP', 'EUR', 'CAD', 'AUD', 'CNY'];
        $rates = [];
        
        try {
            $res = Http::timeout(5)->get("https://open.er-api.com/v6/latest/USD");
            if ($res->successful()) {
                $data = $res->json('rates');
                $ngnPerUsd = (float) ($data['NGN'] ?? 1550);
                
                // Base is USD, so calculate other pairs based on NGN/USD
                foreach ($currencies as $cur) {
                    if ($cur === 'USD') {
                        $rates['USD'] = $ngnPerUsd;
                    } else {
                        // Rate of NGN relative to the currency
                        $curPerUsd = (float) ($data[$cur] ?? 1);
                        $rates[$cur] = $curPerUsd > 0 ? ($ngnPerUsd / $curPerUsd) : 1;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Exchange rate API failed for /rates', ['error' => $e->getMessage()]);
        }

        // Fallbacks
        if (empty($rates)) {
            $rates = [
                'USD' => (float) \App\Models\SystemSetting::get('fx_rate_usd', 1550),
                'GBP' => (float) \App\Models\SystemSetting::get('fx_rate_gbp', 1950),
                'EUR' => (float) \App\Models\SystemSetting::get('fx_rate_eur', 1700),
                'CAD' => (float) \App\Models\SystemSetting::get('fx_rate_cad', 1120),
                'AUD' => (float) \App\Models\SystemSetting::get('fx_rate_aud', 980),
                'CNY' => (float) \App\Models\SystemSetting::get('fx_rate_cny', 215),
            ];
        }

        return response()->json([
            'rates' => $rates,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────
    //  INVOICING
    // ─────────────────────────────────────────────
    public function invoicing()
    {
        $user = Auth::user();
        $invoices = ServiceInvoice::where('user_id', $user->id)->latest()->get();
        return view('services.invoicing', compact('invoices'));
    }

    public function createInvoice(Request $request)
    {
        $request->validate([
            'client_name'    => 'required|string|max:100',
            'client_email'   => 'required|email',
            'items'          => 'required|array|min:1',
            'items.*.desc'   => 'required|string|max:200',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.price'  => 'required|numeric|min:0',
            'due_date'       => 'required|date|after:today',
        ]);

        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);
        $vatPercent = \App\Models\SystemSetting::get('invoice_vat_percent', 7.5) / 100;
        $vat      = $subtotal * $vatPercent; 
        $total    = $subtotal + $vat;

        $invoiceFee = \App\Models\SystemSetting::get('invoice_creation_fee', 200);
        $result = $this->deductAndLog('Invoice – ' . $request->client_name, $invoiceFee);
        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message']]);
        }

        // Persist invoice
        $invoice = ServiceInvoice::create([
            'user_id' => Auth::id(),
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'items' => $request->items,
            'subtotal' => $subtotal,
            'tax_amount' => $vat,
            'total_amount' => $total,
            'due_date' => $request->due_date,
            'status' => 'sent',
            'invoice_number' => 'INV-' . strtoupper(bin2hex(random_bytes(3))),
        ]);

        // Generate Invoice PDF
        $pdfPath = $this->generateInvoicePdf($invoice);
        $invoice->update(['pdf_path' => $pdfPath]);

        return response()->json([
            'status'       => true,
            'message'      => 'Invoice created successfully!',
            'invoice_no'   => $invoice->invoice_number,
            'subtotal'     => number_format($subtotal, 2),
            'vat'          => number_format($vat, 2),
            'total'        => number_format($total, 2),
            'due_date'     => $request->due_date,
            'client'       => $request->client_name,
            'client_email' => $request->client_email,
            'balance'      => number_format($result['balance'], 2),
            'ref'          => $result['txId'],
            'pdf_url'      => Storage::url($pdfPath),
        ]);
    }

    private function generateInvoicePdf(ServiceInvoice $invoice)
    {
        $data = [
            'invoice_number' => $invoice->invoice_number,
            'date' => $invoice->created_at->format('F d, Y'),
            'due_date' => $invoice->due_date->format('F d, Y'),
            'sender_name' => Auth::user()->name ?? 'Fuwa.NG Services',
            'sender_email' => Auth::user()->email,
            'client_name' => $invoice->client_name,
            'client_email' => $invoice->client_email,
            'items' => $invoice->items,
            'subtotal' => $invoice->subtotal,
            'tax' => $invoice->tax_amount,
            'total' => $invoice->total_amount,
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);
        $path = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
