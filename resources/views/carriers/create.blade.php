@extends('layouts.app')

@section('title', 'Tambah Carrier Baru')
@section('page_heading', 'Tambah Carrier Baru')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Tambah Carrier Baru</h4>
                <p class="text-muted small mb-0">Daftarkan operator armada transportasi pengiriman baru ke sistem.</p>
            </div>
            <a href="{{ route('carriers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <div class="card p-4">
        <form action="{{ route('carriers.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Kode Carrier <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="code" 
                           class="form-control font-monospace @error('code') is-invalid @enderror" 
                           placeholder="Contoh: MAERSK" 
                           value="{{ old('code') }}" 
                           required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold small">Nama Perusahaan Carrier <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Contoh: Maersk Line Logistics" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Moda Transportasi Utama <span class="text-danger">*</span></label>
                    <select name="transport_mode" class="form-select @error('transport_mode') is-invalid @enderror" required>
                        <option value="">Pilih Moda Transportasi</option>
                        <option value="SEA" {{ old('transport_mode') === 'SEA' ? 'selected' : '' }}>SEA (Kapal Laut / Kontainer)</option>
                        <option value="AIR" {{ old('transport_mode') === 'AIR' ? 'selected' : '' }}>AIR (Pesawat Kargo)</option>
                        <option value="LAND" {{ old('transport_mode') === 'LAND' ? 'selected' : '' }}>LAND (Truk / Kereta Logistik)</option>
                        <option value="MULTIMODAL" {{ old('transport_mode') === 'MULTIMODAL' ? 'selected' : '' }}>MULTIMODAL (Kombinasi Laut-Darat-Udara)</option>
                    </select>
                    @error('transport_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Email Operasional / CS</label>
                    <input type="email" 
                           name="contact_email" 
                           class="form-control @error('contact_email') is-invalid @enderror" 
                           placeholder="support@carrier.com" 
                           value="{{ old('contact_email') }}">
                    @error('contact_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Nomor Telepon Hotline</label>
                    <input type="text" 
                           name="contact_phone" 
                           class="form-control @error('contact_phone') is-invalid @enderror" 
                           placeholder="+62 21 555 1234" 
                           value="{{ old('contact_phone') }}">
                    @error('contact_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6 d-flex align-items-center">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small" for="isActiveCheck">
                            Status Aktif (Dapat dipilih saat membuat pengiriman)
                        </label>
                    </div>
                </div>

                <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('carriers.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <span>Simpan Data Carrier</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
