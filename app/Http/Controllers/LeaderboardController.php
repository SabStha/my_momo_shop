<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'referral_count'); // referral_count, earnings, points
        $period = $request->get('period', 'all'); // all, month

        $query = Creator::with('user');

        if ($period === 'month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $query->whereBetween('updated_at', [$start, $end]);
        }

        if ($sort === 'earnings') {
            $query->orderByDesc('earnings');
        } elseif ($sort === 'points') {
            $query->orderByDesc('points');
        } else {
            $query->orderByDesc('referral_count');
        }

        $creators = $query->take(50)->get();

        // Assign rank and badge
        foreach ($creators as $i => $creator) {
            $creator->rank = $i + 1;
            if ($i === 0) {
                $creator->badge = 'Gold';
            } elseif ($i < 3) {
                $creator->badge = 'Silver';
            } elseif ($i < 10) {
                $creator->badge = 'Bronze';
            } else {
                $creator->badge = 'Participant';
            }
        }

        return view('leaderboard.index', compact('creators', 'sort', 'period'));
    }
} 