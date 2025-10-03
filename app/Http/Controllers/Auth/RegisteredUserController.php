<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $verifiedEmail = session('verified_email');
        
        return Inertia::render('Auth/Register', [
            'verifiedEmail' => $verifiedEmail,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $verifiedEmail = session('verified_email');
        
        if (!$verifiedEmail) {
            return redirect()->route('register')->withErrors([
                'email' => 'E-mailadres moet eerst geverifieerd worden.'
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Ensure the email matches the verified email
        if ($request->email !== $verifiedEmail) {
            return redirect()->route('register')->withErrors([
                'email' => 'E-mailadres komt niet overeen met geverifieerd adres.'
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user', // Default role
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Clear the verified email from session
        $request->session()->forget('verified_email');

        return redirect(route('dashboard', absolute: false));
    }
}
