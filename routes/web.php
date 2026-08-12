<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::get('/', function () {
    return redirect()->route('login');
});

// Routes d'authentification
Route::controller(AuthController::class)->group(function () {
    // Formulaires : consultables librement.
    Route::get('/login', 'showLoginForm')->name('login');
    Route::get('/register', 'showRegisterForm')->name('register');
    Route::get('/forgot-password', 'showForgotPasswordForm')->name('password.request');
    Route::get('/reset-password/{token}', 'showResetPasswordForm')->name('password.reset');
    Route::post('/logout', 'logout')->name('logout');

    // Soumissions : limitées à 5 tentatives par minute et par IP. Sans cela,
    // rien ne borne les essais de mot de passe, l'énumération de comptes par
    // le formulaire d'inscription, ni l'envoi massif d'emails de
    // réinitialisation.
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/forgot-password', 'sendResetLink')->name('password.email');
        Route::post('/reset-password', 'resetPassword')->name('password.update');
    });

    // Les jetons de vérification sont à usage unique et expirent : la limite
    // vise ici le balayage de jetons, d'où un quota plus large.
    Route::middleware('throttle:20,1')
        ->get('/verify-email/{token}', 'verifyEmail')
        ->name('verify.email');
});

// Routes protégées par authentification
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/visualization/climate', 'visualizationClimate')->name('visualization.climate');
        Route::get('/visualization/economic', 'visualizationEconomic')->name('visualization.economic');
        Route::get('/cartography', 'cartography')->name('cartography');
        Route::get('/api/map-data', 'getMapData')->name('api.map.data');
    });

    // Gestion des données
    Route::controller(DataController::class)->prefix('data')->name('data.')->group(function () {
        Route::get('/climate/create', 'createClimate')->name('climate.create');
        Route::post('/climate', 'storeClimate')->name('climate.store');
        Route::get('/economic/create', 'createEconomic')->name('economic.create');
        Route::post('/economic', 'storeEconomic')->name('economic.store');
        Route::get('/my-data', 'myData')->name('my');
    });

    // Profil utilisateur
    Route::controller(UserController::class)->prefix('user')->name('user.')->group(function () {
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/profile', 'updateProfile')->name('profile.update');
        Route::get('/change-password', 'showChangePasswordForm')->name('password.change');
        Route::post('/change-password', 'changePassword')->name('password.update');
    });

    // Routes admin
    Route::middleware('admin')->controller(AdminController::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/reports', 'reports')->name('reports');
        Route::get('/logs', 'logs')->name('logs');
        Route::post('/logs/clear', 'clearLogs')->name('logs.clear');
        Route::get('/emails', 'emails')->name('emails');
        Route::post('/emails/test', 'sendTestEmail')->name('emails.test');
        Route::post('/emails/newsletter', 'sendNewsletter')->name('emails.newsletter');
        Route::get('/validation', 'validationPage')->name('validation');
        Route::post('/validate/{type}/{id}', 'validateData')->name('validate');
        Route::post('/reject/{type}/{id}', 'rejectData')->name('reject');
        Route::get('/users', 'users')->name('users');
        Route::put('/users/{id}/role', 'updateUserRole')->name('user.role');
        Route::put('/users/{id}/status', 'updateUserStatus')->name('user.status');
    });
});
