<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    public function impersonate(User $user)
    {
        // Check if current user can impersonate
        if (!auth()->user()->canImpersonate()) {
            abort(403, 'Je hebt geen toestemming om in te loggen als andere gebruikers.');
        }

        // Check if target user can be impersonated
        if (!$user->canBeImpersonated()) {
            abort(403, 'Deze gebruiker kan niet worden geïmiteerd.');
        }

        // Store original user ID in session
        Session::put('impersonator_id', auth()->id());
        
        // Log the impersonation
        \Log::info('User impersonation started', [
            'impersonator_id' => auth()->id(),
            'impersonator_email' => auth()->user()->email,
            'target_user_id' => $user->id,
            'target_user_email' => $user->email,
            'ip_address' => request()->ip(),
        ]);

        // Login as the target user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 
            "Je bent nu ingelogd als {$user->name}. Klik op 'Stop Impersonation' om terug te gaan naar je eigen account.");
    }

    public function stopImpersonation()
    {
        $impersonatorId = Session::get('impersonator_id');
        
        if (!$impersonatorId) {
            return redirect()->route('admin.users')->with('error', 'Geen actieve impersonation gevonden.');
        }

        $impersonator = User::find($impersonatorId);
        
        if (!$impersonator) {
            return redirect()->route('admin.users')->with('error', 'Impersonator niet gevonden.');
        }

        // Log the end of impersonation
        \Log::info('User impersonation ended', [
            'impersonator_id' => $impersonatorId,
            'impersonator_email' => $impersonator->email,
            'target_user_id' => auth()->id(),
            'target_user_email' => auth()->user()->email,
            'ip_address' => request()->ip(),
        ]);

        // Clear impersonation session
        Session::forget('impersonator_id');
        
        // Login back as original user
        Auth::login($impersonator);

        return redirect()->route('admin.users')->with('success', 
            "Je bent terug ingelogd als {$impersonator->name}.");
    }

    public function isImpersonating(): bool
    {
        return Session::has('impersonator_id');
    }

    public function getImpersonator(): ?User
    {
        $impersonatorId = Session::get('impersonator_id');
        return $impersonatorId ? User::find($impersonatorId) : null;
    }
}
