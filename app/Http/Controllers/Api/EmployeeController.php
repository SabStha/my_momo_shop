<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // Placeholder methods
    public function verify(Request $request) {
        return response()->json(['message' => 'Employee verify']);
    }
} 