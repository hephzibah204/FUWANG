<?php

namespace App\Http\Controllers;

use App\Models\AuctionBid;
use App\Models\AuctionLot;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserAuctionController extends Controller
{
    protected $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
    }

    public function addToWatchlist(Request $request)
    {
        $request->validate(['lot_code' => 'required|string|exists:auction_lots,lot_code']);
        
        DB::table('auction_watchlists')->updateOrInsert([
            'user_id' => Auth::id(),
            'lot_code' => $request->lot_code
        ]);
        
        return response()->json(['ok' => true, 'message' => 'Added to watchlist.']);
    }

    public function removeFromWatchlist(Request $request)
    {
        $request->validate(['lot_code' => 'required|string']);
        
        DB::table('auction_watchlists')
            ->where('user_id', Auth::id())
            ->where('lot_code', $request->lot_code)
            ->delete();
            
        return response()->json(['ok' => true, 'message' => 'Removed from watchlist.']);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'overview');

        $activeBids = AuctionBid::query()
            ->with('lot')
            ->where('user_id', $user->id)
            ->whereHas('lot', function ($q) {
                $q->where('status', 'live');
            })
            ->latest()
            ->get();

        $winningBids = $activeBids->filter(fn($b) => $b->status === 'winning');
        $outbidBids = $activeBids->filter(fn($b) => $b->status === 'outbid');

        // Real watchlist implementation
        $watchlisted = DB::table('auction_watchlists')
            ->where('user_id', $user->id)
            ->join('auction_lots', 'auction_watchlists.lot_code', '=', 'auction_lots.lot_code')
            ->select('auction_lots.*')
            ->get();

        return view('services.auctions.dashboard', [
            'user' => $user,
            'tab' => $tab,
            'winningBids' => $winningBids,
            'outbidBids' => $outbidBids,
            'watchlisted' => $watchlisted,
        ]);
    }

    public function placeBid(Request $request)
    {
        $request->validate([
            'lot_code' => 'required|string|exists:auction_lots,lot_code',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $lot = AuctionLot::where('lot_code', $request->lot_code)->firstOrFail();

        // 1. Check if auction is live
        if ($lot->status !== 'live') {
            return response()->json(['ok' => false, 'message' => 'This auction is not currently live.'], 422);
        }

        // 2. Check if end time has passed
        if ($lot->end_at && $lot->end_at->isPast()) {
            return response()->json(['ok' => false, 'message' => 'This auction has already ended.'], 422);
        }

        // 3. Check minimum bid increment
        $minRequired = (float) $lot->current_price + (float) $lot->bid_increment;
        if ($request->amount < $minRequired) {
            return response()->json(['ok' => false, 'message' => 'Your bid must be at least ₦' . number_format($minRequired, 2)], 422);
        }

        // 4. Check wallet balance (ensure user can cover the bid)
        $balance = (float) ($user->balance?->user_balance ?? 0);
        if ($balance < $request->amount) {
            return response()->json(['ok' => false, 'message' => 'Insufficient wallet balance to place this bid.'], 422);
        }

        return DB::transaction(function () use ($lot, $user, $request) {
            // Re-check lot price within transaction for concurrency
            $freshLot = AuctionLot::where('id', $lot->id)->lockForUpdate()->first();
            $minRequired = (float) $freshLot->current_price + (float) $freshLot->bid_increment;
            if ($request->amount < $minRequired) {
                throw new \Exception('Price has changed. Minimum bid is now ₦' . number_format($minRequired, 2));
            }

            // Mark previous highest bid for this lot as 'outbid'
            AuctionBid::where('lot_id', $lot->lot_code)
                ->where('status', 'winning')
                ->update(['status' => 'outbid']);

            // Create new bid
            $bid = AuctionBid::create([
                'user_id' => $user->id,
                'lot_id' => $lot->lot_code,
                'item_name' => $lot->title,
                'bid_amount' => $request->amount,
                'status' => 'winning',
                'reference' => 'BID-' . strtoupper(Str::random(8)),
            ]);

            // Update lot price
            $freshLot->current_price = $request->amount;
            $freshLot->save();

            return response()->json([
                'ok' => true,
                'message' => 'Bid placed successfully!',
                'new_price' => (float) $request->amount,
                'reference' => $bid->reference,
            ]);
        });
    }
}
