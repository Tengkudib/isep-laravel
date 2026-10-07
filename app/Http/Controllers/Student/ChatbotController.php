<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AiTutor;
use Illuminate\Http\Request;

/**
 * Backend chatbot AI Tutor (Google Gemini API) - student/chatbot_api.php.
 */
class ChatbotController extends Controller
{
    public function __invoke(Request $request, AiTutor $tutor)
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

        if ($limitError = $tutor->hitLimit($studentId)) {
            return response()->json(['error' => $limitError], 429);
        }

        if (! $tutor->isConfigured()) {
            return response()->json(['error' => t('Chatbot belum dikonfigurasi oleh pentadbir sistem.', 'The chatbot has not been configured by the system administrator yet.')]);
        }

        // Konteks nota bab semasa (jika pelajar berada di halaman bab dan berhak mengaksesnya)
        [$chapterTitle, $context] = $tutor->chapterContext($studentId, $chapterId);

        $systemPrompt = "Anda ialah 'iSEP Tutor', pembantu AI dalam platform pembelajaran pengaturcaraan iSEP untuk pelajar. "
            .$tutor->languageInstruction().' '
            .'Tugas anda: bantu pelajar memahami konsep pengaturcaraan (Python, PHP, Java, HTML, JavaScript, MySQL) dengan penjelasan yang jelas, ringkas dan mudah difahami, guna contoh kod bila berguna. '
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

        return response()->json($tutor->generate($systemPrompt, $contents));
    }
}
