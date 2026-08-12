<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\VerifyEmail;
use App\Mail\ResetPasswordEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Afficher la page de connexion
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Traiter la connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Vérifier si l'utilisateur existe
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.']);
        }

        // Vérifier si le compte est activé
        if (!$user->email_verified_at) {
            return back()->withErrors(['email' => 'Votre compte n\'est pas encore activé. Veuillez vérifier votre email.'])
                       ->with('show_resend_activation', true)
                       ->with('resend_email', $user->email);
        }

        // Vérifier si le compte est actif
        if ($user->statut !== 'actif') {
            return back()->withErrors(['email' => 'Votre compte est inactif. Veuillez contacter l\'administrateur.']);
        }

        // Tenter la connexion
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'))
                           ->with('success', 'Connexion réussie ! Bienvenue ' . Auth::user()->prenom);
        }

        return back()->withErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.']);
    }

    // Afficher la page d'inscription
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Traiter l'inscription
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'role' => 'required|in:chercheur,collectivite,public',
            'region' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
        ]);

        // Créer le token de vérification
        $verificationToken = Str::random(64);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'role' => $request->role,
            'region' => $request->region,
            'telephone' => $request->telephone,
            'statut' => 'inactif',
            'email_verified_at' => null,
            'verification_token' => $verificationToken,
            'verification_token_expires' => now()->addHours(24),
        ]);

        // Envoyer l'email de vérification
        $this->sendVerificationEmail($user);

        return redirect()->route('login')
                       ->with('success', 'Inscription réussie ! Un email de vérification vous a été envoyé.');
    }

    // Vérifier l'email
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)
                   ->where('verification_token_expires', '>', now())
                   ->first();

        if (!$user) {
            return redirect()->route('login')
                           ->withErrors(['email' => 'Le lien de vérification est invalide ou a expiré.']);
        }

        $user->update([
            'email_verified_at' => now(),
            'statut' => 'actif',
            'verification_token' => null,
            'verification_token_expires' => null,
        ]);

        return redirect()->route('login')
                       ->with('success', 'Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.');
    }

    // Afficher le formulaire de demande de réinitialisation
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // Envoyer le lien de réinitialisation
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('success', 'Si cet email existe, un lien de réinitialisation a été envoyé.');
        }

        $resetToken = Str::random(64);
        $user->update([
            'reset_token' => $resetToken,
            'reset_token_expires' => now()->addHour(),
        ]);

        $this->sendResetPasswordEmail($user);

        return back()->with('success', 'Un lien de réinitialisation a été envoyé à votre email.');
    }

    // Afficher le formulaire de réinitialisation
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    // Réinitialiser le mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)
                   ->where('reset_token', $request->token)
                   ->where('reset_token_expires', '>', now())
                   ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Le lien de réinitialisation est invalide ou a expiré.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires' => null,
        ]);

        return redirect()->route('login')
                       ->with('success', 'Votre mot de passe a été réinitialisé avec succès !');
    }

    // Déconnexion
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                       ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    // Envoyer l'email de vérification
    private function sendVerificationEmail($user)
    {
        try {
            Mail::to($user->email)->send(new VerifyEmail($user));
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email de vérification: ' . $e->getMessage());
        }
    }

    // Envoyer l'email de réinitialisation
    private function sendResetPasswordEmail($user)
    {
        try {
            Mail::to($user->email)->send(new ResetPasswordEmail($user));
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email de réinitialisation: ' . $e->getMessage());
        }
    }
}
