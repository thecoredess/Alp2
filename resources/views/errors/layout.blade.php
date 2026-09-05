<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — Sistem ALP DBKL</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc; color: #1e293b; padding: 24px;
        }
        .card {
            max-width: 480px; width: 100%; background: #fff; border: 1px solid #e2e8f0;
            border-radius: 16px; padding: 40px 32px; text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .brand {
            margin: 0 auto 16px; max-width: 200px;
        }
        .brand img { width: 100%; height: auto; display: block; }
        .code { font-size: 44px; font-weight: 700; color: #1e3a5f; line-height: 1; }
        .title { margin: 12px 0 8px; font-size: 18px; font-weight: 600; }
        .msg { color: #64748b; font-size: 14px; line-height: 1.6; }
        .actions { margin-top: 24px; }
        .btn {
            display: inline-block; background: #1e3a5f; color: #fff; text-decoration: none;
            padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 500;
        }
        .btn:hover { background: #16304d; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <img src="{{ asset('images/logo-alp-dbkl.png') }}" alt="Logo DBKL — Sistem ALP">
        </div>
        <div class="code">@yield('code')</div>
        <h1 class="title">@yield('title')</h1>
        <p class="msg">@yield('message')</p>
        <div class="actions">
            <a href="{{ url('/dashboard') }}" class="btn">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>
