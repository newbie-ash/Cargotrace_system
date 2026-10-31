@extends('layouts.app')

@section('title', 'Detail Lokasi - ' . $location->name)
@section('page_heading', 'Detail Data Lokasi')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">{{ $location->name }}</h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-gradient-info font-monospace">{{ $location->code }}</span>
                    <span class="text-muted small">{{ $location->city }}, {{ $location->country }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                        <span>Edit Lokasi</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card p-4">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Tipe Fasilitas</span>
                <span class="badge badge-gradient-accent fs-6">{{ $location->type }}</span>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Koordinat Geospasial</span>
                <div class="font-monospace fw-bold">{{ $location->latitude }}, {{ $location->longitude }}</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Kargo Berangkat (Origin)</span>
                <div class="fw-bold fs-5">{{ $location->origin_shipments_count }} Pengiriman</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Kargo Masuk (Destination)</span>
                <div class="fw-bold fs-5">{{ $location->destination_shipments_count }} Pengiriman</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Total Titik Singgah (Tracking Events)</span>
                <div class="fw-bold fs-5">{{ $location->tracking_events_count }} Checkpoints</div>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block mb-1">Terdaftar Pada</span>
                <div class="text-muted small">{{ $location->created_at->format('d M Y, H:i') }} WIB</div>
            </div>
        </div>
    </div>
</div>
@endsection
