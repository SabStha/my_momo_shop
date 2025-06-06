<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FindsController extends Controller
{
    public function index()
    {
        return view('finds.index');
    }
} 