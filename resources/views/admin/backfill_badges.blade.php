<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backfill Lencana - iSEP Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{font-family:'Segoe UI',sans-serif; padding:40px; background:#f4f6fb;}</style>
</head>
<body>
<div class="container" style="max-width:600px;">
    <h4 class="fw-bold mb-3">🔧 Backfill Lencana Pelajar</h4>
    <p class="text-muted">Utiliti ini menyemak semula semua pelajar sedia ada dan menganugerahkan lencana yang sepatutnya sudah mereka perolehi (sijil, streak, latihan, kuiz) — berguna selepas logic lencana baru ditambah ke sistem. Selamat dijalankan berkali-kali (tidak akan duplicate).</p>

    @if (! $ran)
        <a href="{{ route('admin.backfill', ['run' => 1]) }}" class="btn btn-primary">Jalankan Backfill Sekarang</a>
    @else
        <div class="alert alert-success">Backfill selesai untuk {!! count($log) !!} pelajar: {{ implode(', ', $log) }}</div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
    @endif
</div>
</body>
</html>