<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class OutboundController extends Controller
{
    public function index(): View
    {
        $title = 'Outbound';
        return view('outbound.index', compact('title'));
    }
}
