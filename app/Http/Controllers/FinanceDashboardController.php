<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\User;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        // Get claim statistics for admin dashboard
        $statistics = Claim::getClaimStatistics();

        // Get recent claims for activity table
        $recentClaims = Claim::with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($claim) {
                return [
                    'id' => $claim->id,
                    'employee_name' => $claim->user->name,
                    'title' => $claim->title,
                    'category' => $claim->category->display_name,
                    'amount' => $claim->amount,
                    'status' => $claim->formatted_status['text'],
                    'status_class' => $claim->formatted_status['class'],
                    'submitted_at' => $claim->created_at->format('M d, Y H:i'),
                    'claim_url' => route('admin.claims.show', $claim->id)
                ];
            });

        // Get pending claims count specifically
        $pendingCount = Claim::whereIn('status', ['submitted', 'under_review'])->count();

        return view('admin.dashboard', [
            'user' => auth()->user(),
            'statistics' => $statistics,
            'recentClaims' => $recentClaims,
            'pendingCount' => $pendingCount,
        ]);
    }
}
