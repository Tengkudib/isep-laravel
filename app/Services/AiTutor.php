<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * Sambungan Google Gemini yang dikongsi oleh chatbot iSEP Tutor dan "Cuba Sendiri".
 * Had penggunaan dikongsi supaya kuota Gemini tidak dihabiskan oleh seorang pelajar.
 */
class AiTutor
{
    public function isConfigured(): bool
    {
        return (string) config('services.gemini.key') !== '';
    }

    /**
     * Semak & kira had penggunaan AI pelajar (10 seminit, 100 sehari). Pulangkan mesej ralat jika melebihi had.
     */
    public function hitLimit(int $studentId): ?string
    {
        foreach ([
            ['chatbot-min|'.$studentId, 10, t('Terlalu banyak soalan dalam masa singkat. Tunggu %d saat dan cuba lagi.', 'Too many questions in a short time. Wait %d seconds and try again.')],
            ['chatbot-day|'.$studentId, 100, t('Had harian AI Tutor (100 soalan) telah dicapai. Cuba lagi esok.', 'Daily AI Tutor limit (100 questions) reached. Please try again tomorrow.')],
        ] as [$key, $max, $msg]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return sprintf($msg, RateLimiter::availableIn($key));
            }
        }
        RateLimiter::hit('chatbot-min|'.$studentId, 60);
        RateLimiter::hit('chatbot-day|'.$studentId, 86400);

        return null;
    }

    /**
     * Tajuk & nota bab (hanya jika pelajar berhak mengakses bab itu), untuk dijadikan konteks AI.
     *
     * @return array{0: string, 1: string} [tajuk, nota]
     */
    public function chapterContext(int $studentId, int $chapterId): array
    {
        if ($chapterId <= 0) {
            return ['', ''];
        }
        $title = DB::table('chapters as c')->join('student_progress as sp', 'sp.chapter_id', '=', 'c.id')
            ->where('c.id', $chapterId)->where('sp.student_id', $studentId)->value('c.title');
        if (! $title) {
            return ['', ''];
        }
        $raw = DB::table('learning_content')->where('chapter_id', $chapterId)->where('content_type', 'notes')
            ->orderBy('order_number')->pluck('content')->implode("\n\n");
        $notes = trim(strip_tags($raw));
        if (mb_strlen($notes) > 6000) {
            $notes = mb_substr($notes, 0, 6000).'...';
        }

        return [$title, $notes];
    }

    public function languageInstruction(): string
    {
        return current_lang() === 'en'
            ? 'Reply in English, in a warm and encouraging tone.'
            : 'Balas dalam Bahasa Melayu, dengan nada mesra dan menggalakkan.';
    }

    /**
     * Panggil Gemini. $contents ialah senarai giliran format Gemini (role user/model).
     *
     * @return array{reply?: string, error?: string}
     */
    public function generate(string $systemPrompt, array $contents, string $logTag = 'chatbot'): array
    {
        $model = (string) config('services.gemini.model');

        try {
            $response = Http::timeout(30)
                ->withHeaders(['x-goog-api-key' => (string) config('services.gemini.key')])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                    'contents' => $contents,
                    // Model 'thinking' turut guna kuota token output, jadi beri ruang lebih
                    'generationConfig' => ['maxOutputTokens' => 2048],
                ]);
        } catch (Throwable $e) {
            Log::error("iSEP {$logTag} HTTP error: ".$e->getMessage());

            return ['error' => t('Tidak dapat menghubungi perkhidmatan AI. Cuba lagi sebentar.', 'Could not reach the AI service. Please try again shortly.')];
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->reject(fn ($p) => ! empty($p['thought']))
            ->pluck('text')->filter()->implode('');
        if ($response->status() !== 200 || $text === '') {
            Log::error("iSEP {$logTag} Gemini API error: ".($response->json('error.message')
                ?? $response->json('candidates.0.finishReason')
                ?? 'HTTP '.$response->status()));

            return ['error' => t('Maaf, ralat berlaku semasa menjana jawapan. Cuba lagi.', 'Sorry, something went wrong generating a reply. Please try again.')];
        }

        return ['reply' => $text];
    }
}
