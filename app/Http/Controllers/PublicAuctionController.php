<?php

namespace App\Http\Controllers;

use App\Models\AuctionBid;
use App\Models\AuctionLot;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicAuctionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $location = trim((string) $request->query('location', ''));
        $status = trim((string) $request->query('status', ''));
        $sort = trim((string) $request->query('sort', 'ending_soon'));

        $filters = [
            'q' => $q,
            'category' => $category,
            'location' => $location,
            'status' => $status,
            'sort' => $sort,
        ];

        $dbError = null;
        $paginated = null;
        $categories = collect();
        $locations = collect();

        try {
            $lots = AuctionLot::query()
                ->with(['images' => function ($q) {
                    $q->orderBy('sort_order')->orderBy('id');
                }])
                ->whereIn('status', ['scheduled', 'live', 'ended']);

            if ($q !== '') {
                $lots->where(function ($w) use ($q) {
                    $w->where('title', 'like', '%' . $q . '%')
                        ->orWhere('lot_code', 'like', '%' . $q . '%');
                });
            }
            if ($category !== '') {
                $lots->where('category', $category);
            }
            if ($location !== '') {
                $lots->where('location', $location);
            }
            if ($status !== '') {
                $lots->where('status', $status);
            }

            if ($sort === 'price_low') {
                $lots->orderBy('current_price', 'asc');
            } elseif ($sort === 'price_high') {
                $lots->orderBy('current_price', 'desc');
            } elseif ($sort === 'newest') {
                $lots->orderBy('created_at', 'desc');
            } else {
                $lots->orderByRaw("CASE status WHEN 'live' THEN 0 WHEN 'scheduled' THEN 1 ELSE 2 END")
                    ->orderBy('end_at', 'asc');
            }

            $paginated = $lots->paginate(12)->withQueryString();

            $categories = AuctionLot::query()
                ->whereIn('status', ['scheduled', 'live', 'ended'])
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->values();

            $locations = AuctionLot::query()
                ->whereIn('status', ['scheduled', 'live', 'ended'])
                ->whereNotNull('location')
                ->select('location')
                ->distinct()
                ->orderBy('location')
                ->pluck('location')
                ->values();
        } catch (\Throwable $e) {
            Log::error('Public auctions index failed', [
                'error' => $e->getMessage(),
                'q' => $q,
                'category' => $category,
                'location' => $location,
                'status' => $status,
                'sort' => $sort,
            ]);
            $dbError = app()->environment('local') ? $e->getMessage() : 'Auctions are temporarily unavailable.';
            $paginated = new LengthAwarePaginator(
                [],
                0,
                12,
                LengthAwarePaginator::resolveCurrentPage(),
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }

        return view('public.auctions.index', [
            'lots' => $paginated,
            'filters' => $filters,
            'categories' => $categories,
            'locations' => $locations,
            'updatedAt' => now(),
            'dbError' => $dbError,
        ]);
    }

    public function show(Request $request, string $lotCode)
    {
        try {
            $lot = AuctionLot::query()
                ->with(['images', 'seller'])
                ->where('lot_code', $lotCode)
                ->whereIn('status', ['scheduled', 'live', 'ended'])
                ->firstOrFail();
        } catch (\Throwable $e) {
            Log::error('Public auctions show failed', [
                'error' => $e->getMessage(),
                'lot_code' => $lotCode,
            ]);
            abort(503, app()->environment('local') ? $e->getMessage() : 'Auctions are temporarily unavailable.');
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
                    'reference' => $b->reference,
                    'bidder' => $masked,
                    'created_at' => $b->created_at,
                ];
            });

        return view('public.auctions.show', [
            'lot' => $lot,
            'bids' => $bids,
        ]);
    }
}
