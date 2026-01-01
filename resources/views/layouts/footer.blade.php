<footer class="modern-footer mt-5">
    <div class="footer-content">
        <div class="container-fluid">
            <div class="row">
                <!-- À propos -->
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">
                        <i class="fas fa-cloud-sun"></i> ONCC Sénégal
                    </h5>
                    <p class="footer-text">
                        Observatoire National sur les Changements Climatiques du Sénégal. 
                        Surveillance et analyse des données environnementales et économiques pour un avenir durable.
                    </p>
                    <div class="social-links mt-3">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Liens rapides -->
                <div class="col-md-2 mb-4">
                    <h5 class="footer-title">Liens Rapides</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('dashboard') }}"><i class="fas fa-chevron-right"></i> Accueil</a></li>
                        <li><a href="{{ route('cartography') }}"><i class="fas fa-chevron-right"></i> Cartographie</a></li>
                        <li><a href="{{ route('data.climate.create') }}"><i class="fas fa-chevron-right"></i> Données Climat</a></li>
                        <li><a href="{{ route('data.economic.create') }}"><i class="fas fa-chevron-right"></i> Données Économiques</a></li>
                    </ul>
                </div>

                <!-- Ressources -->
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">Ressources</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Documentation</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Rapports Annuels</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> API Documentation</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Guides d'utilisation</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">Contact</h5>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Dakar, Sénégal</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+221 76 881 59 72 </span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>ibrahimadiallo0899@gmail.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Lun - Ven: 8h - 17h</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Stats bar -->
            <div class="footer-stats row text-center mt-4 pt-4 border-top border-light">
                <div class="col-md-3 mb-3">
                    <div class="stat-item">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4 class="mb-0">{{ \App\Models\User::count() }}</h4>
                        <small>Utilisateurs Actifs</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-item">
                        <i class="fas fa-cloud fa-2x mb-2"></i>
                        <h4 class="mb-0">{{ \App\Models\DonneeClimatique::count() }}</h4>
                        <small>Données Climatiques</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-item">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <h4 class="mb-0">{{ \App\Models\DonneeEconomique::count() }}</h4>
                        <small>Données Économiques</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-item">
                        <i class="fas fa-map-marked-alt fa-2x mb-2"></i>
                        <h4 class="mb-0">{{ \App\Models\Region::count() }}</h4>
                        <small>Régions Couvertes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} ONCC Sénégal. Tous droits réservés Prod By BROTO.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="footer-bottom-link">Politique de confidentialité</a>
                    <span class="mx-2">|</span>
                    <a href="#" class="footer-bottom-link">Conditions d'utilisation</a>
                    <span class="mx-2">|</span>
                    <a href="#" class="footer-bottom-link">Mentions légales</a>
                </div>
            </div>
        </div>
    </div>
</footer>
