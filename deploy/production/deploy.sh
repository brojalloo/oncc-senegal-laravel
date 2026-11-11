#!/usr/bin/env bash
set -euo pipefail
umask 077

cd -- "$(dirname -- "$0")"
request=${SSH_ORIGINAL_COMMAND:-"deploy-oncc ${1:-}"}
if [[ ! "$request" =~ ^deploy-oncc\ (ghcr\.io/brojalloo/oncc-senegal-laravel@sha256:[a-f0-9]{64})$ ]]; then
    echo 'Empreinte ONCC de production attendue.' >&2
    exit 1
fi
image=${BASH_REMATCH[1]}

exec 9> .deploy.lock
flock -n 9 || { echo 'Un déploiement ONCC est déjà en cours.' >&2; exit 1; }

export ONCC_IMAGE="$image"
compose=(docker compose --env-file .env -f compose.yaml)
"${compose[@]}" pull app
if "${compose[@]}" up -d --no-build --wait --wait-timeout 240 app; then
    printf '%s\n' "$image" > .current-image.new
    mv -f .current-image.new .current-image
    exit 0
fi

"${compose[@]}" logs --tail 100 app >&2 || true
if [[ -s .current-image ]]; then
    echo 'Rétablissement du dernier conteneur validé.' >&2
    ONCC_IMAGE=$(cat .current-image)
    "${compose[@]}" up -d --no-build --wait --wait-timeout 240 app
else
    "${compose[@]}" stop app
fi
exit 1
