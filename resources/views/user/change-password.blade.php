@extends('layouts.app')

@section('title', 'Changer le mot de passe - ONCC Sénégal')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <span class="eyebrow">Mon compte</span>
        <h1>Changer le mot de passe</h1>
        <p class="text-muted mb-0">
            Le nouveau mot de passe prend effet immédiatement. Vous restez connecté sur cet appareil.
        </p>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card premium-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('user.password.update') }}" class="premium-form">
                        @csrf

                        <div>
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password"
                                   name="current_password"
                                   required
                                   autocomplete="current-password"
                                   autofocus>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   required
                                   autocomplete="new-password">
                            <small class="text-muted">Huit caractères au minimum.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password">
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="submit" class="btn-premium">
                                <i class="fas fa-key me-1"></i> Changer le mot de passe
                            </button>
                            <a href="{{ route('user.profile') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
