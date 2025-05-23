<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class PosTableController extends Controller
{
    public function index()
    {
        $tables = Table::all();
        return response()->json($tables);
    }
} 