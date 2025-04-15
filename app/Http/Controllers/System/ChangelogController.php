<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Changelog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangelogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Admin only needs to access index
        $this->middleware('admin')->only('index');
        
        // No middleware needed for public published changelogs
    }

    /**
     * Display the changelog management page.
     */
    public function index()
    {
        // Check if user is super admin (role_id 5)
        if (Auth::user()->role_id !== 5) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        return view('pages.admin.system.changelogs.index');
    }

    /**
     * Get published changelogs for the login page.
     */
    public function getPublishedChangelogs()
    {
        $changelogs = Changelog::published()
            ->latest()
            ->take(5)
            ->get();
        
        // No need to sanitize HTML here as we want to preserve it
        // The HTML will be rendered safely in the view using {!! !!}
        
        return response()->json([
            'changelogs' => $changelogs
        ]);
    }
}
