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
                    <div class="input-group">
                        <input type="text" 
                               name="name" 
                               id="locationNameInput"
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $location->name) }}" 
                               required>
                        <button type="button" class="btn btn-outline-secondary" id="lookupCoordBtn" title="Cari koordinat via OpenStreetMap Nominatim">
                            <i class="bi bi-geo-alt"></i> Cari GPS
                        </button>
                    </div>
                    <small id="lookupResultText" class="text-success d-none mt-1" style="font-size: 0.75rem;"></small>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
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
                           id="latitudeInput"
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
                           id="longitudeInput"
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('lookupCoordBtn');
    const nameInput = document.getElementById('locationNameInput');
    const latInput = document.getElementById('latitudeInput');
    const lngInput = document.getElementById('longitudeInput');
    const feedback = document.getElementById('lookupResultText');

    if (btn) {
        btn.addEventListener('click', function () {
            const query = nameInput.value.trim();
            if (!query) {
                alert('Silakan ketik nama fasilitas atau pelabuhan terlebih dahulu.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            feedback.classList.add('d-none');

            fetch(`{{ route('locations.lookup') }}?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.data) {
                        latInput.value = data.data.latitude;
                        lngInput.value = data.data.longitude;
                        feedback.textContent = `Ditemukan: ${data.data.display_name}`;
                        feedback.classList.remove('d-none');
                    } else {
                        alert(data.message || 'Koordinat tidak ditemukan.');
                    }
                })
                .catch(err => {
                    alert('Gagal menghubungi layanan geocoding.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-geo-alt"></i> Cari GPS';
                });
        });
    }
});
</script>
@endsection
