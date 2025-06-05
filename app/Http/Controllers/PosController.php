<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PosController extends Controller
{
    // Placeholder methods
    public function index() {
        return view('desktop.admin.pos');
    }
    public function tables() {
        return response()->json(['message' => 'POS tables']);
    }
    public function orders() {
        return response()->json(['message' => 'POS orders']);
    }
    public function payments() {
        return response()->json(['message' => 'POS payments']);
    }
    public function accessLogs() {
        return response()->json(['message' => 'POS access logs']);
    }
} 