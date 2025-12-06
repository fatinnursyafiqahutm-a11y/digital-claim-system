<?php

namespace App\Http\Controllers;
use App\Models\Claim;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $statistics = Claim::getClaimStatistics(auth()->user()->id);

        return view('employee.dashboard', [
            'user' => auth()->user(),
            'statistics' => $statistics,
        ]);
    }
}
