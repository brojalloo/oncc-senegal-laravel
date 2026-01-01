@extends('layouts.app')

@section('title', 'Mon Profil')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/premium-design.css') }}">
<style>
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
        margin: 0 auto 1.5rem;
        box-shadow: 0 10px 30px rgba(249, 115, 22, 0.4);
    }
    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }
    .profile-role {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .profile-role.admin { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; }
    .profile-role.chercheur { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; }
    .profile-role.collectivite { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; }
    .profile-role.public { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; }
    
    .stat-mini-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .stat-mini-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .stat-mini-card .icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.25rem;
        color: white;
    }
    .stat-mini-card .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-dark);
    }
    .stat-mini-card .label {
        font-size: 0.8rem;
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); position: relative;">
        <div class="particles-container">
            @for($i = 0; $i < 8; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
            @endfor
        </div>
        <h1>
            <i class="fas fa-user-circle me-2"></i>
            Mon Profil
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Gérez vos informations personnelles et vos paramètres de compte
        </p>
        <div class="header-badge">
            <i class="fas fa-shield-alt me-1"></i>
            {{ ucfirst(Auth::user()->role) }}
        </div>
    </div>

    @if(session('success'))
        <div class="alert-premium success mb-4">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <strong>Succès !</strong>
                <p class="mb-0">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-premium danger mb-4">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <strong>Erreur !</strong>
                <p class="mb-0">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Colonne gauche - Avatar et Stats -->
        <div class="col-lg-4">
            <!-- Carte Profil -->
            <div class="premium-card mb-4" style="text-align: center;">
                <div class="card-body py-5">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($user->nom, 0, 1)) }}
                    </div>
                    <h4 class="profile-name">{{ $user->nom }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    <span class="profile-role {{ $user->role }}">
                        <i class="fas fa-user-shield me-1"></i>
                        {{ ucfirst($user->role) }}
                    </span>
                    <div class="mt-3">
                        @if($user->statut === 'actif')
                            <span class="badge-premium success">
                                <i class="fas fa-check-circle"></i> Compte actif
                            </span>
                        @else
                            <span class="badge-premium warning">
                                <i class="fas fa-clock"></i> En attente
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistiques Mini -->
            <div class="premium-card mb-4">
                <div class="card-header gradient-info">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h5>Mes Statistiques</h5>
                            <small>Aperçu de vos contributions</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-mini-card">
                                <div class="icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                                    <i class="fas fa-cloud-sun"></i>
                                </div>
                                <div class="value">{{ $user->donneesClimatiques->count() }}</div>
                                <div class="label">Données Climat</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-mini-card">
                                <div class="icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="value">{{ $user->donneesEconomiques->count() }}</div>
                                <div class="label">Données Éco</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-mini-card">
                                <div class="icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="value">{{ $user->rapports->count() }}</div>
                                <div class="label">Rapports</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-mini-card">
                                <div class="icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="value">{{ $user->created_at->diffInDays(now()) }}</div>
                                <div class="label">Jours actif</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center text-muted">
                        <small><i class="fas fa-clock me-1"></i> Membre depuis le {{ $user->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne droite - Formulaires -->
        <div class="col-lg-8">
            <!-- Informations du compte -->
            <div class="premium-card mb-4">
                <div class="card-header gradient-primary">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div>
                            <h5>Informations du Compte</h5>
                            <small>Mettez à jour vos informations personnelles</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.profile.update') }}" method="POST" class="premium-form">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-user text-primary"></i> Nom complet
                                    </label>
                                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" 
                                           value="{{ old('nom', $user->nom) }}" required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-envelope text-info"></i> Email
                                    </label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-building text-success"></i> Organisation
                                    </label>
                                    <input type="text" name="organisation" class="form-control @error('organisation') is-invalid @enderror" 
                                           value="{{ old('organisation', $user->organisation) }}" placeholder="Votre organisation">
                                    @error('organisation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-phone text-warning"></i> Téléphone
                                    </label>
                                    <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror" 
                                           value="{{ old('telephone', $user->telephone) }}" placeholder="+221 XX XXX XX XX">
                                    @error('telephone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-premium primary">
                            <i class="fas fa-save"></i> Mettre à jour le profil
                        </button>
                    </form>
                </div>
            </div>

            <!-- Changement de mot de passe -->
            <div class="premium-card">
                <div class="card-header gradient-warning">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5>Sécurité du Compte</h5>
                            <small>Modifiez votre mot de passe régulièrement</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.password.update') }}" method="POST" class="premium-form">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-lock text-secondary"></i> Mot de passe actuel
                                    </label>
                                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-key text-warning"></i> Nouveau mot de passe
                                    </label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Min. 8 caractères</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-check-double text-success"></i> Confirmer
                                    </label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-premium warning">
                            <i class="fas fa-key"></i> Changer le mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
