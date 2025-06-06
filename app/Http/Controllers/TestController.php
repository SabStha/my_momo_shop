<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{
    public function assignMonthlyRewards()
    {
        Artisan::call('creators:assign-monthly-rewards');
        return redirect()->route('test.panel')->with('success', 'Monthly rewards assignment triggered successfully.');
    }
} 