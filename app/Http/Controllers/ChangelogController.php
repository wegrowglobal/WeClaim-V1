<?php

namespace App\Http\Controllers;

use App\Models\Changelog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangelogController extends Controller
{
    /**
     * Display the changelog management page.
     */
    public function index()
    {
        // Check if user is super admin (role_id 5)
        if (Auth::user()->role_id !== 5) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        return view('pages.admin.changelog.index');
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

        return response()->json($changelogs);
    }
}
