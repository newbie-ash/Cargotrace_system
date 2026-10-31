@extends('layouts.app')

@section('title', 'Detail Carrier - ' . $carrier->name)
@section('page_heading', 'Detail Data Carrier')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">{{ $carrier->name }}</h4>
                <span class="badge badge-gradient-info font-monospace">{{ $carrier->code }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('carriers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('carriers.edit', $carrier) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                        <span>Edit Carrier</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card p-4">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Moda Transportasi</span>
                <span class="badge badge-gradient-accent fs-6">{{ $carrier->transport_mode }}</span>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Status Operasional</span>
                @if($carrier->is_active)
                    <span class="badge badge-gradient-success">Aktif Beroperasi</span>
                @else
                    <span class="badge badge-gradient-neutral">Nonaktif</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Email Layanan</span>
                <div class="fw-semibold">{{ $carrier->contact_email ?? '-' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Telepon Hotline</span>
                <div class="fw-semibold">{{ $carrier->contact_phone ?? '-' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Total Kargo Terhubung</span>
                <div class="fw-bold fs-5">{{ $carrier->shipments_count }} Pengiriman</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Terdaftar Pada</span>
                <div class="text-muted small">{{ $carrier->created_at->format('d M Y, H:i') }} WIB</div>
            </div>
        </div>
    </div>
</div>
@endsection
