<footer class="modern-footer pt-5">
    <div class="footer-content px-4 py-5">
        <div class="container-fluid">
            <div class="row gy-4">
                <div class="col-md-4">
                    <h5 class="footer-title mb-3">
                        <i class="fas fa-cloud-sun me-2"></i> ONCC Sénégal
                    </h5>
                    <p class="footer-text text-slate-300">
                        Observatoire National sur les Changements Climatiques du Sénégal. Surveillance et analyse des données environnementales et économiques pour un avenir durable.
                    </p>
                    <div class="social-links mt-3 d-flex gap-2">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <div class="col-md-2">
                    <h5 class="footer-title mb-3">Liens Rapides</h5>
                    <ul class="footer-links list-unstyled">
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-chevron-right me-2"></i> Accueil</a></li>
                        <li><a href="{{ route('cartography') }}"><i class="fas fa-chevron-right me-2"></i> Cartographie</a></li>
                        @auth
                            @if(Auth::user()->peutSaisirClimatique())
                                <li><a href="{{ route('data.climate.create') }}"><i class="fas fa-chevron-right me-2"></i> Données Climat</a></li>
                            @endif
                            @if(Auth::user()->peutSaisirEconomique())
                                <li><a href="{{ route('data.economic.create') }}"><i class="fas fa-chevron-right me-2"></i> Données Économiques</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>

                <div class="col-md-3">
                    <h5 class="footer-title mb-3">Ressources</h5>
                    <ul class="footer-links list-unstyled">
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i> Documentation</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i> Rapports Annuels</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i> API Documentation</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i> Guides d'utilisation</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h5 class="footer-title mb-3">Contact</h5>
                    <ul class="footer-contact list-unstyled text-slate-300">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><span>Dakar, Sénégal</span></li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i><span>+221 76 881 59 72</span></li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i><span>ibrahimadiallo0899@gmail.com</span></li>
                        <li><i class="fas fa-clock me-2"></i><span>Lun - Ven: 8h - 17h</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-stats row text-center mt-4 pt-4 border-top border-slate-700">
                <div class="col-md-3 mb-3">
                    <div class="stat-item p-4 rounded-3 h-100 bg-slate-950/80">
                        <i class="fas fa-users fa-2x mb-2 text-slate-200"></i>
                        <h4 class="mb-0 text-white">{{ \App\Models\User::count() }}</h4>
                        <small class="text-slate-400">Utilisateurs Actifs</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-item p-4 rounded-3 h-100 bg-slate-950/80">
                        <i class="fas fa-cloud fa-2x mb-2 text-slate-200"></i>
                        <h4 class="mb-0 text-white">{{ \App\Models\DonneeClimatique::count() }}</h4>
                        <small class="text-slate-400">Données Climatiques</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-item p-4 rounded-3 h-100 bg-slate-950/80">
                        <i class="fas fa-chart-line fa-2x mb-2 text-slate-200"></i>
                        <h4 class="mb-0 text-white">{{ \App\Models\DonneeEconomique::count() }}</h4>
                        <small class="text-slate-400">Données Économiques</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-item p-4 rounded-3 h-100 bg-slate-950/80">
                        <i class="fas fa-map-marked-alt fa-2x mb-2 text-slate-200"></i>
                        <h4 class="mb-0 text-white">{{ \App\Models\Region::count() }}</h4>
                        <small class="text-slate-400">Régions Couvertes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom py-4 border-top border-slate-700">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0 text-slate-400">&copy; {{ date('Y') }} ONCC Sénégal. Tous droits réservés Prod By BROTO.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="footer-bottom-link me-2">Politique de confidentialité</a>
                    <a href="#" class="footer-bottom-link me-2">Conditions d'utilisation</a>
                    <a href="#" class="footer-bottom-link">Mentions légales</a>
                </div>
            </div>
        </div>
    </div>
</footer>
