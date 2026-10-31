@extends('layouts.app')

@section('title', 'Tambah Lokasi Baru')
@section('page_heading', 'Tambah Lokasi Baru')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Tambah Lokasi / Hub Baru</h4>
                <p class="text-muted small mb-0">Daftarkan pelabuhan, bandara, atau hub transit logistik dengan koordinat geospasial.</p>
            </div>
            <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <div class="card p-4">
        <form action="{{ route('locations.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Kode UN/LOCODE <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="code" 
                           class="form-control font-monospace @error('code') is-invalid @enderror" 
                           placeholder="Contoh: IDTPP" 
                           value="{{ old('code') }}" 
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
                           placeholder="Contoh: Port of Tanjung Priok" 
                           value="{{ old('name') }}" 
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
                           placeholder="Jakarta" 
                           value="{{ old('city') }}" 
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
                           placeholder="Indonesia" 
                           value="{{ old('country') }}" 
                           required>
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Tipe Fasilitas <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="PORT" {{ old('type') === 'PORT' ? 'selected' : '' }}>PORT (Pelabuhan Laut)</option>
                        <option value="AIRPORT" {{ old('type') === 'AIRPORT' ? 'selected' : '' }}>AIRPORT (Bandara Kargo)</option>
                        <option value="WAREHOUSE" {{ old('type') === 'WAREHOUSE' ? 'selected' : '' }}>WAREHOUSE (Gudang)</option>
                        <option value="HUB" {{ old('type') === 'HUB' ? 'selected' : '' }}>HUB (Distribusi)</option>
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
                           placeholder="-6.1022" 
                           value="{{ old('latitude') }}" 
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
                           placeholder="106.8833" 
                           value="{{ old('longitude') }}" 
                           required>
                    @error('longitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('locations.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <span>Simpan Lokasi</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
