<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        $title = 'Menu Management';
        return view('menu.index', compact('menus', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:menu',
        ]);

        Menu::create($request->all());

        return redirect()->back()->with('success', 'Menu created successfully');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:menu,id',
            'name' => 'required|unique:menu,name,' . $request->id,
        ]);

        $menu = Menu::findOrFail($request->id);
        $menu->update($request->all());

        return redirect()->back()->with('success', 'Menu updated successfully');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->back()->with('success', 'Menu deleted successfully');
    }

    public function toggleUserMenu(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $menuId = $request->menu_id;

        if ($user->menus()->where('menu_id', $menuId)->exists()) {
            $user->menus()->detach($menuId);
            $status = 'detached';
        } else {
            $user->menus()->attach($menuId);
            $status = 'attached';
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => 'Menu access updated successfully'
        ]);
    }

    public function getUserMenus($userId)
    {
        $user = User::with('menus')->findOrFail($userId);
        $allMenus = Menu::all();

        $userMenuIds = $user->menus->pluck('id')->toArray();

        $menus = $allMenus->map(function ($menu) use ($userMenuIds) {
            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'has_access' => in_array($menu->id, $userMenuIds)
            ];
        });

        return response()->json([
            'user' => $user,
            'menus' => $menus
        ]);
    }
}
