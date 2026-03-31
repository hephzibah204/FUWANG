<?php

namespace App\Http\Controllers;

use App\Models\AuctionBid;
use App\Models\AuctionLot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuctionRealtimeController extends Controller
{
    public function stream(Request $request)
    {
        $lotCode = trim((string) $request->query('lot'));
        $interval = max(2, min(15, (int) $request->query('interval', 5)));
        $ttlSeconds = max(10, min(300, (int) $request->query('ttl', 60)));

        return response()->stream(function () use ($lotCode, $interval, $ttlSeconds) {
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            ob_implicit_flush(true);
            ignore_user_abort(true);

            $startedAt = time();
            $lastHash = null;

            while (time() - $startedAt < $ttlSeconds) {
                if (connection_aborted()) {
                    break;
                }

                $payload = $lotCode !== '' ? $this->lotSnapshot($lotCode) : $this->listSnapshot();
                $json = json_encode($payload);
                $hash = $json ? hash('sha256', $json) : null;

                if ($hash && $hash !== $lastHash) {
                    $lastHash = $hash;
                    echo "event: snapshot\n";
                    echo 'data: ' . $json . "\n\n";
                }

                echo "event: heartbeat\n";
                echo 'data: {"ts":' . (int) (microtime(true) * 1000) . "}\n\n";

                @flush();
                @ob_flush();

                sleep($interval);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function listSnapshot(): array
    {
        $lots = AuctionLot::query()
            ->whereIn('status', ['scheduled', 'live', 'ended'])
            ->orderByRaw("CASE status WHEN 'live' THEN 0 WHEN 'scheduled' THEN 1 ELSE 2 END")
            ->orderBy('end_at', 'asc')
            ->get(['lot_code', 'status', 'current_price', 'end_at', 'location', 'category']);

        return [
            'scope' => 'list',
            'ts' => (int) (microtime(true) * 1000),
            'lots' => $lots->map(fn ($l) => [
                'lot_code' => $l->lot_code,
                'status' => $l->status,
                'current_price' => (float) $l->current_price,
                'end_at' => $l->end_at ? $l->end_at->toIso8601String() : null,
                'location' => $l->location,
                'category' => $l->category,
            ])->values(),
        ];
    }

    private function lotSnapshot(string $lotCode): array
    {
        $lot = AuctionLot::query()
            ->where('lot_code', $lotCode)
            ->whereIn('status', ['scheduled', 'live', 'ended'])
            ->first(['lot_code', 'status', 'current_price', 'end_at']);

        if (!$lot) {
            return [
                'scope' => 'lot',
                'ts' => (int) (microtime(true) * 1000),
                'error' => 'not_found',
            ];
        }

        $bids = AuctionBid::query()
            ->with('user:id,fullname,username,email')
            ->where('lot_id', $lot->lot_code)
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($b) {
                $name = $b->user?->fullname ?? $b->user?->username ?? $b->user?->email ?? 'Bidder';
                $masked = Str::upper(mb_substr($name, 0, 1)) . '***';
                return [
                    'amount' => (float) $b->bid_amount,
                    'status' => $b->status,
                    'bidder' => $masked,
                    'created_at' => $b->created_at->toIso8601String(),
                ];
            })
            ->values();

        return [
            'scope' => 'lot',
            'ts' => (int) (microtime(true) * 1000),
            'lot' => [
                'lot_code' => $lot->lot_code,
                'status' => $lot->status,
                'current_price' => (float) $lot->current_price,
                'end_at' => $lot->end_at ? $lot->end_at->toIso8601String() : null,
            ],
            'bids' => $bids,
        ];
    }
}

