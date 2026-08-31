<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     * If SMTP fails, generate the reset URL and display it directly on-screen.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // If user not found, return same generic message (security: don't reveal user existence)
        if (!$user) {
            return back()->with('status', __('passwords.sent'));
        }

        // Generate a reset token manually so we can build the URL for fallback
        $token = Str::random(64);

        // Delete any existing token for this user then create a fresh one
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        // Build the reset URL
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ], false));

        // Try sending the email — if it fails, display the link directly
        try {
            $user->sendPasswordResetNotification($token);

            return back()->with('status', __('passwords.sent'));
        } catch (\Throwable $e) {
            // SMTP failed — show the reset link directly on-screen
            // This is acceptable for an internal admin tool where email delivery isn't guaranteed
            \Illuminate\Support\Facades\Log::warning('Password reset email failed, showing link on-screen: ' . $e->getMessage());

            return back()->with([
                'reset_link' => $resetUrl,
                'reset_email' => $request->email,
            ]);
        }
    }
}
