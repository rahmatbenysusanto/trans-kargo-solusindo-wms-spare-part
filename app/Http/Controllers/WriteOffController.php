<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class WriteOffController extends Controller
{
    public function index(): View
    {
        $title = "Write Off";
        return view('writeoff.index', compact('title'));
    }
}
