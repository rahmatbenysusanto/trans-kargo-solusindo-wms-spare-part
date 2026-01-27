<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class OutboundController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $title = 'Outbound';
        return view('outbound.index', compact('title', 'search'));
    }
}
