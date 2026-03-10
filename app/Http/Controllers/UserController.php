<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $users = User::with('clients')->latest()
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->paginate(10);

        $title = 'User';
        $clients = \App\Models\Client::all();
        return view('user.index', compact('title', 'users', 'search', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'status' => $request->status ?? 'active',
            'role' => $request->role,
        ]);

        if ($request->client_ids) {
            $user->clients()->sync($request->client_ids);
        }

        return redirect()->back()->with('success', 'User created successfully');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'name' => 'required',
            'username' => 'required|unique:users,username,' . $request->id,
            'email' => 'required|email|unique:users,email,' . $request->id,
            'role' => 'required',
        ]);

        $user = User::findOrFail($request->id);
        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'status' => $request->status,
            'role' => $request->role,
        ];

        if ($request->password) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($data);

        // Sync clients
        if ($request->role === 'Client User') {
            $user->clients()->sync($request->client_ids ?? []);
        } else {
            // Admin WMS doesn't strictly need clients sync'd, but maybe better to clear it
            $user->clients()->detach();
        }

        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully');
    }
}
