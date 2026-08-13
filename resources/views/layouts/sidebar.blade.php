<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse" data-bs-parent="#sidebarContainer">
    <div class="sidebar-header px-3 py-4 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-1">Navigation</h5>
            <small class="text-muted">Accès rapide</small>
        </div>
        <button type="button" class="btn-close d-md-none" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-expanded="false" aria-label="Close sidebar"></button>
    </div>

    <div class="position-sticky pt-3">
        <ul class="nav flex-column px-3">
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->routeIs('visualization.climate') ? 'active' : '' }}" href="{{ route('visualization.climate') }}">
                    <i class="fas fa-cloud-rain me-2"></i> Données Climatiques
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->routeIs('visualization.economic') ? 'active' : '' }}" href="{{ route('visualization.economic') }}">
                    <i class="fas fa-chart-line me-2"></i> Données Économiques
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->routeIs('cartography') ? 'active' : '' }}" href="{{ route('cartography') }}">
                    <i class="fas fa-map-marked-alt me-2"></i> Cartographie
                </a>
            </li>

            <hr class="my-3">

            <li class="nav-item mb-1">
                <a class="nav-link {{ request()->routeIs('data.*') ? 'active' : '' }}" href="{{ route('data.my') }}">
                    <i class="fas fa-database me-2"></i> Mes Données
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link" href="{{ route('data.climate.create') }}">
                    <i class="fas fa-plus-circle me-2"></i> Ajouter Données Climat
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link" href="{{ route('data.economic.create') }}">
                    <i class="fas fa-plus-circle me-2"></i> Ajouter Données Éco
                </a>
            </li>

            @if(Auth::user()->role === 'admin')
                <hr class="my-3">
                <li class="nav-item mb-3">
                    <h6 class="sidebar-heading text-uppercase text-muted">Administration</h6>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-chart-pie me-2"></i> Dashboard Admin
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.validation') ? 'active' : '' }}" href="{{ route('admin.validation') }}">
                        <i class="fas fa-check-circle me-2"></i> Validation
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                        <i class="fas fa-users me-2"></i> Utilisateurs
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                        <i class="fas fa-file-alt me-2"></i> Rapports
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}" href="{{ route('admin.logs') }}">
                        <i class="fas fa-list me-2"></i> Logs Système
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('admin.emails') ? 'active' : '' }}" href="{{ route('admin.emails') }}">
                        <i class="fas fa-envelope me-2"></i> Emails
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
