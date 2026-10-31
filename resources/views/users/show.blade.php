@extends('layouts.app')

@section('title', 'Detail Pengguna - ' . $user->name)
@section('page_heading', 'Detail Data Pengguna')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-gradient-info">{{ strtoupper($user->role) }}</span>
                    <span class="text-muted small">{{ $user->email }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i>
                    <span>Edit Profil</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card p-4">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Peran Akses (RBAC)</span>
                @if($user->role === 'admin')
                    <span class="badge badge-gradient-danger fs-6">Administrator (Akses Penuh)</span>
                @elseif($user->role === 'operator')
                    <span class="badge badge-gradient-info fs-6">Logistics Operator</span>
                @else
                    <span class="badge badge-gradient-success fs-6">Customer (Pengirim/Penerima)</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Perusahaan / Afiliasi</span>
                <div class="fw-bold fs-6">{{ $user->company_name ?? '-' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Alamat Email</span>
                <div class="fw-semibold">{{ $user->email }}</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Nomor Telepon</span>
                <div class="fw-semibold">{{ $user->phone ?? '-' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Kargo / Shipments Terkait</span>
                <div class="fw-bold fs-5">{{ $user->shipments_count }} Pengiriman</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Tanggal Bergabung</span>
                <div class="text-muted small">{{ $user->created_at->format('d M Y, H:i') }} WIB</div>
            </div>
        </div>
    </div>
</div>
@endsection
