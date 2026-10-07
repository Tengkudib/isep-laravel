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

if (! function_exists('safe_html')) {
    /**
     * Bersihkan HTML nota yang ditulis admin/pensyarah: kekalkan tag pemformatan asas,
     * buang skrip, atribut acara (onclick dll.), gaya dan pautan javascript:.
     */
    function safe_html(?string $html): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $allowed = [
            'p' => [], 'br' => [], 'hr' => [], 'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [], 's' => [],
            'sub' => [], 'sup' => [], 'small' => [], 'mark' => [], 'span' => [], 'div' => [], 'blockquote' => [],
            'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
            'ul' => [], 'ol' => [], 'li' => [], 'pre' => [], 'code' => [],
            'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
            'a' => ['href', 'title'], 'img' => ['src', 'alt', 'title', 'width', 'height'],
        ];
        // Elemen yang dibuang bersama isinya
        $drop = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'base', 'svg', 'math'];

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__safe_root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $isSafeUrl = fn (string $url) => ! preg_match('/^\s*(javascript|vbscript|data):/i', html_entity_decode($url))
            || preg_match('/^\s*data:image\/(png|jpe?g|gif|webp);/i', $url);

        $clean = function (DOMNode $node) use (&$clean, $allowed, $drop, $isSafeUrl) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                    $node->removeChild($child);

                    continue;
                }
                if (! $child instanceof DOMElement) {
                    continue;
                }
                $tag = strtolower($child->tagName);
                if (in_array($tag, $drop, true)) {
                    $node->removeChild($child);

                    continue;
                }
                $clean($child);
                if (! isset($allowed[$tag])) {
                    // Tag tidak dikenali: kekalkan isinya sahaja
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    if (! in_array($name, $allowed[$tag], true) || (in_array($name, ['href', 'src'], true) && ! $isSafeUrl($attr->value))) {
                        $child->removeAttribute($attr->name);
                    }
                }
                if ($tag === 'a' && $child->hasAttribute('href')) {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            }
        };

        $root = $doc->getElementById('__safe_root');
        $clean($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }
}

if (! function_exists('exercise_answer_matches')) {
    function exercise_answer_matches(string $answer, ?string $expected): bool
    {
        $normalize = fn (string $s) => rtrim(mb_strtolower(preg_replace('/\s+/u', '', $s)), '.');
        if (trim((string) $expected) === '') {
            return trim($answer) !== '';
        }

        return $normalize($answer) === $normalize((string) $expected);
    }
}
