<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return view('user.notification');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    public function markAsRead($notificationId)
    {
        auth()->user()->notifications()->findOrFail($notificationId)->markAsRead();
        return redirect()->back();
    }
}
