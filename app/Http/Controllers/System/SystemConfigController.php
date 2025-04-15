<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\SystemConfig;
use Illuminate\Http\Request;

class SystemConfigController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $configs = SystemConfig::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('pages.admin.system-config', compact('configs'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'configs' => 'required|array',
            'configs.*.key' => 'required|string',
            'configs.*.value' => 'required',
        ]);

        foreach ($validated['configs'] as $config) {
            SystemConfig::where('key', $config['key'])
                ->update(['value' => $config['value']]);
        }

        return response()->json([
            'message' => 'Configuration updated successfully',
            'configs' => SystemConfig::all()
        ]);
    }
}
