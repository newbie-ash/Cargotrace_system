@extends('layouts.app')

@section('title', 'Master Data Carriers')
@section('page_heading', 'Master Data Carriers')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">Daftar Carrier & Armada Logistik</h4>
                <p class="text-muted small mb-0">
                    Master data penyedia jasa transportasi kargo laut, udara, darat, dan multimodal.
                </p>
            </div>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('carriers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Carrier Baru</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card mb-4 p-3">
        <form action="{{ route('carriers.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau kode carrier..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <select name="mode" class="form-select">
                    <option value="">Semua Moda Transportasi</option>
                    <option value="SEA" {{ request('mode') === 'SEA' ? 'selected' : '' }}>SEA (Laut)</option>
                    <option value="AIR" {{ request('mode') === 'AIR' ? 'selected' : '' }}>AIR (Udara)</option>
                    <option value="LAND" {{ request('mode') === 'LAND' ? 'selected' : '' }}>LAND (Darat)</option>
                    <option value="MULTIMODAL" {{ request('mode') === 'MULTIMODAL' ? 'selected' : '' }}>MULTIMODAL</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                @if(request()->hasAny(['search', 'mode']))
                    <a href="{{ route('carriers.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Carriers Table -->
    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Carrier</th>
                        <th>Moda Transportasi</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Shipments</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carriers as $carrier)
                        <tr>
                            <td class="ps-4 fw-bold font-monospace">{{ $carrier->code }}</td>
                            <td>
                                <div class="fw-semibold">{{ $carrier->name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gradient-info">
                                    {{ $carrier->transport_mode }}
                                </span>
                            </td>
                            <td>
                                <small class="d-block text-muted">{{ $carrier->contact_email ?? '-' }}</small>
                                <small class="d-block text-muted">{{ $carrier->contact_phone ?? '' }}</small>
                            </td>
                            <td>
                                @if($carrier->is_active)
                                    <span class="badge badge-gradient-success">Aktif</span>
                                @else
                                    <span class="badge badge-gradient-neutral">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-gradient-accent">{{ $carrier->shipments_count }} items</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('carriers.show', $carrier) }}" class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                        <a href="{{ route('carriers.edit', $carrier) }}" class="btn btn-outline-secondary" title="Edit Carrier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-secondary text-danger" 
                                                title="Hapus Carrier"
                                                onclick="confirmDelete('{{ route('carriers.destroy', $carrier) }}', '{{ $carrier->name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-truck fs-2 d-block mb-2 opacity-50"></i>
                                Tidak ada data carrier yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($carriers->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Menampilkan {{ $carriers->firstItem() }} - {{ $carriers->lastItem() }} dari {{ $carriers->total() }} carrier</small>
                {{ $carriers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
@include('components.confirm-modal', [
    'modalId' => 'deleteConfirmModal',
    'title' => 'Konfirmasi Hapus Carrier',
    'message' => 'Apakah Anda yakin ingin menghapus data carrier ini? Tindakan ini tidak dapat dibatalkan.'
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
