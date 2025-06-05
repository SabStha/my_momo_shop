<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    // Placeholder methods
    public function index() {
        return response()->json(['message' => 'TimeEntry index']);
    }
    public function clockIn() {
        return response()->json(['message' => 'TimeEntry clock in']);
    }
    public function clockOut() {
        return response()->json(['message' => 'TimeEntry clock out']);
    }
} 