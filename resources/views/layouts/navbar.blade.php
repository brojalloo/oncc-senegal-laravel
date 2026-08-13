<nav class="navbar navbar-expand-lg navbar-dark modern-navbar py-3">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
            <div class="navbar-logo">
                <i class="fas fa-cloud-sun"></i>
            </div>
            <div class="brand-text">
                <span class="d-block fw-semibold">ONCC Sénégal</span>
                <small class="text-light opacity-75">Observatoire National</small>
            </div>
        </a>

        @auth
            <button class="btn btn-outline-light me-3 d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-expanded="false" aria-controls="sidebar">
                <i class="fas fa-bars"></i>
            </button>
        @endauth

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                @auth
                    <li class="nav-item me-3">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-home me-1"></i> Accueil
                        </a>
                    </li>
                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item me-3">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-shield-alt me-1"></i> Admin
                            </a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-dropdown" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="ms-2">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end modern-dropdown" aria-labelledby="userDropdown">
                            <li class="dropdown-header py-3">
                                <div class="text-center">
                                    <div class="user-avatar-large mb-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <strong>{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</strong>
                                    <br>
                                    <small class="text-muted">{{ ucfirst(Auth::user()->role) }}</small>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('user.profile') }}">
                                    <i class="fas fa-user-circle me-2"></i> Mon Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('user.password.change') }}">
                                    <i class="fas fa-key me-2"></i> Changer le mot de passe
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item me-2">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i> Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i> Inscription
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
