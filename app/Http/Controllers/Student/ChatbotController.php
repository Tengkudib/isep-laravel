<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * Backend chatbot AI Tutor (Google Gemini API) - student/chatbot_api.php.
 */
class ChatbotController extends Controller
{
    public function __invoke(Request $request)
    {
        $studentId = (int) $request->user()->id;

        $message = trim((string) $request->input('message', ''));
        $chapterId = (int) $request->input('chapter_id', 0);
        $history = json_decode((string) $request->input('history', '[]'), true);
        if (! is_array($history)) {
            $history = [];
        }

        if ($message === '') {
            return response()->json(['error' => t('Sila taip soalan anda.', 'Please type your question.')]);
        }
        if (mb_strlen($message) > 1500) {
            return response()->json(['error' => t('Soalan terlalu panjang. Ringkaskan sedikit.', 'Your question is too long. Please shorten it.')]);
        }

        // Had penggunaan setiap pelajar supaya kuota Gemini tidak dihabiskan oleh seorang pelajar
        foreach ([
            ['chatbot-min|'.$studentId, 10, 60, t('Terlalu banyak soalan dalam masa singkat. Tunggu %d saat dan cuba lagi.', 'Too many questions in a short time. Wait %d seconds and try again.')],
            ['chatbot-day|'.$studentId, 100, 86400, t('Had harian chatbot (100 soalan) telah dicapai. Cuba lagi esok.', 'Daily chatbot limit (100 questions) reached. Please try again tomorrow.')],
        ] as [$key, $max, $decay, $msg]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return response()->json(['error' => sprintf($msg, RateLimiter::availableIn($key))], 429);
            }
        }
        RateLimiter::hit('chatbot-min|'.$studentId, 60);
        RateLimiter::hit('chatbot-day|'.$studentId, 86400);

        $apiKey = (string) config('services.gemini.key');
        if ($apiKey === '') {
            return response()->json(['error' => t('Chatbot belum dikonfigurasi oleh pentadbir sistem.', 'The chatbot has not been configured by the system administrator yet.')]);
        }

        // Konteks nota bab semasa (jika pelajar berada di halaman bab dan berhak mengaksesnya)
        $context = '';
        $chapterTitle = '';
        if ($chapterId > 0) {
            $chap = DB::table('chapters as c')->join('student_progress as sp', 'sp.chapter_id', '=', 'c.id')
                ->where('c.id', $chapterId)->where('sp.student_id', $studentId)->value('c.title');

            if ($chap) {
                $chapterTitle = $chap;
                $raw = DB::table('learning_content')->where('chapter_id', $chapterId)->where('content_type', 'notes')
                    ->orderBy('order_number')->pluck('content')->implode("\n\n");
                $context = trim(strip_tags($raw));
                if (mb_strlen($context) > 6000) {
                    $context = mb_substr($context, 0, 6000).'...';
                }
            }
        }

        $langInstruction = current_lang() === 'en'
            ? 'Reply in English, in a warm and encouraging tone.'
            : 'Balas dalam Bahasa Melayu, dengan nada mesra dan menggalakkan.';

        $systemPrompt = "Anda ialah 'iSEP Tutor', pembantu AI dalam platform pembelajaran pengaturcaraan iSEP untuk pelajar. "
            .$langInstruction.' '
            .'Tugas anda: bantu pelajar memahami konsep pengaturcaraan (Python, PHP, Java, HTML, JavaScript) dengan penjelasan yang jelas, ringkas dan mudah difahami, guna contoh kod bila berguna. '
            .'Jika pelajar bertanya soalan yang tiada kaitan langsung dengan pembelajaran/pengaturcaraan, tolak dengan sopan dan ajak mereka kembali fokus. '
            .'Jangan terus berikan jawapan penuh untuk soalan kuiz atau latihan yang dinilai — bimbing pelajar untuk berfikir dan memahami, bukan sekadar beri jawapan siap.';

        if ($context !== '') {
            $systemPrompt .= "\n\nPelajar sedang belajar bab \"{$chapterTitle}\". Nota bab ini disertakan sebagai rujukan konteks sahaja (bukan untuk disalin bulat-bulat):\n{$context}";
        }

        // Gemini guna peranan 'user' / 'model' (bukan 'assistant')
        $contents = [];
        foreach (array_slice($history, -10) as $h) {
            if (! is_array($h) || ! isset($h['role'], $h['content'])) {
                continue;
            }
            if (! in_array($h['role'], ['user', 'assistant'], true)) {
                continue;
            }
            // Perbualan mesti bermula dengan giliran pengguna
            if ($contents === [] && $h['role'] !== 'user') {
                continue;
            }
            $contents[] = [
                'role' => $h['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => (string) $h['content']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $model = (string) config('services.gemini.model');

        try {
            $response = Http::timeout(30)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                    'contents' => $contents,
                    // Model 'thinking' turut guna kuota token output, jadi beri ruang lebih
                    'generationConfig' => ['maxOutputTokens' => 2048],
                ]);
        } catch (Throwable $e) {
            Log::error('iSEP chatbot HTTP error: '.$e->getMessage());

            return response()->json(['error' => t('Tidak dapat menghubungi perkhidmatan AI. Cuba lagi sebentar.', 'Could not reach the AI service. Please try again shortly.')]);
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->reject(fn ($p) => ! empty($p['thought']))
            ->pluck('text')->filter()->implode('');
        if ($response->status() !== 200 || $text === '') {
            Log::error('iSEP chatbot Gemini API error: '.($response->json('error.message')
                ?? $response->json('candidates.0.finishReason')
                ?? 'HTTP '.$response->status()));

            return response()->json(['error' => t('Maaf, ralat berlaku semasa menjana jawapan. Cuba lagi.', 'Sorry, something went wrong generating a reply. Please try again.')]);
        }

        return response()->json(['reply' => $text]);
    }
}
