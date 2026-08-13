<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ONCC Sénégal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&family=Source+Sans+3:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-card {
            max-width: 600px;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card-header">
                <i class="fas fa-user-plus fa-2x mb-2"></i>
                <h3>Créer un Compte</h3>
                <p class="mb-0">Rejoignez l'Observatoire National</p>
            </div>

            <div class="auth-card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> 
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">
                                <i class="fas fa-user"></i> Nom *
                            </label>
                            <input type="text" 
                                   class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" 
                                   name="nom" 
                                   value="{{ old('nom') }}" 
                                   placeholder="Votre nom"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">
                                <i class="fas fa-user"></i> Prénom *
                            </label>
                            <input type="text" 
                                   class="form-control @error('prenom') is-invalid @enderror" 
                                   id="prenom" 
                                   name="prenom" 
                                   value="{{ old('prenom') }}" 
                                   placeholder="Votre prénom"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Adresse Email *
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="votre.email@exemple.com"
                               required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Mot de passe *
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••"
                                   required>
                            <small class="text-muted">Min. 8 caractères</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock"></i> Confirmer *
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="••••••••"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">
                            <i class="fas fa-user-tag"></i> Type de compte *
                        </label>
                        <select class="form-select @error('role') is-invalid @enderror" 
                                id="role" 
                                name="role" 
                                required>
                            <option value="">-- Sélectionner --</option>
                            <option value="public" {{ old('role') === 'public' ? 'selected' : '' }}>
                                👤 Public (Consultation uniquement)
                            </option>
                            <option value="collectivite" {{ old('role') === 'collectivite' ? 'selected' : '' }}>
                                🏛️ Collectivité (Saisie données économiques)
                            </option>
                            <option value="chercheur" {{ old('role') === 'chercheur' ? 'selected' : '' }}>
                                👨‍🔬 Chercheur (Saisie toutes données)
                            </option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="region" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Région
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="region" 
                                   name="region" 
                                   value="{{ old('region') }}" 
                                   placeholder="Ex: Dakar">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">
                                <i class="fas fa-phone"></i> Téléphone
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="telephone" 
                                   name="telephone" 
                                   value="{{ old('telephone') }}" 
                                   placeholder="Ex: 771234567">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="organisation" class="form-label">
                            <i class="fas fa-building"></i> Organisation (Optionnel)
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="organisation" 
                               name="organisation" 
                               value="{{ old('organisation') }}" 
                               placeholder="Nom de votre organisation">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>

                    <hr>

                    <div class="text-center">
                        <p class="text-muted mb-2">Vous avez déjà un compte ?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
