@extends('layouts.app')

@section('title', 'Master Data Lokasi & Hub')
@section('page_heading', 'Master Data Lokasi & Hub')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">Daftar Titik Lokasi, Pelabuhan & Hub Global</h4>
                <p class="text-muted small mb-0">
                    Master data titik koordinat transit, pelabuhan laut (Port), bandara (Airport), dan hub logistik.
                </p>
            </div>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('locations.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Lokasi Baru</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card mb-4 p-3">
        <form action="{{ route('locations.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, kode UN/LOCODE, kota, negara..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <select name="type" class="form-select">
                    <option value="">Semua Tipe Hub</option>
                    <option value="PORT" {{ request('type') === 'PORT' ? 'selected' : '' }}>PORT (Pelabuhan Laut)</option>
                    <option value="AIRPORT" {{ request('type') === 'AIRPORT' ? 'selected' : '' }}>AIRPORT (Bandar Udara)</option>
                    <option value="WAREHOUSE" {{ request('type') === 'WAREHOUSE' ? 'selected' : '' }}>WAREHOUSE (Gudang Logistik)</option>
                    <option value="HUB" {{ request('type') === 'HUB' ? 'selected' : '' }}>HUB (Distribusi)</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                @if(request()->hasAny(['search', 'type']))
                    <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Locations Table -->
    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode Hub</th>
                        <th>Nama Lokasi</th>
                        <th>Kota & Negara</th>
                        <th>Tipe</th>
                        <th>Koordinat (Lat, Lng)</th>
                        <th>Aktivitas Kargo</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $loc)
                        <tr>
                            <td class="ps-4 fw-bold font-monospace">{{ $loc->code }}</td>
                            <td>
                                <div class="fw-semibold">{{ $loc->name }}</div>
                            </td>
                            <td>
                                <div>{{ $loc->city }}</div>
                                <small class="text-muted">{{ $loc->country }}</small>
                            </td>
                            <td>
                                <span class="badge badge-gradient-info">
                                    {{ $loc->type }}
                                </span>
                            </td>
                            <td>
                                <small class="font-monospace text-muted">{{ number_format($loc->latitude, 4) }}, {{ number_format($loc->longitude, 4) }}</small>
                            </td>
                            <td>
                                <small class="d-block text-muted">Origin: {{ $loc->origin_shipments_count }}</small>
                                <small class="d-block text-muted">Dest: {{ $loc->destination_shipments_count }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('locations.show', $loc) }}" class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('locations.edit', $loc) }}" class="btn btn-outline-secondary" title="Edit Lokasi">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-secondary text-danger" 
                                                title="Hapus Lokasi"
                                                onclick="confirmDelete('{{ route('locations.destroy', $loc) }}', '{{ $loc->name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-geo-alt fs-2 d-block mb-2 opacity-50"></i>
                                Tidak ada data lokasi yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($locations->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Menampilkan {{ $locations->firstItem() }} - {{ $locations->lastItem() }} dari {{ $locations->total() }} lokasi</small>
                {{ $locations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
@include('components.confirm-modal', [
    'modalId' => 'deleteConfirmModal',
    'title' => 'Konfirmasi Hapus Lokasi',
    'message' => 'Apakah Anda yakin ingin menghapus data lokasi ini? Tindakan ini tidak dapat dibatalkan.'
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
