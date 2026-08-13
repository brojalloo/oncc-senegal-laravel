@extends('layouts.app')

@section('title', 'Logs système')

@push('styles')
<style>
.premium-card .card-body {
    padding: 2rem 2.5rem;
}
@media (max-width: 768px) {
    .premium-card .card-body {
        padding: 1.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header avec particules -->
    <div class="page-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); position: relative;">
        <div class="particles-container">
            @for($i = 0; $i < 8; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 10) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
            @endfor
        </div>
        <h1>
            <i class="fas fa-clipboard-list me-2"></i>
            Logs Système
        </h1>
        <p>
            <span class="pulse-dot"></span>
            Consultation des logs d'erreurs et d'événements système
        </p>
        <div class="header-badge">
            <i class="fas fa-server me-1"></i>
            Journal système
        </div>
    </div>

    <div class="premium-card">
        <div class="card-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-content">
                    <div class="header-icon" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Fichier de logs: laravel.log</h5>
                        <small class="opacity-75">Dernières entrées du journal système</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-light rounded-pill" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Actualiser
                    </button>
                    <form action="{{ route('admin.logs.clear') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light rounded-pill" 
                                onclick="return confirm('Êtes-vous sûr de vouloir vider les logs ?')">
                            <i class="fas fa-trash me-1"></i> Vider
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert-premium success mb-4">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(empty($logs))
                <div class="alert-premium info">
                    <div class="alert-icon"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <strong>Logs vides</strong>
                        <p class="mb-0">Aucun log trouvé. Le fichier de logs est vide ou n'existe pas.</p>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="premium-table glass-table" id="logsTable">
                        <thead>
                            <tr>
                                <th width="180"><i class="fas fa-clock me-1"></i> Date/Heure</th>
                                <th width="120"><i class="fas fa-tag me-1"></i> Niveau</th>
                                <th><i class="fas fa-comment me-1"></i> Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ $log['date'] ?? '' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $levelClass = match($log['level'] ?? 'INFO') {
                                                'ERROR' => 'danger',
                                                'WARNING' => 'warning',
                                                'DEBUG' => 'secondary',
                                                'INFO' => 'info',
                                                'CRITICAL' => 'danger',
                                                'EMERGENCY' => 'danger',
                                                'REQUEST' => 'success',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge-premium {{ $levelClass }}">
                                            @if($log['level'] == 'REQUEST')
                                                <i class="fas fa-globe me-1"></i>
                                            @endif
                                            {{ $log['level'] ?? 'INFO' }}
                                        </span>
                                    </td>
                                    <td>
                                        <code style="font-size: 0.85em; word-break: break-all; white-space: pre-wrap; color: #64748b;">{{ $log['message'] ?? '' }}</code>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3 rounded-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="text-muted">
                            <i class="fas fa-database me-2"></i>
                            <strong>{{ count($logs) }}</strong> entrées affichées 
                            <span class="mx-2">|</span>
                            <strong>{{ $totalLines }}</strong> lignes au total
                        </span>
                        <div>
                            <span class="badge-premium success me-2">
                                <i class="fas fa-globe"></i> REQUEST
                            </span>
                            <span class="badge-premium info me-2">
                                <i class="fas fa-info-circle"></i> INFO
                            </span>
                            <span class="badge-premium warning me-2">
                                <i class="fas fa-exclamation-triangle"></i> WARNING
                            </span>
                            <span class="badge-premium danger">
                                <i class="fas fa-times-circle"></i> ERROR
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
