<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Doação')</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; padding: 2rem; color: #1f2937; }
        .card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h1 { font-size: 1.4rem; margin-top: 0; }
        label { display: block; margin: 1rem 0 .3rem; font-weight: 600; font-size: .9rem; }
        input { width: 100%; padding: .6rem; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; }
        button { margin-top: 1.5rem; width: 100%; padding: .8rem; background: #16a34a; color: #fff; border: 0; border-radius: 8px; font-size: 1rem; cursor: pointer; }
        .error { color: #dc2626; font-size: .85rem; margin-top: .3rem; }
        .muted { color: #6b7280; font-size: .9rem; }
        .qr { text-align: center; margin: 1.5rem 0; }
        .qr img { max-width: 240px; }
        .code { word-break: break-all; background: #f3f4f6; padding: .8rem; border-radius: 8px; font-size: .8rem; }
        .badge { display: inline-block; padding: .2rem .6rem; border-radius: 999px; font-size: .8rem; background: #fef3c7; color: #92400e; }
        .ong-list { list-style: none; padding: 0; margin: 1.5rem 0 0; }
        .ong-item { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 0; border-top: 1px solid #e5e7eb; }
        .ong-item h2 { font-size: 1rem; margin: 0; }
        .ong-item p { margin: .2rem 0 0; }
        .ong-info { flex: 1; }
        .ong-desc { font-size: .85rem; color: #4b5563; }
        .ong-donate { display: flex; gap: .4rem; align-items: center; }
        .ong-donate input { width: 120px; padding: .5rem; margin: 0; }
        .ong-donate button { margin: 0; width: auto; padding: .5rem 1rem; font-size: .9rem; }
        .pagination { margin-top: 1.5rem; display: flex; gap: .75rem; align-items: center; font-size: .9rem; }
        .pagination a { color: #16a34a; text-decoration: none; font-weight: 600; }
        .pagination .disabled { color: #9ca3af; }
        .pagination .current { color: #6b7280; }
        .btn-link { display: inline-block; padding: .5rem 1rem; background: #16a34a; color: #fff; text-decoration: none; border-radius: 8px; font-size: .9rem; white-space: nowrap; }
        .empty { color: #6b7280; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>
