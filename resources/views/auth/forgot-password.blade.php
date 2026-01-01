@extends('layouts.app')

@section('title', 'Mot de passe oublié - ONCC Sénégal')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="text-center mb-4">
                    <i class="fas fa-key"></i> Mot de passe oublié
                </h2>
                
                <p class="text-muted text-center mb-4">
                    Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                </p>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane"></i> Envoyer le lien de réinitialisation
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
