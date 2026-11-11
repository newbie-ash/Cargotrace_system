<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Delixie</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #EDF7F6;
            background-image:
                radial-gradient(circle at 90% 12%, rgba(19, 41, 61, 0.90) 0%, rgba(38, 93, 122, 0.75) 35%, rgba(103, 199, 198, 0.60) 60%, transparent 85%),
                radial-gradient(circle at 10% 88%, rgba(103, 199, 198, 0.35) 0%, rgba(167, 228, 219, 0.25) 50%, transparent 75%),
                linear-gradient(135deg, #EFF7F6 0%, #E3F4F1 50%, #D4F0EB 100%);
            color: #13293D;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .error-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.75) 0%, rgba(217, 242, 238, 0.45) 100%);
            border: 1px solid rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 20px 50px rgba(19, 41, 61, 0.08);
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #265D7A;
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -2px;
        }
        .error-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #13293D;
            margin-bottom: 0.75rem;
        }
        .error-desc {
            color: #265D7A;
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 1.75rem;
            font-weight: 400;
        }
        .btn-portal {
            background-color: #265D7A;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.75rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 8px 20px rgba(38, 93, 122, 0.25);
        }
        .btn-portal:hover {
            background-color: #13293D;
            color: #ffffff;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <div class="error-title">Akses Ditolak</div>
        <div class="error-desc">
            Anda tidak memiliki izin RBAC untuk mengakses modul ini. Silakan kembali ke dashboard operasional Anda.
        </div>
        <a href="/dashboard" class="btn-portal">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>
</body>
</html>
