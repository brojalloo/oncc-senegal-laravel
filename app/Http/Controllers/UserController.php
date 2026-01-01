<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Afficher le profil
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    // Mettre à jour le profil
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'region' => $request->region,
            'telephone' => $request->telephone,
        ]);

        return redirect()->route('user.profile')
                       ->with('success', 'Profil mis à jour avec succès.');
    }

    // Formulaire de changement de mot de passe
    public function showChangePasswordForm()
    {
        return view('user.change-password');
    }

    // Changer le mot de passe
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('user.profile')
                       ->with('success', 'Mot de passe changé avec succès.');
    }
}
