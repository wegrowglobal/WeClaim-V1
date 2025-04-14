<?php

namespace App\Livewire\Auth;

use App\Models\System\Changelog;
use Livewire\Component;

class ChangelogFeed extends Component
{
    public function render()
    {
        $changelogs = Changelog::published()
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.auth.changelog-feed', [
            'changelogs' => $changelogs
        ]);
    }
}
