@extends('layouts.app')

@section('title', 'Shipments & Tracking')
@section('page_heading', 'Shipments & Tracking')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">Daftar Pengiriman Global (Shipments)</h4>
                <p class="text-muted small mb-0">
                    Monitor pergerakan kargo interkontinental, status terkini, dan anomali keterlambatan rute.
                </p>
            </div>
            @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                <a href="{{ route('shipments.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat Pengiriman Baru</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card mb-4 p-3">
        <form action="{{ route('shipments.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari No. Shipment / Ref..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <select name="carrier_id" class="form-select">
                    <option value="">Semua Carrier</option>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                            {{ $carrier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <select name="delay" class="form-select">
                    <option value="">Semua Jadwal</option>
                    <option value="on_time" {{ request('delay') === 'on_time' ? 'selected' : '' }}>On-Time</option>
                    <option value="delayed" {{ request('delay') === 'delayed' ? 'selected' : '' }}>Delayed (Terlambat)</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                @if(request()->hasAny(['search', 'status', 'carrier_id', 'delay']))
                    <a href="{{ route('shipments.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Shipment</th>
                        <th>Rute (Origin &rarr; Dest)</th>
                        <th>Carrier & Kargo</th>
                        <th>Status</th>
                        <th>Kondisi Jadwal</th>
                        <th>Tiba (Est / Act)</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('shipments.show', $shipment) }}" class="fw-bold text-decoration-none text-dark font-monospace">
                                    {{ $shipment->shipment_number }}
                                </a>
                                @if($shipment->reference_number)
                                    <small class="d-block text-muted">Ref: {{ $shipment->reference_number }}</small>
                                @endif
                                @if(!Auth::user()->isCustomer() && $shipment->customer)
                                    <small class="d-block text-muted">Cust: {{ $shipment->customer->name }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 font-monospace small fw-semibold">
                                    <span>{{ $shipment->originLocation?->code ?? '-' }}</span>
                                    <i class="bi bi-arrow-right text-muted"></i>
                                    <span>{{ $shipment->destinationLocation?->code ?? '-' }}</span>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    {{ Str::limit($shipment->originLocation?->city, 10) }} &rarr; {{ Str::limit($shipment->destinationLocation?->city, 10) }}
                                </small>
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $shipment->carrier?->name ?? '-' }}</div>
                                <span class="badge badge-gradient-info" style="font-size: 0.7rem;">
                                    {{ $shipment->cargo_type }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'CREATED' => 'badge-gradient-neutral',
                                        'BOOKED' => 'badge-gradient-info',
                                        'PICKED_UP' => 'badge-gradient-info',
                                        'IN_TRANSIT' => 'badge-gradient-accent',
                                        'DELIVERED' => 'badge-gradient-success',
                                        'CANCELLED' => 'badge-gradient-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$shipment->current_status] ?? 'badge-gradient-neutral' }}">
                                    {{ $shipment->current_status }}
                                </span>
                            </td>
                            <td>
                                @if($shipment->is_delayed)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Delayed (+{{ $shipment->delay_duration_hours }}j)
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i> On-Time
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="d-block fw-semibold">
                                    {{ $shipment->actual_arrival ? $shipment->actual_arrival->format('d M Y H:i') : ($shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y H:i') : $shipment->expected_arrival->format('d M Y H:i')) }}
                                </small>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    Exp: {{ $shipment->expected_arrival->format('d M Y') }}
                                </small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline-secondary" title="Detail & Tracking Timeline">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                                        <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-outline-secondary" title="Edit Metadata">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if(Auth::user()->isAdmin())
                                        <button type="button" 
                                                class="btn btn-outline-secondary text-danger" 
                                                title="Hapus Shipment"
                                                onclick="confirmDelete('{{ route('shipments.destroy', $shipment) }}', '{{ $shipment->shipment_number }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada pengiriman yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shipments->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Menampilkan {{ $shipments->firstItem() }} - {{ $shipments->lastItem() }} dari {{ $shipments->total() }} shipment</small>
                {{ $shipments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
@include('components.confirm-modal', [
    'modalId' => 'deleteConfirmModal',
    'title' => 'Konfirmasi Hapus Shipment',
    'message' => 'Apakah Anda yakin ingin menghapus data shipment ini beserta riwayat tracking dan items terkait? Tindakan ini tidak dapat dibatalkan.'
])

<script>
function confirmDelete(actionUrl, name) {
    const form = document.getElementById('confirmDeleteForm');
    if (form) {
        form.action = actionUrl;
        const msg = document.getElementById('deleteItemName');
        if (msg) msg.textContent = name;
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }
}
</script>
@endsection
