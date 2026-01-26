<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $client = Client::paginate(10);

        $title = 'Client';
        return view('client.index', compact('title', 'client'));
    }

    public function store(Request $request)
    {
        Client::create([
            'name' => $request->post('name'),
        ]);

        return redirect()->back()->with('success', 'Client added successfully');
    }

    public function update(Request $request)
    {
        $client = Client::find($request->post('id'));
        $client->update([
            'name' => $request->post('name'),
        ]);

        return redirect()->back()->with('success', 'Client updated successfully');
    }
}
