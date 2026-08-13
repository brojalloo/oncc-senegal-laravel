@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@push('styles')
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header admin-header" style="position: relative;">
        <div class="particles-container">
            @for($i = 0; $i < 8; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
            @endfor
        </div>
        <h1>
            <i class="fas fa-users me-2"></i>
            Gestion des Utilisateurs
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Administrez les comptes utilisateurs et leurs permissions
        </p>
        <div class="header-badge">
            <i class="fas fa-database me-1"></i>
            {{ \App\Models\User::count() }} utilisateurs
        </div>
    </div>

    <!-- Statistiques avec le design du dashboard -->
    <div class="stats-grid mb-4">
        <div class="stat-card info">
            <div class="trend-badge">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Utilisateurs</div>
                <div class="stat-value">{{ \App\Models\User::count() }}</div>
                <div class="stat-unit">Comptes enregistrés</div>
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
                <div class="stat-value">{{ \App\Models\User::where('statut', 'actif')->count() }}</div>
                <div class="stat-unit">Comptes actifs</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="trend-badge">
                <i class="fas fa-pause"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Inactifs</div>
                <div class="stat-value">{{ \App\Models\User::where('statut', 'inactif')->count() }}</div>
                <div class="stat-unit">Comptes désactivés</div>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="trend-badge">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Administrateurs</div>
                <div class="stat-value">{{ \App\Models\User::where('role', 'admin')->count() }}</div>
                <div class="stat-unit">Super utilisateurs</div>
            </div>
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

    <!-- Table des utilisateurs -->
    <div class="premium-card">
        <div class="card-header gradient-primary">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h5>Liste des Utilisateurs</h5>
                    <small>{{ $users->total() }} utilisateur(s) enregistré(s)</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="premium-table glass-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i> ID</th>
                                <th><i class="fas fa-user me-1"></i> Nom</th>
                                <th><i class="fas fa-envelope me-1"></i> Email</th>
                                <th><i class="fas fa-building me-1"></i> Organisation</th>
                                <th><i class="fas fa-user-tag me-1"></i> Rôle</th>
                                <th><i class="fas fa-toggle-on me-1"></i> Statut</th>
                                <th><i class="fas fa-envelope-open me-1"></i> Email Vérifié</th>
                                <th><i class="fas fa-calendar me-1"></i> Inscription</th>
                                <th><i class="fas fa-cogs me-1"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td><strong>#{{ $user->id }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.85rem;">
                                                {{ strtoupper(substr($user->nom, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->nom }}</strong>
                                                @if($user->role === 'admin')
                                                    <span class="badge-premium danger ms-1" style="font-size: 0.65rem;">Admin</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->organisation ?? '-' }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge-premium danger">
                                                <i class="fas fa-crown"></i> Admin
                                            </span>
                                        @elseif($user->role === 'chercheur')
                                            <span class="badge-premium primary">
                                                <i class="fas fa-microscope"></i> Chercheur
                                            </span>
                                        @elseif($user->role === 'collectivite')
                                            <span class="badge-premium info">
                                                <i class="fas fa-landmark"></i> Collectivité
                                            </span>
                                        @else
                                            <span class="badge-premium secondary">
                                                <i class="fas fa-user"></i> {{ ucfirst($user->role) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->statut === 'actif')
                                            <span class="badge-premium success">
                                                <i class="fas fa-check-circle"></i> Actif
                                            </span>
                                        @else
                                            <span class="badge-premium danger">
                                                <i class="fas fa-ban"></i> Inactif
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge-premium success">
                                                <i class="fas fa-check"></i> Vérifié
                                            </span>
                                        @else
                                            <span class="badge-premium warning">
                                                <i class="fas fa-clock"></i> En attente
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if($user->id !== auth()->id())
                                            <div class="d-flex gap-1">
                                                <!-- Bouton modifier rôle -->
                                                <button type="button" class="btn-action primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#roleModal{{ $user->id }}"
                                                        title="Modifier le rôle">
                                                    <i class="fas fa-user-edit"></i>
                                                </button>

                                                <!-- Bouton activer/désactiver -->
                                                @if($user->statut === 'actif')
                                                    <form action="{{ route('admin.user.status', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="statut" value="inactif">
                                                        <button type="submit" class="btn-action warning" 
                                                                onclick="return confirm('Désactiver ce compte ?')"
                                                                title="Désactiver le compte">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.user.status', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="statut" value="actif">
                                                        <button type="submit" class="btn-action success" 
                                                                onclick="return confirm('Activer ce compte ?')"
                                                                title="Activer le compte">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <!-- Modal pour modifier le rôle -->
                                            <div class="modal fade" id="roleModal{{ $user->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                                                        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-user-edit me-2"></i>
                                                                Modifier le rôle de {{ $user->nom }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.user.role', $user->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Rôle actuel : 
                                                                        <span class="text-primary">{{ ucfirst($user->role) }}</span>
                                                                    </label>
                                                                    <select name="role" class="form-select" required>
                                                                        <option value="">-- Sélectionner un rôle --</option>
                                                                        <option value="public" {{ $user->role === 'public' ? 'selected' : '' }}>
                                                                            👤 Public
                                                                        </option>
                                                                        <option value="collectivite" {{ $user->role === 'collectivite' ? 'selected' : '' }}>
                                                                            🏛️ Collectivité
                                                                        </option>
                                                                        <option value="chercheur" {{ $user->role === 'chercheur' ? 'selected' : '' }}>
                                                                            🔬 Chercheur
                                                                        </option>
                                                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                                                            👑 Administrateur
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <div class="alert-premium info" style="padding: 1rem;">
                                                                    <strong><i class="fas fa-info-circle me-1"></i> Permissions des rôles :</strong>
                                                                    <ul class="mb-0 mt-2 ps-3">
                                                                        <li><strong>Public</strong> : Consultation uniquement</li>
                                                                        <li><strong>Collectivité</strong> : Saisie de données économiques</li>
                                                                        <li><strong>Chercheur</strong> : Saisie de données climatiques et économiques</li>
                                                                        <li><strong>Administrateur</strong> : Accès complet</li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                                                                    Annuler
                                                                </button>
                                                                <button type="submit" class="btn-premium primary">
                                                                    <i class="fas fa-save"></i> Enregistrer
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge-premium info">
                                                <i class="fas fa-user-check"></i> Vous
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}
                </div>
            @else
                <div class="alert-premium info">
                    <div class="alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <strong>Aucun utilisateur</strong>
                        <p class="mb-0">Aucun utilisateur trouvé dans le système.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-action.primary { background: #3b82f6; color: white; }
.btn-action.success { background: #10b981; color: white; }
.btn-action.warning { background: #f59e0b; color: white; }
.btn-action.danger { background: #ef4444; color: white; }
.btn-action:hover { transform: scale(1.1); }
</style>
@endsection
