<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Backend chatbot AI Tutor (Anthropic Claude API) - student/chatbot_api.php.
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

        $apiKey = (string) config('services.anthropic.key');
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

        $messages = [];
        foreach (array_slice($history, -10) as $h) {
            if (! is_array($h) || ! isset($h['role'], $h['content'])) {
                continue;
            }
            if (! in_array($h['role'], ['user', 'assistant'], true)) {
                continue;
            }
            $messages[] = ['role' => $h['role'], 'content' => (string) $h['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['x-api-key' => $apiKey, 'anthropic-version' => '2023-06-01'])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => 700,
                    'system' => $systemPrompt,
                    'messages' => $messages,
                ]);
        } catch (Throwable $e) {
            Log::error('iSEP chatbot HTTP error: '.$e->getMessage());

            return response()->json(['error' => t('Tidak dapat menghubungi perkhidmatan AI. Cuba lagi sebentar.', 'Could not reach the AI service. Please try again shortly.')]);
        }

        $text = $response->json('content.0.text');
        if ($response->status() !== 200 || $text === null) {
            Log::error('iSEP chatbot Anthropic API error: '.($response->json('error.message') ?? 'HTTP '.$response->status()));

            return response()->json(['error' => t('Maaf, ralat berlaku semasa menjana jawapan. Cuba lagi.', 'Sorry, something went wrong generating a reply. Please try again.')]);
        }

        return response()->json(['reply' => $text]);
    }
}
