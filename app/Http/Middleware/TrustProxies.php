<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Les plateformes d'hébergement terminent le TLS devant le conteneur et
     * transmettent la requête en clair, avec l'en-tête X-Forwarded-Proto. Sans
     * ce middleware, l'application se croit en HTTP : elle n'émet pas HSTS et
     * génère des URL http://.
     *
     * L'adresse du répartiteur n'est ni fixe ni connue à l'avance sur ces
     * plateformes, d'où le joker. Cela suppose que le conteneur ne soit
     * joignable qu'à travers ce répartiteur — c'est le cas d'un déploiement
     * PaaS, où aucun port n'est exposé directement. Si vous publiez le
     * conteneur en direct sur Internet, restreignez cette valeur aux adresses
     * de votre répartiteur.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * Les en-têtes de transfert à prendre en compte.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
