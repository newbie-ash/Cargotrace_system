<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | CargoTrace</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #020617;
            background-image:
                radial-gradient(circle at 80% 15%, rgba(6, 182, 212, 0.16), transparent 45%),
                radial-gradient(circle at 15% 85%, rgba(245, 158, 11, 0.10), transparent 45%),
                linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 48px 48px, 48px 48px;
            color: #F8FAFC;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .error-card {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        .error-code {
            font-size: 3.75rem;
            font-weight: 800;
            color: #22D3EE;
            line-height: 1;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 24px rgba(34, 211, 238, 0.35);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <h4 class="text-white fw-bold mb-2">Akses Ditolak</h4>
        <p class="small mb-4" style="color: #94A3B8; font-size: 0.88rem;">
            {{ $exception->getMessage() ?: 'Akun Anda tidak memiliki wewenang untuk membuka halaman ini.' }}
        </p>

        @php
            $homeLabel = Auth::check() ? 'Kembali ke Dashboard' : 'Kembali ke Beranda';
            $homeIcon = Auth::check() ? 'bi-grid-1x2' : 'bi-house';
            $homeRoute = Auth::check() ? route('dashboard') : route('landing');
        @endphp

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
            <a href="{{ $homeRoute }}" class="btn fw-bold py-2 px-4" style="background: #FFFFFF; color: #020617; border-radius: 9999px;">
                <i class="bi {{ $homeIcon }} me-1"></i> {{ $homeLabel }}
            </a>
            @auth
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn w-100 py-2 px-4 fw-semibold" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16); color: #F8FAFC; border-radius: 9999px;">
                        <i class="bi bi-box-arrow-right me-1"></i> Ganti Akun
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn py-2 px-4 fw-semibold" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.16); color: #F8FAFC; border-radius: 9999px;">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk (Login)
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
