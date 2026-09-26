<?php

// ============================================================
// iSEP - Helper global (bahasa BM/EN & utiliti kecil)
// Kandungan kursus (nota/kuiz/latihan) dalam database kekal Bahasa Melayu;
// t() hanya untuk teks antara muka.
// ============================================================

if (! function_exists('current_lang')) {
    /**
     * Bahasa antara muka semasa: 'ms' (lalai) atau 'en', dibaca dari kuki isep_lang
     * yang ditetapkan oleh assets/theme-toggle.js.
     */
    function current_lang(): string
    {
        $lang = request()?->cookie('isep_lang') ?? ($_COOKIE['isep_lang'] ?? 'ms');

        return $lang === 'en' ? 'en' : 'ms';
    }
}

if (! function_exists('t')) {
    /**
     * Pulangkan teks ikut bahasa semasa.
     */
    function t(string $ms, string $en): string
    {
        return current_lang() === 'en' ? $en : $ms;
    }
}

if (! function_exists('youtube_embed_url')) {
    /**
     * Tukar pautan YouTube biasa (watch?v=... atau youtu.be/...) kepada format embed.
     * Pautan bukan-YouTube atau yang sudah dalam format embed dikembalikan tanpa diubah.
     */
    function youtube_embed_url(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }
        if (preg_match('#youtube\.com/watch\?v=([A-Za-z0-9_-]+)#', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }
        if (preg_match('#youtu\.be/([A-Za-z0-9_-]+)#', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        return $url;
    }
}

if (! function_exists('dashboard_route_for')) {
    /**
     * URL dashboard ikut peranan pengguna.
     */
    function dashboard_route_for(?string $role): string
    {
        return match ($role) {
            'student' => route('student.dashboard'),
            'lecturer' => route('lecturer.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('login'),
        };
    }
}

if (! function_exists('rows')) {
    /**
     * Jalankan query builder dan pulangkan hasil sebagai senarai array bersekutu
     * (setara fetch_all(MYSQLI_ASSOC) dalam sistem asal).
     */
    function rows($query): array
    {
        $result = $query instanceof \Illuminate\Support\Collection ? $query : $query->get();

        return $result->map(fn ($r) => (array) $r)->all();
    }
}

if (! function_exists('row')) {
    /**
     * Baris pertama sebagai array bersekutu, atau null (setara fetch_assoc()).
     */
    function row($query): ?array
    {
        $r = $query->first();

        return $r ? (array) $r : null;
    }
}
