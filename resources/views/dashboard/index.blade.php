@extends('layouts.app')

@section('title', 'Operational Dashboard')
@section('page_heading', 'Operational Dashboard')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <!-- Welcome Header Card -->
    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge badge-gradient-accent mb-2">CargoTrace Network</span>
                <h3 class="mb-1 fw-bold">Selamat Datang, {{ Auth::user()->name }}</h3>
                <p class="text-muted mb-0 small">
                    Peran Akses: <strong>{{ strtoupper(Auth::user()->role) }}</strong>. Sistem visibilitas pengiriman global aktif memantau rute dan status kargo.
                </p>
            </div>
            <div class="d-flex gap-2">
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('carriers.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Carrier</span>
                    </a>
                    <a href="{{ route('locations.create') }}" class="btn btn-secondary">
                        <i class="bi bi-geo-alt"></i>
                        <span>Tambah Lokasi</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card theme-accent">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Total Shipments</span>
                        <div class="kpi-value">{{ number_format($stats['total_shipments']) }}</div>
                        <small class="text-muted" style="font-size: 11px;">Aktif & Terjadwal</small>
                    </div>
                    <div class="kpi-icon-squircle icon-accent">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card theme-emerald">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Active Carriers</span>
                        <div class="kpi-value">{{ number_format($stats['active_carriers']) }}</div>
                        <small class="text-muted" style="font-size: 11px;">Armada Laut / Udara</small>
                    </div>
                    <div class="kpi-icon-squircle icon-emerald">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card theme-blue">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Global Hubs</span>
                        <div class="kpi-value">{{ number_format($stats['total_locations']) }}</div>
                        <small class="text-muted" style="font-size: 11px;">Pelabuhan & Bandara</small>
                    </div>
                    <div class="kpi-icon-squircle icon-blue">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card theme-purple">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Terdaftar di Sistem</span>
                        <div class="kpi-value">{{ number_format($stats['total_users']) }}</div>
                        <small class="text-muted" style="font-size: 11px;">Admin, Operator, Customer</small>
                    </div>
                    <div class="kpi-icon-squircle icon-purple">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation & Recent Audit Logs (Admin Only) -->
    <div class="row g-3">
        <div class="col-12 col-lg-{{ Auth::user()->isAdmin() ? '7' : '12' }}">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Akses Cepat Master Data</span>
                    <span class="badge badge-gradient-info font-monospace">RBAC Terverifikasi</span>
                </div>
                <div class="p-4">
                    <p class="text-muted small mb-3">
                        Navigasi cepat untuk mengelola data operasional pengiriman, armada logistik, dan titik koordinat transit:
                    </p>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <a href="{{ route('carriers.index') }}" class="card text-decoration-none p-3 h-100 border">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="kpi-icon-squircle icon-accent" style="width: 38px; height: 38px;">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">Data Carrier</div>
                                        <small class="text-muted">Kelola armada laut, udara, darat</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="{{ route('locations.index') }}" class="card text-decoration-none p-3 h-100 border">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="kpi-icon-squircle icon-blue" style="width: 38px; height: 38px;">
                                        <i class="bi bi-geo-alt"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">Data Lokasi / Hub</div>
                                        <small class="text-muted">Pelabuhan dan koordinat GPS</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @if(Auth::user()->isAdmin())
                            <div class="col-12 col-sm-6">
                                <a href="{{ route('users.index') }}" class="card text-decoration-none p-3 h-100 border">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="kpi-icon-squircle icon-purple" style="width: 38px; height: 38px;">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Manajemen User</div>
                                            <small class="text-muted">Kelola akun dan role akses</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(Auth::user()->isAdmin())
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Aktivitas Audit Log Terbaru</span>
                        <i class="bi bi-shield-check text-muted"></i>
                    </div>
                    <div class="p-3">
                        @forelse($stats['recent_audits'] as $audit)
                            <div class="d-flex align-items-start gap-2 py-2 border-bottom" style="border-color: var(--colors-hairline) !important;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 28px; height: 28px; background: #F2EFE9; color: #1A1A1C;">
                                    <i class="bi bi-journal-text" style="font-size: 0.8rem;"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold small text-truncate">{{ $audit->action }}</span>
                                        <small class="text-muted" style="font-size: 10px;">{{ $audit->created_at->diffForHumans() }}</small>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Oleh: {{ $audit->user?->name ?? 'System' }} (IP: {{ $audit->ip_address ?? 'Local' }})
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-journal-x fs-4 d-block mb-1"></i>
                                Belum ada aktivitas mutasi yang tercatat di audit log.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
