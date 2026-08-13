@extends('layouts.app')

@section('title', 'Gestion des emails')

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header">
        <h1>
            <i class="fas fa-envelope me-2"></i>
            Gestion des Emails
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Envoyez des newsletters et gérez les communications par email
        </p>
        <div class="header-badge">
            <i class="fas fa-paper-plane me-1"></i>
            Newsletter & Communications
        </div>
    </div>

    <!-- Statistiques avec design dashboard -->
    <div class="stats-grid mb-4">
        <div class="stat-card info">
            <div class="trend-badge">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Utilisateurs Inscrits</div>
                <div class="stat-value">{{ $emailStats['total_users'] }}</div>
                <div class="stat-unit">Destinataires potentiels</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="trend-badge">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Utilisateurs Actifs</div>
                <div class="stat-value">{{ $emailStats['active_users'] }}</div>
                <div class="stat-unit">Emails vérifiés</div>
            </div>
        </div>
        <div class="stat-card info">
            <div class="trend-badge">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Serveur SMTP</div>
                <div class="stat-value">{{ config('mail.mailers.smtp.host', 'N/A') }}</div>
                <div class="stat-unit">{{ config('mail.from.address', 'Non configuré') }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Test d'email -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card h-100">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div>
                            <h5>Test d'envoi d'email</h5>
                            <small>Vérifiez votre configuration SMTP</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('test_success'))
                        <div class="alert-premium success mb-3">
                            <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                            <div>{{ session('test_success') }}</div>
                        </div>
                    @endif

                    @if(session('test_error'))
                        <div class="alert-premium danger mb-3">
                            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div>{{ session('test_error') }}</div>
                        </div>
                    @endif

                    <form action="{{ route('admin.emails.test') }}" method="POST" class="premium-form">
                        @csrf
                        <div class="mb-4">
                            <label for="test_email" class="form-label">
                                <i class="fas fa-at text-info"></i> Adresse email de test
                            </label>
                            <input type="email" class="form-control @error('test_email') is-invalid @enderror" 
                                   id="test_email" name="test_email"
                                   placeholder="votre.email@exemple.com" required>
                            @error('test_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn-premium info">
                            <i class="fas fa-paper-plane"></i> Envoyer email de test
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Configuration SMTP -->
        <div class="col-lg-6 mb-4">
            <div class="premium-card h-100">
                <div class="card-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <h5>Configuration SMTP</h5>
                            <small>Paramètres actuels du serveur mail</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="premium-table glass-table">
                            <tbody>
                                <tr>
                                    <td><strong><i class="fas fa-plug text-primary me-2"></i>Driver</strong></td>
                                    <td><span class="badge-premium primary">{{ config('mail.default') }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-server text-info me-2"></i>Serveur SMTP</strong></td>
                                    <td>{{ config('mail.mailers.smtp.host') }}</td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-network-wired text-success me-2"></i>Port</strong></td>
                                    <td><span class="badge-premium success">{{ config('mail.mailers.smtp.port') }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-user text-warning me-2"></i>Utilisateur</strong></td>
                                    <td>
                                        @if(config('mail.mailers.smtp.username'))
                                            <span class="badge-premium success"><i class="fas fa-check"></i> Configuré</span>
                                        @else
                                            <span class="badge-premium danger"><i class="fas fa-times"></i> Non configuré</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-shield-alt text-accent me-2"></i>Encryption</strong></td>
                                    <td><span class="badge-premium info">{{ strtoupper(config('mail.mailers.smtp.encryption', 'none')) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-envelope text-danger me-2"></i>Expéditeur</strong></td>
                                    <td>{{ config('mail.from.address') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    @if(!config('mail.mailers.smtp.username'))
                    <div class="alert-premium warning mt-3">
                        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <strong>Configuration requise</strong>
                            <p class="mb-0 small">Modifiez le fichier <code>.env</code> pour configurer vos identifiants SMTP.</p>
                        </div>
                    </div>
                    @else
                    <div class="alert-premium success mt-3">
                        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <strong>Configuration détectée !</strong>
                            <p class="mb-0 small">Utilisez le formulaire de test pour vérifier.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter -->
    <div class="premium-card">
        <div class="card-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div>
                    <h5>Envoyer une Newsletter</h5>
                    <small>Communiquez avec vos utilisateurs</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('newsletter_success'))
                <div class="alert-premium success mb-4">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div>{{ session('newsletter_success') }}</div>
                </div>
            @endif

            @if(session('newsletter_error'))
                <div class="alert-premium danger mb-4">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>{{ session('newsletter_error') }}</div>
                </div>
            @endif

            <form action="{{ route('admin.emails.newsletter') }}" method="POST" class="premium-form">
                @csrf
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <label for="newsletter_subject" class="form-label">
                            <i class="fas fa-heading text-success"></i> Sujet de la newsletter
                        </label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="newsletter_subject" name="subject"
                               placeholder="Sujet de votre newsletter" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-4">
                        <label class="form-label">
                            <i class="fas fa-users text-primary"></i> Destinataires
                        </label>
                        <div class="mt-2">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" id="target_all" name="target" value="all" checked>
                                <label class="form-check-label" for="target_all">
                                    <span class="badge-premium primary">👥</span> Tous les utilisateurs vérifiés
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" id="target_admin" name="target" value="admin">
                                <label class="form-check-label" for="target_admin">
                                    <span class="badge-premium danger">👑</span> Administrateurs uniquement
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="target_chercheur" name="target" value="chercheur">
                                <label class="form-check-label" for="target_chercheur">
                                    <span class="badge-premium info">🔬</span> Chercheurs uniquement
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="newsletter_content" class="form-label">
                        <i class="fas fa-file-alt text-warning"></i> Contenu de la newsletter (HTML supporté)
                    </label>
                    <textarea class="form-control @error('content') is-invalid @enderror" 
                              id="newsletter_content" name="content" rows="10" required 
                              style="font-family: monospace;"><h2>Newsletter ONCC-SN</h2>

<p>Bonjour,</p>

<p>Voici les dernières nouvelles de l'Observatoire National sur les Changements Climatiques du Sénégal.</p>

<p>Cordialement,<br>
L'équipe ONCC-SN</p></textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Vous pouvez utiliser du HTML pour formater votre message.
                    </small>
                </div>

                <button type="submit" class="btn-premium success" 
                        onclick="return confirm('Êtes-vous sûr de vouloir envoyer cette newsletter ?')">
                    <i class="fas fa-paper-plane"></i> Envoyer la newsletter
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
