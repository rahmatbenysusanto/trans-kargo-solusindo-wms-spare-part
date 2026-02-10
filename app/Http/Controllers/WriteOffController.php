<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class WriteOffController extends Controller
{
    public function index(): View
    {
        $title = "Write Off";
        $data = \App\Models\Outbound::with('client')
            ->where('category', 'Write-off')
            ->latest()
            ->get();
        return view('writeoff.index', compact('title', 'data'));
    }
}
