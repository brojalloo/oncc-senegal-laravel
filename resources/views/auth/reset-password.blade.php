@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe - ONCC Sénégal')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="text-center mb-4">
                    <i class="fas fa-lock"></i> Nouveau mot de passe
                </h2>
                
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Réinitialiser le mot de passe
                    </button>
                </form>

                <hr>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Retour à la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
