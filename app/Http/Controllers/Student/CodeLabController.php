<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AiTutor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * "Cuba Sendiri": jalankan kod Java/PHP (Python, JavaScript, HTML & MySQL dijalankan terus di pelayar)
 * dan AI Tutor yang mengajar - terangkan kod, beri cabaran ikut tajuk bab, dan semak kod pelajar.
 */
class CodeLabController extends Controller
{
    // ID bahasa Judge0: Java (JDK 17), PHP (8.3)
    private const JUDGE0_LANGUAGES = ['java' => 91, 'php' => 98];

    private const MAX_CODE_LENGTH = 20000;

    public function run(Request $request): JsonResponse
    {
        $studentId = (int) $request->user()->id;
        $lang = (string) $request->input('language', '');
        $code = (string) $request->input('code', '');
        $stdin = (string) $request->input('stdin', '');

        if (! isset(self::JUDGE0_LANGUAGES[$lang])) {
            return response()->json(['error' => t('Bahasa ini dijalankan terus di pelayar.', 'This language runs directly in the browser.')], 400);
        }
        if (trim($code) === '') {
            return response()->json(['error' => t('Sila tulis kod dahulu.', 'Please write some code first.')], 422);
        }
        if (strlen($code) > self::MAX_CODE_LENGTH || strlen($stdin) > 5000) {
            return response()->json(['error' => t('Kod terlalu panjang.', 'The code is too long.')], 422);
        }

        // 20 larian seminit bagi setiap pelajar
        $key = 'coderun|'.$studentId;
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['error' => sprintf(t('Terlalu banyak larian. Tunggu %d saat.', 'Too many runs. Wait %d seconds.'), RateLimiter::availableIn($key))], 429);
        }
        RateLimiter::hit($key, 60);

        if ($lang === 'java') {
            $code = $this->javaAsMain($code);
        }

        $headers = ['Accept' => 'application/json'];
        if ($token = (string) config('services.code_runner.key')) {
            $headers['X-Auth-Token'] = $token;
        }

        try {
            $response = Http::timeout(30)->withHeaders($headers)
                ->post(rtrim((string) config('services.code_runner.url'), '/').'/submissions?base64_encoded=true&wait=true', [
                    'language_id' => self::JUDGE0_LANGUAGES[$lang],
                    'source_code' => base64_encode($code),
                    'stdin' => base64_encode($stdin),
                    'cpu_time_limit' => 5,
                    'wall_time_limit' => 10,
                ]);
        } catch (Throwable $e) {
            Log::error('iSEP code runner HTTP error: '.$e->getMessage());

            return response()->json(['error' => t('Tidak dapat menghubungi pelaksana kod. Cuba lagi sebentar.', 'Could not reach the code runner. Please try again shortly.')], 502);
        }

        if (! $response->successful() || $response->json('status.id') === null) {
            Log::error('iSEP code runner error: HTTP '.$response->status().' '.mb_substr($response->body(), 0, 300));

            return response()->json(['error' => t('Pelaksana kod sibuk atau tidak tersedia. Cuba lagi sebentar.', 'The code runner is busy or unavailable. Please try again shortly.')], 502);
        }

        $decode = fn (?string $v) => $v === null ? '' : (string) base64_decode($v);

        return response()->json([
            'status' => $response->json('status.description'),
            'ok' => (int) $response->json('status.id') === 3,
            'stdout' => $decode($response->json('stdout')),
            'stderr' => $decode($response->json('stderr')),
            'compile_output' => $decode($response->json('compile_output')),
            'time' => $response->json('time'),
        ]);
    }

    /**
     * Judge0 menjalankan Java sebagai kelas "Main". Namakan semula kelas public pelajar (cth. HelloWorld) kepada Main.
     */
    private function javaAsMain(string $code): string
    {
        if (preg_match('/\bpublic\s+(?:final\s+)?class\s+(\w+)/', $code, $m) && $m[1] !== 'Main') {
            return preg_replace('/\b'.preg_quote($m[1], '/').'\b/', 'Main', $code);
        }

        return $code;
    }

    /**
     * AI Tutor untuk "Cuba Sendiri": mode = explain | challenge | review.
     */
    public function assist(Request $request, AiTutor $tutor): JsonResponse
    {
        $studentId = (int) $request->user()->id;
        $mode = (string) $request->input('mode', '');
        $chapterId = (int) $request->input('chapter_id', 0);
        $code = (string) $request->input('code', '');
        $output = mb_substr((string) $request->input('output', ''), 0, 3000);
        $challenge = mb_substr((string) $request->input('challenge', ''), 0, 3000);

        if (! in_array($mode, ['explain', 'challenge', 'review'], true)) {
            return response()->json(['error' => t('Permintaan tidak sah.', 'Invalid request.')], 400);
        }
        if ($mode !== 'challenge' && trim($code) === '') {
            return response()->json(['error' => t('Sila tulis kod dahulu.', 'Please write some code first.')], 422);
        }
        if (strlen($code) > self::MAX_CODE_LENGTH) {
            return response()->json(['error' => t('Kod terlalu panjang.', 'The code is too long.')], 422);
        }

        $chapter = row(DB::table('chapters as c')->join('languages as l', 'l.id', '=', 'c.language_id')
            ->select('c.title', 'l.name as language_name')->where('c.id', $chapterId));
        if (! $chapter) {
            return response()->json(['error' => t('Bab tidak dijumpai.', 'Chapter not found.')], 404);
        }

        if ($limitError = $tutor->hitLimit($studentId)) {
            return response()->json(['error' => $limitError], 429);
        }
        if (! $tutor->isConfigured()) {
            return response()->json(['error' => t('AI Tutor belum dikonfigurasi oleh pentadbir sistem.', 'The AI Tutor has not been configured by the system administrator yet.')]);
        }

        [, $notes] = $tutor->chapterContext($studentId, $chapterId);
        $lang = $chapter['language_name'];

        $system = "Anda ialah 'iSEP Tutor', guru pengaturcaraan yang sabar untuk pelajar pemula dalam platform iSEP. "
            .$tutor->languageInstruction().' '
            ."Pelajar sedang belajar {$lang}, bab \"{$chapter['title']}\". "
            .'Gunakan Markdown ringkas: tajuk kecil, senarai bernombor, dan blok kod ```. Pastikan jawapan padat dan sesuai untuk pemula.';
        if ($notes !== '') {
            $system .= "\n\nNota bab (rujukan konteks sahaja):\n{$notes}";
        }

        $prompt = match ($mode) {
            'explain' => "Terangkan kod {$lang} ini kepada pelajar pemula, baris demi baris atau blok demi blok. "
                ."Nyatakan konsep utama bab ini yang digunakan dalam kod, apa output yang dijangka dan sebabnya. Akhiri dengan satu soalan ringkas untuk menguji kefahaman pelajar.\n\n"
                ."Kod:\n```\n{$code}\n```",
            'challenge' => "Beri SATU cabaran koding kecil (boleh siap dalam 5-15 minit) yang melatih konsep bab \"{$chapter['title']}\" dalam {$lang}. "
                .'Format: **Cabaran:** (tugasan jelas), **Contoh output yang dijangka:**, **Petunjuk:** (1-2 petunjuk tanpa memberi jawapan). '
                .'JANGAN beri penyelesaian. '.($lang === 'MySql' ? 'Sertakan pernyataan CREATE TABLE dan INSERT sebagai data permulaan yang boleh pelajar gunakan.' : ''),
            'review' => ($challenge !== '' ? "Cabaran yang sedang diselesaikan pelajar:\n{$challenge}\n\n" : '')
                ."Semak kod {$lang} pelajar ini".($challenge !== '' ? ' berdasarkan cabaran di atas' : '').'. '
                .'Nyatakan (1) sama ada ia betul/berfungsi, (2) apa yang sudah bagus, (3) kesilapan atau cara memperbaikinya dalam bentuk PETUNJUK - jangan tulis semula penyelesaian penuh. '
                ."Jika output menunjukkan ralat, terangkan maksud ralat itu dalam bahasa mudah.\n\n"
                ."Kod pelajar:\n```\n{$code}\n```\n\nOutput apabila dijalankan:\n```\n".($output !== '' ? $output : '(belum dijalankan)')."\n```",
        };

        return response()->json($tutor->generate($system, [['role' => 'user', 'parts' => [['text' => $prompt]]]], 'codelab'));
    }
}
