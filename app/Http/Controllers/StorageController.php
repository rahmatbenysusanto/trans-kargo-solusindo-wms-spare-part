<?php

namespace App\Http\Controllers;

use App\Models\StorageZone;
use App\Models\StorageRak;
use App\Models\StorageBin;
use App\Models\StorageLevel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorageController extends Controller
{
    // Zone
    public function zone(): View
    {
        $storageZone = StorageZone::all();
        $title = 'Zone';
        return view('storage.zone', compact('title', 'storageZone'));
    }

    public function zoneStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageZone::create(['name' => $request->post('name')]);
        return back()->with('success', 'Created Zone Successfully');
    }

    public function zoneUpdate(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageZone::where('id', $request->post('id'))->update(['name' => $request->post('name')]);
        return back()->with('success', 'Updated Zone Successfully');
    }

    public function zoneDestroy($id): \Illuminate\Http\RedirectResponse
    {
        StorageZone::destroy($id);
        return back()->with('success', 'Deleted Zone Successfully');
    }

    // Rak
    public function rak(): View
    {
        $storageZone = StorageZone::all();
        $storageRak = StorageRak::with('zone')->paginate(10);
        $title = 'Rak';
        return view('storage.rak', compact('title', 'storageRak', 'storageZone'));
    }

    public function rakStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageRak::create([
            'storage_zone_id'   => $request->post('zone'),
            'name'              => $request->post('name'),
        ]);
        return back()->with('success', 'Created Rak Successfully');
    }

    public function rakUpdate(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageRak::where('id', $request->post('id'))->update([
            'storage_zone_id'   => $request->post('zone'),
            'name'              => $request->post('name'),
        ]);
        return back()->with('success', 'Updated Rak Successfully');
    }

    public function rakDestroy($id): \Illuminate\Http\RedirectResponse
    {
        StorageRak::destroy($id);
        return back()->with('success', 'Deleted Rak Successfully');
    }

    public function rakFind(Request $request): \Illuminate\Http\JsonResponse
    {
        $rak = StorageRak::where('storage_zone_id', $request->get('zoneId'))->get();
        return response()->json(['data' => $rak]);
    }

    // Bin
    public function bin(): View
    {
        $storageZone = StorageZone::all();
        $storageBin = StorageBin::with('zone', 'rak')->paginate(10);
        $title = 'Bin';
        return view('storage.bin', compact('title', 'storageBin', 'storageZone'));
    }

    public function binStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageBin::create([
            'storage_zone_id'   => $request->post('zone'),
            'storage_rak_id'    => $request->post('rak'),
            'name'              => $request->post('name'),
        ]);
        return back()->with('success', 'Created Bin Successfully');
    }

    public function binUpdate(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageBin::where('id', $request->post('id'))->update([
            'storage_zone_id'   => $request->post('zone'),
            'storage_rak_id'    => $request->post('rak'),
            'name'              => $request->post('name'),
        ]);
        return back()->with('success', 'Updated Bin Successfully');
    }

    public function binDestroy($id): \Illuminate\Http\RedirectResponse
    {
        StorageBin::destroy($id);
        return back()->with('success', 'Deleted Bin Successfully');
    }

    public function binFind(Request $request): \Illuminate\Http\JsonResponse
    {
        $bin = StorageBin::where('storage_rak_id', $request->get('rakId'))->get();
        return response()->json(['data' => $bin]);
    }

    // Level
    public function level(): View
    {
        $storageZone = StorageZone::all();
        $storageLevel = StorageLevel::with('zone', 'rak', 'bin')->paginate(10);
        $title = 'Level';
        return view('storage.level', compact('title', 'storageZone', 'storageLevel'));
    }

    public function levelStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageLevel::create([
            'storage_zone_id'   => $request->post('zone'),
            'storage_rak_id'    => $request->post('rak'),
            'storage_bin_id'    => $request->post('bin'),
            'name'              => $request->post('name'),
        ]);
        return back()->with('success', 'Created Level Successfully');
    }

    public function levelUpdate(Request $request): \Illuminate\Http\RedirectResponse
    {
        StorageLevel::where('id', $request->post('id'))->update([
            'storage_zone_id'   => $request->post('zone'),
            'storage_rak_id'    => $request->post('rak'),
            'storage_bin_id'    => $request->post('bin'),
            'name'              => $request->post('name'),
        ]);
        return back()->with('success', 'Updated Level Successfully');
    }

    public function levelDestroy($id): \Illuminate\Http\RedirectResponse
    {
        StorageLevel::destroy($id);
        return back()->with('success', 'Deleted Level Successfully');
    }

    public function levelFind(Request $request): \Illuminate\Http\JsonResponse
    {
        $level = StorageLevel::where('storage_bin_id', $request->get('binId'))->get();
        return response()->json(['data' => $level]);
    }
}
