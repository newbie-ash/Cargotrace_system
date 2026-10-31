@extends('layouts.app')

@section('title', 'Edit Pengguna - ' . $user->name)
@section('page_heading', 'Edit Data Pengguna')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Edit Pengguna: {{ $user->name }}</h4>
                <p class="text-muted small mb-0">Perbarui profil, informasi kontak, atau peran akses pengguna.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <div class="card p-4">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $user->name) }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $user->email) }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" 
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Minimal 6 karakter">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" 
                           name="password_confirmation" 
                           class="form-control" 
                           placeholder="Ulangi kata sandi baru">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Peran Akses (Role) <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Customer (Pengirim/Penerima)</option>
                        <option value="operator" {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}>Logistics Operator (Petugas)</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh)</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Nomor Telepon</label>
                    <input type="text" 
                           name="phone" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Nama Perusahaan / Organisasi</label>
                    <input type="text" 
                           name="company_name" 
                           class="form-control @error('company_name') is-invalid @enderror" 
                           value="{{ old('company_name', $user->company_name) }}">
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <span>Perbarui Pengguna</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
