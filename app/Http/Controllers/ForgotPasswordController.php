<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()->route('password.confirmation')->with('status', 'success');
        } else {
            return redirect()->route('password.confirmation')->with('status', 'failed');
        }
    }

    public function showConfirmation()
    {
        return view('pages.auth.forgot-password-confirmation');
    }
}
