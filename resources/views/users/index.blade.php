@extends('layouts.app')

@section('title', 'Manajemen Pengguna & RBAC')
@section('page_heading', 'Manajemen Pengguna & RBAC')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">Manajemen Pengguna & Hak Akses (RBAC)</h4>
                <p class="text-muted small mb-0">
                    Otorisasi server-side untuk peran Administrator, Logistics Operator, dan Customer.
                </p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i>
                <span>Tambah Pengguna Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card mb-4 p-3">
        <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, email, perusahaan..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <select name="role" class="form-select">
                    <option value="">Semua Peran (Role)</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="operator" {{ request('role') === 'operator' ? 'selected' : '' }}>Logistics Operator</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer (Pengirim/Penerima)</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Lengkap</th>
                        <th>Email & Kontak</th>
                        <th>Peran (Role)</th>
                        <th>Perusahaan</th>
                        <th>Total Shipments</th>
                        <th>Terdaftar</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" 
                                         style="width: 32px; height: 32px; background: #F2EFE9; font-size: 0.8rem; color: #1A1A1C;">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="fw-semibold">{{ $u->name }}</div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $u->email }}</div>
                                <small class="text-muted">{{ $u->phone ?? '-' }}</small>
                            </td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="badge badge-gradient-danger">Administrator</span>
                                @elseif($u->role === 'operator')
                                    <span class="badge badge-gradient-info">Logistics Operator</span>
                                @else
                                    <span class="badge badge-gradient-success">Customer</span>
                                @endif
                            </td>
                            <td>{{ $u->company_name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-gradient-accent">{{ $u->shipments_count }} items</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $u->created_at->format('d M Y') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('users.show', $u) }}" class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $u) }}" class="btn btn-outline-secondary" title="Edit Pengguna">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(Auth::id() !== $u->id)
                                        <button type="button" 
                                                class="btn btn-outline-secondary text-danger" 
                                                title="Hapus Pengguna"
                                                onclick="confirmDelete('{{ route('users.destroy', $u) }}', '{{ $u->name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                                Tidak ada data pengguna yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} pengguna</small>
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
@include('components.confirm-modal', [
    'modalId' => 'deleteConfirmModal',
    'title' => 'Konfirmasi Hapus Pengguna',
    'message' => 'Apakah Anda yakin ingin menghapus akun pengguna ini? Tindakan ini tidak dapat dibatalkan.'
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
