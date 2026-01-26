<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class RMAController extends Controller
{
    public function index(): View
    {
        $title = 'RMA';
        return view('rma.index', compact('title'));
    }
}
