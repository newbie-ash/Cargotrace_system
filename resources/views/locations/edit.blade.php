@extends('layouts.app')

@section('title', 'Edit Lokasi - ' . $location->name)
@section('page_heading', 'Edit Data Lokasi')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Edit Lokasi: {{ $location->name }}</h4>
                <p class="text-muted small mb-0">Perbarui koordinat geografis atau metadata hub kargo.</p>
            </div>
            <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <div class="card p-4">
        <form action="{{ route('locations.update', $location) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Kode UN/LOCODE <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="code" 
                           class="form-control font-monospace @error('code') is-invalid @enderror" 
                           value="{{ old('code', $location->code) }}" 
                           required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold small">Nama Fasilitas / Pelabuhan <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $location->name) }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Kota (City) <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="city" 
                           class="form-control @error('city') is-invalid @enderror" 
                           value="{{ old('city', $location->city) }}" 
                           required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Negara (Country) <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="country" 
                           class="form-control @error('country') is-invalid @enderror" 
                           value="{{ old('country', $location->country) }}" 
                           required>
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Tipe Fasilitas <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="PORT" {{ old('type', $location->type) === 'PORT' ? 'selected' : '' }}>PORT (Pelabuhan Laut)</option>
                        <option value="AIRPORT" {{ old('type', $location->type) === 'AIRPORT' ? 'selected' : '' }}>AIRPORT (Bandara Kargo)</option>
                        <option value="WAREHOUSE" {{ old('type', $location->type) === 'WAREHOUSE' ? 'selected' : '' }}>WAREHOUSE (Gudang)</option>
                        <option value="HUB" {{ old('type', $location->type) === 'HUB' ? 'selected' : '' }}>HUB (Distribusi)</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Latitude (-90 s/d 90) <span class="text-danger">*</span></label>
                    <input type="number" 
                           step="0.0000001" 
                           name="latitude" 
                           class="form-control font-monospace @error('latitude') is-invalid @enderror" 
                           value="{{ old('latitude', $location->latitude) }}" 
                           required>
                    @error('latitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Longitude (-180 s/d 180) <span class="text-danger">*</span></label>
                    <input type="number" 
                           step="0.0000001" 
                           name="longitude" 
                           class="form-control font-monospace @error('longitude') is-invalid @enderror" 
                           value="{{ old('longitude', $location->longitude) }}" 
                           required>
                    @error('longitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('locations.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <span>Perbarui Lokasi</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
