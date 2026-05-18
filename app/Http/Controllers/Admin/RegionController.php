<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::orderBy('name')->get();
        return view('admin.regions', compact('regions'));
    }

    public function toggleRegistration(Region $region)
    {
        $region->update([
            'allow_registration' => !$region->allow_registration
        ]);

        return response()->json([
            'success' => true,
            'message' => $region->allow_registration 
                ? "Registration enabled for {$region->name}" 
                : "Registration blocked for {$region->name}",
        ]);
    }
}