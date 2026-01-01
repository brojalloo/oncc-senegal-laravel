<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('visualization.climate') ? 'active' : '' }}" href="{{ route('visualization.climate') }}">
                    <i class="fas fa-cloud-rain"></i> Données Climatiques
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('visualization.economic') ? 'active' : '' }}" href="{{ route('visualization.economic') }}">
                    <i class="fas fa-chart-line"></i> Données Économiques
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cartography') ? 'active' : '' }}" href="{{ route('cartography') }}">
                    <i class="fas fa-map-marked-alt"></i> Cartographie
                </a>
            </li>
            
            <hr>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('data.*') ? 'active' : '' }}" href="{{ route('data.my') }}">
                    <i class="fas fa-database"></i> Mes Données
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('data.climate.create') }}">
                    <i class="fas fa-plus-circle"></i> Ajouter Données Climat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('data.economic.create') }}">
                    <i class="fas fa-plus-circle"></i> Ajouter Données Éco
                </a>
            </li>
            
            @if(Auth::user()->role === 'admin')
                <hr>
                <li class="nav-item">
                    <h6 class="sidebar-heading px-3 mt-2 mb-1 text-muted">
                        <span>Administration</span>
                    </h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-chart-pie"></i> Dashboard Admin
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.validation') ? 'active' : '' }}" href="{{ route('admin.validation') }}">
                        <i class="fas fa-check-circle"></i> Validation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                        <i class="fas fa-users"></i> Utilisateurs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                        <i class="fas fa-file-alt"></i> Rapports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}" href="{{ route('admin.logs') }}">
                        <i class="fas fa-list"></i> Logs Système
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.emails') ? 'active' : '' }}" href="{{ route('admin.emails') }}">
                        <i class="fas fa-envelope"></i> Emails
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
