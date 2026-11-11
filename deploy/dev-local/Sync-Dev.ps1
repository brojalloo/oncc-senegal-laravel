param([switch]$Build)

$ErrorActionPreference = 'Stop'
$context = 'desktop-linux'
$compose = Join-Path $PSScriptRoot 'compose.yaml'
$envFile = Join-Path $PSScriptRoot '.env'

function Invoke-Docker {
    & docker --context $context @args
    if ($LASTEXITCODE -ne 0) {
        throw "Docker a echoue (code $LASTEXITCODE)."
    }
}

Invoke-Docker info --format '{{.OSType}}' | Out-Null

if (-not (Test-Path -LiteralPath $envFile)) {
    $bytes = New-Object byte[] 32
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try {
        $rng.GetBytes($bytes)
    } finally {
        $rng.Dispose()
    }
    $value = 'base64:' + [Convert]::ToBase64String($bytes)
    $stream = [System.IO.File]::Open($envFile, [System.IO.FileMode]::CreateNew)
    try {
        $writer = New-Object System.IO.StreamWriter($stream)
        try {
            $writer.WriteLine("ONCC_APP_KEY=$value")
        } finally {
            $writer.Dispose()
        }
    } finally {
        $stream.Dispose()
    }
}

$composeArgs = @('compose', '--env-file', $envFile, '-f', $compose)
$previousImage = $env:DEV_IMAGE
try {
    if ($Build) {
        $env:DEV_IMAGE = 'oncc-senegal-dev:local'
        Invoke-Docker @composeArgs up --build --wait --wait-timeout 180
    } else {
        $repository = 'ghcr.io/brojalloo/oncc-senegal-dev'
        Invoke-Docker pull "${repository}:dev"
        $image = Invoke-Docker image inspect "${repository}:dev" --format '{{range .RepoDigests}}{{println .}}{{end}}' |
            Where-Object { $_ -like "$repository@sha256:*" } |
            Select-Object -First 1
        if ($image -notlike "$repository@sha256:*") {
            throw "Empreinte de l'image introuvable pour $repository."
        }
        $env:DEV_IMAGE = $image
        Invoke-Docker @composeArgs up --no-build --pull missing --wait --wait-timeout 180
    }
} finally {
    $env:DEV_IMAGE = $previousImage
}

Write-Host 'Application disponible sur http://127.0.0.1:8080'
