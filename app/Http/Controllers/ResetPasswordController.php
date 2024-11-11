<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Http\Requests\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');
        return view('pages.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Check if password was reset successfully
        if ($status === Password::PASSWORD_RESET) {
            return view('pages.auth.password-reset-success');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
