<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Log Masuk') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-dbkl.png') }}?v={{ @filemtime(public_path('images/logo-dbkl.png')) ?: time() }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-dbkl.png') }}?v={{ @filemtime(public_path('images/logo-dbkl.png')) ?: time() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { height: 100%; margin: 0; }
        .guest-page {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }
        .guest-shell {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }
        .guest-brand {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            padding: 2rem 1.5rem 2.25rem;
            color: #fff;
            background:
                radial-gradient(ellipse 80% 50% at 20% 0%, rgba(59,130,246,.25), transparent 55%),
                radial-gradient(ellipse 60% 40% at 100% 100%, rgba(220,38,38,.12), transparent 50%),
                linear-gradient(160deg, #0b1f4d 0%, #153a7a 55%, #1e4d96 100%);
        }
        .guest-brand-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.15rem;
            text-align: center;
        }
        .guest-portal-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: min(100%, 26rem);
            margin-inline: auto;
            color: rgba(255,255,255,.82);
            font-size: 0.75rem;
            font-weight: 650;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .guest-portal-divider::before,
        .guest-portal-divider::after {
            content: "";
            flex: 1 1 auto;
            height: 1px;
            background: rgba(255,255,255,.3);
        }
        .guest-logo-plate {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            max-width: min(100%, 26rem);
            padding: 0.85rem 1rem;
            border-radius: 0.85rem;
            background: #fff;
            box-shadow: 0 10px 28px rgba(0,0,0,.2);
            border: 1px solid rgba(255,255,255,.35);
            margin-inline: auto;
        }
        .guest-logo-plate img {
            display: block;
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 7.5rem;
            object-fit: contain;
        }
        .guest-brief {
            width: min(100%, 26rem);
            margin-inline: auto;
            text-align: center;
        }
        .guest-brief-intro {
            margin: 0 0 1rem;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 0.92rem;
            font-weight: 600;
            line-height: 1.55;
            color: rgba(255,255,255,.92);
        }
        .guest-chips {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.45rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .guest-chips li {
            font-size: 0.68rem;
            font-weight: 650;
            letter-spacing: 0.02em;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.22);
            color: rgba(255,255,255,.92);
        }
        .guest-form {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 2.5rem 1.5rem;
            background: #fff;
        }
        .guest-form-box {
            width: 100%;
            max-width: 24rem;
        }
        .guest-form-welcome {
            margin: 0 0 1.25rem;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #33509f;
            line-height: 1.25;
        }
        .guest-site-footer {
            flex-shrink: 0;
            background: #0a1a3a;
            color: rgba(255,255,255,.78);
            text-align: center;
            padding: 0.9rem 1.25rem 1rem;
            border-top: 4px solid transparent;
            border-image: linear-gradient(90deg, #1d4ed8, #dc2626 50%, #e5e7eb) 1;
            font-size: 0.72rem;
            line-height: 1.55;
        }
        .guest-site-footer p {
            margin: 0.15rem 0;
        }
        .guest-jpm-credit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.35rem;
        }
        .guest-jpm-credit img {
            display: block;
            height: 1.75rem;
            width: auto;
            object-fit: contain;
        }
        .guest-contact {
            width: min(100%, 28rem);
            margin-top: 1.35rem;
            padding-top: 1.15rem;
            border-top: 1px solid rgba(255,255,255,.22);
            text-align: center;
            font-size: 0.72rem;
            line-height: 1.55;
            color: rgba(255,255,255,.88);
        }
        .guest-contact-title {
            margin: 0 0 0.5rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            color: #fff;
        }
        .guest-contact-org {
            margin: 0 0 0.2rem;
            font-weight: 600;
            color: rgba(255,255,255,.95);
        }
        .guest-contact-dept {
            margin: 0 0 0.35rem;
            font-weight: 500;
            color: rgba(255,255,255,.9);
        }
        .guest-contact-line {
            margin: 0.25rem 0;
        }
        .guest-contact a {
            color: #bfdbfe;
            text-decoration: none;
        }
        .guest-contact a:hover {
            color: #fff;
            text-decoration: underline;
        }
        @media (min-width: 1024px) {
            .guest-shell {
                flex-direction: row;
            }
            .guest-brand,
            .guest-form {
                width: 50%;
                flex: 0 0 50%;
                max-width: 50%;
                min-height: calc(100vh - 4.5rem);
            }
            .guest-brand {
                padding: 3.25rem 3.5rem;
            }
            .guest-logo-plate {
                padding: 1rem 1.15rem;
                max-width: min(100%, 28rem);
            }
            .guest-logo-plate img {
                max-height: 9rem;
            }
            .guest-portal-divider { width: min(100%, 28rem); }
            .guest-brief { width: min(100%, 28rem); }
            .guest-brief-intro { font-size: 0.95rem; }
            .guest-form { padding: 3rem 2.5rem; }
            .guest-site-footer {
                font-size: 0.78rem;
                padding: 0.85rem 2rem 1rem;
            }
        }
    </style>
</head>
<body class="antialiased text-gray-800">
    <div class="guest-page">
        <div class="guest-shell">
            <aside class="guest-brand">
                <div class="guest-brand-inner">
                    <div class="guest-logo-plate">
                        <img
                            src="{{ asset('images/logo-alp-dbkl.png') }}?v={{ @filemtime(public_path('images/logo-alp-dbkl.png')) ?: time() }}"
                            alt="Logo DBKL — Sistem Pengurusan Sumbangan ALP"
                            width="720"
                            height="220"
                            decoding="async"
                        >
                    </div>
                    <div class="guest-portal-divider" role="presentation">Portal Rasmi</div>
                    <div class="guest-brief">
                        <p class="guest-brief-intro">
                            Mendigitalkan proses sumbangan pembangunan komuniti Ahli Lembaga Penasihat (ALP)
                            Bandaraya Kuala Lumpur — dari permohonan hingga pemantauan.
                        </p>
                        <ul class="guest-chips" aria-label="Fokus sistem">
                            <li>Peruntukan</li>
                            <li>Semakan</li>
                            <li>Perakuan</li>
                            <li>Kelulusan</li>
                            <li>Pembayaran</li>
                            <li>Pelaporan</li>
                        </ul>
                        @include('auth.partials.hubungi-kami')
                    </div>
                </div>
            </aside>

            <main class="guest-form">
                <div class="guest-form-box">
                    @include('partials.flash')
                    @yield('content')
                </div>
            </main>
        </div>

        <footer class="guest-site-footer">
            <p>Sistem Sumbangan ALP &copy; {{ date('Y') }} DBKL</p>
            <p class="guest-jpm-credit">
                <img
                    src="{{ asset('images/jpm-logo.png') }}?v={{ @filemtime(public_path('images/jpm-logo.png')) ?: time() }}"
                    alt="Logo Jabatan Pengurusan Maklumat (JPM)"
                    width="120"
                    height="28"
                    decoding="async"
                >
                <span>Dibangunkan oleh Jabatan Pengurusan Maklumat (JPM)</span>
            </p>
        </footer>
    </div>
</body>
</html>
