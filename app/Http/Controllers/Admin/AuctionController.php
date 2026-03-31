<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionBid;
use App\Models\AuctionLot;
use App\Models\AuctionSeller;
use App\Models\AuctionLotImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function index()
    {
        $showTrashed = (bool) request()->boolean('trashed');
        $lotsQuery = AuctionLot::with(['seller'])->latest();
        if ($showTrashed) {
            $lotsQuery->withTrashed();
        }
        $lots = $lotsQuery->paginate(20)->withQueryString();
        return view('admin.auctions.index', compact('lots'));
    }

    public function create()
    {
        $sellers = AuctionSeller::orderBy('name')->get();
        return view('admin.auctions.create', compact('sellers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'seller_id' => 'required|exists:auction_sellers,id',
            'category' => 'required|string|max:80',
            'location' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'starting_price' => 'required|numeric|min:0',
            'current_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => 'required|in:draft,scheduled,live,ended,cancelled',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
        ]);

        $startingPrice = (float) $request->starting_price;
        $currentPrice = $request->filled('current_price') ? (float) $request->current_price : $startingPrice;

        $lot = AuctionLot::create(array_merge($request->only([
            'title', 'seller_id', 'category', 'location', 'description', 
            'starting_price', 'bid_increment', 'start_at', 'end_at', 'status'
        ]), [
            'lot_code' => 'LOT-' . strtoupper(Str::random(6)),
            'current_price' => $currentPrice,
            'featured' => $request->boolean('featured'),
        ]));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('auctions', 'public');
                AuctionLotImage::create([
                    'auction_lot_id' => $lot->id,
                    'url' => Storage::url($path),
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.auctions.index')->with('success', 'Auction lot created successfully.');
    }

    public function edit(AuctionLot $lot)
    {
        $lot->loadMissing('images');
        $sellers = AuctionSeller::orderBy('name')->get();
        return view('admin.auctions.edit', compact('lot', 'sellers'));
    }

    public function update(Request $request, AuctionLot $lot)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'seller_id' => 'required|exists:auction_sellers,id',
            'category' => 'required|string|max:80',
            'location' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'starting_price' => 'required|numeric|min:0',
            'current_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'status' => 'required|in:draft,scheduled,live,ended,cancelled',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:2048',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer|exists:auction_lot_images,id',
        ]);

        $currentPrice = $request->filled('current_price') ? (float) $request->current_price : (float) $lot->current_price;

        DB::transaction(function () use ($request, $lot, $currentPrice) {
            $lot->update(array_merge($request->only([
            'title', 'seller_id', 'category', 'location', 'description', 
            'starting_price', 'bid_increment', 'start_at', 'end_at', 'status'
        ]), [
            'current_price' => $currentPrice,
            'featured' => $request->boolean('featured'),
        ]));

            if (is_array($request->remove_image_ids) && !empty($request->remove_image_ids)) {
                AuctionLotImage::where('auction_lot_id', $lot->id)
                    ->whereIn('id', $request->remove_image_ids)
                    ->delete();
            }

            if ($request->hasFile('images')) {
                $existingCount = AuctionLotImage::where('auction_lot_id', $lot->id)->count();
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('auctions', 'public');
                    AuctionLotImage::create([
                        'auction_lot_id' => $lot->id,
                        'url' => Storage::url($path),
                        'sort_order' => $existingCount + $index,
                    ]);
                }
            }
        });

        return redirect()->route('admin.auctions.index')->with('success', 'Auction lot updated successfully.');
    }

    public function destroy(AuctionLot $lot)
    {
        $lot->delete();
        return back()->with('success', 'Auction lot deleted.');
    }

    public function restore(string $id)
    {
        $lot = AuctionLot::withTrashed()->findOrFail($id);
        $lot->restore();
        return back()->with('success', 'Auction lot restored.');
    }

    public function sellers()
    {
        $showTrashed = (bool) request()->boolean('trashed');
        $sellersQuery = AuctionSeller::latest();
        if ($showTrashed) {
            $sellersQuery->withTrashed();
        }
        $sellers = $sellersQuery->paginate(20)->withQueryString();
        return view('admin.auctions.sellers', compact('sellers'));
    }

    public function storeSeller(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'location' => 'nullable|string|max:120',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'verified' => 'nullable|boolean',
            'avatar_url' => 'nullable|url|max:255',
            'about' => 'nullable|string',
        ]);

        AuctionSeller::create([
            'name' => $request->name,
            'location' => $request->location,
            'rating' => $request->rating ?? 4.8,
            'reviews_count' => $request->reviews_count ?? 0,
            'verified' => $request->boolean('verified'),
            'avatar_url' => $request->avatar_url,
            'about' => $request->about,
        ]);
        return back()->with('success', 'Seller added.');
    }

    public function updateSeller(Request $request, AuctionSeller $seller)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'location' => 'nullable|string|max:120',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'verified' => 'nullable|boolean',
            'avatar_url' => 'nullable|url|max:255',
            'about' => 'nullable|string',
        ]);

        $seller->update([
            'name' => $request->name,
            'location' => $request->location,
            'rating' => $request->rating ?? $seller->rating,
            'reviews_count' => $request->reviews_count ?? $seller->reviews_count,
            'verified' => $request->boolean('verified'),
            'avatar_url' => $request->avatar_url,
            'about' => $request->about,
        ]);

        return back()->with('success', 'Seller updated.');
    }

    public function destroySeller(AuctionSeller $seller)
    {
        $seller->delete();
        return back()->with('success', 'Seller deleted.');
    }

    public function restoreSeller(string $id)
    {
        $seller = AuctionSeller::withTrashed()->findOrFail($id);
        $seller->restore();
        return back()->with('success', 'Seller restored.');
    }

    public function bids(AuctionLot $lot)
    {
        $showTrashed = (bool) request()->boolean('trashed');
        $bidsQuery = AuctionBid::with('user')
            ->where('lot_id', $lot->lot_code)
            ->latest();
        if ($showTrashed) {
            $bidsQuery->withTrashed();
        }
        $bids = $bidsQuery->paginate(30)->withQueryString();
        return view('admin.auctions.bids', compact('lot', 'bids'));
    }

    public function updateBid(Request $request, AuctionBid $bid)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bid_amount' => 'required|numeric|min:0',
            'status' => 'required|in:winning,outbid,cancelled',
        ]);

        DB::transaction(function () use ($request, $bid) {
            $bid->update([
                'user_id' => $request->user_id,
                'bid_amount' => (float) $request->bid_amount,
                'status' => $request->status,
            ]);

            $lot = AuctionLot::where('lot_code', $bid->lot_id)->first();
            if ($lot) {
                $max = AuctionBid::where('lot_id', $lot->lot_code)
                    ->whereNull('deleted_at')
                    ->where('status', '!=', 'cancelled')
                    ->max('bid_amount');
                $lot->update(['current_price' => $max ?? $lot->starting_price]);
            }
        });

        return back()->with('success', 'Bid updated.');
    }

    public function destroyBid(AuctionBid $bid)
    {
        $bid->delete();
        return back()->with('success', 'Bid deleted.');
    }

    public function restoreBid(string $id)
    {
        $bid = AuctionBid::withTrashed()->findOrFail($id);
        $bid->restore();
        return back()->with('success', 'Bid restored.');
    }
}
