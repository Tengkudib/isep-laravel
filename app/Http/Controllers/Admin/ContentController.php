<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pengurusan bab & kandungan bab (nota, video, contoh kod, latihan, kuiz) - admin & lecturer.
 * Menggantikan admin/chapters.php, chapter_content.php dan exercise_csv_template.php.
 */
class ContentController extends Controller
{
    private const ALLOWED_EXT = ['pdf', 'txt', 'pptx', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

    private function syncTotalChapters(int $languageId): void
    {
        DB::update(
            'UPDATE languages SET total_chapters = (SELECT COUNT(*) FROM chapters WHERE language_id = ?) WHERE id = ?',
            [$languageId, $languageId]
        );
    }

    // ------------------------------------------------------------------
    // Senarai bab bagi satu bahasa
    // ------------------------------------------------------------------

    public function chapters(Request $request, int $languageId)
    {
        $language = row(DB::table('languages')->where('id', $languageId));
        abort_unless($language, 404, t('Bahasa tidak dijumpai.', 'Language not found.'));

        $isAdmin = $request->user()->role === 'admin';

        return view('admin.chapters', [
            'language' => $language,
            'chapters' => rows(DB::table('chapters')->where('language_id', $languageId)->orderBy('chapter_number')),
            'back_url' => $isAdmin ? route('admin.languages') : route('lecturer.dashboard'),
            'back_label' => $isAdmin ? t('Urus Bahasa', 'Manage Languages') : t('Dashboard Lecturer', 'Lecturer Dashboard'),
            'error' => session('error'),
            'success' => session('success'),
        ]);
    }

    public function chaptersAction(Request $request, int $languageId)
    {
        abort_unless(DB::table('languages')->where('id', $languageId)->exists(), 404, t('Bahasa tidak dijumpai.', 'Language not found.'));

        if ($request->has('add_chapter')) {
            $chapterNumber = (int) $request->input('chapter_number');
            $title = trim((string) $request->input('title', ''));

            if (! $title || ! $chapterNumber) {
                return back()->with('error', t('Nombor bab dan tajuk diperlukan.', 'Chapter number and title are required.'));
            }
            if (DB::table('chapters')->where('language_id', $languageId)->where('chapter_number', $chapterNumber)->exists()) {
                return back()->with('error', t('Nombor bab tersebut sudah wujud.', 'That chapter number already exists.'));
            }

            DB::table('chapters')->insert([
                'language_id' => $languageId,
                'chapter_number' => $chapterNumber,
                'title' => $title,
                'learning_objective' => trim((string) $request->input('learning_objective', '')),
                'minimum_quiz_score' => (int) $request->input('minimum_quiz_score'),
                'order_number' => $chapterNumber,
            ]);
            $this->syncTotalChapters($languageId);

            return back()->with('success', t('Bab berjaya ditambah.', 'Chapter added successfully.'));
        }

        if ($request->has('delete_chapter')) {
            DB::table('chapters')->where('id', (int) $request->input('id'))->delete();
            $this->syncTotalChapters($languageId);

            return back()->with('success', t('Bab dipadam.', 'Chapter deleted.'));
        }

        return back();
    }

    // ------------------------------------------------------------------
    // Kandungan satu bab
    // ------------------------------------------------------------------

    private function findChapter(int $chapterId): array
    {
        $chapter = row(DB::table('chapters as c')->join('languages as l', 'l.id', '=', 'c.language_id')
            ->select('c.*', 'l.name as lang_name')->where('c.id', $chapterId));
        abort_unless($chapter, 404, t('Bab tidak dijumpai.', 'Chapter not found.'));

        return $chapter;
    }

    public function content(int $chapterId)
    {
        $chapter = $this->findChapter($chapterId);

        return view('admin.chapter_content', [
            'chapter' => $chapter,
            'content_list' => rows(DB::table('learning_content')->where('chapter_id', $chapterId)->orderBy('content_type')->orderBy('id')),
            'exercises' => rows(DB::table('exercises')->where('chapter_id', $chapterId)),
            'quizzes' => rows(DB::table('quizzes')->where('chapter_id', $chapterId)),
            'error' => session('error'),
            'success' => session('success'),
        ]);
    }

    public function contentAction(Request $request, int $chapterId)
    {
        $this->findChapter($chapterId);
        $in = fn (string $k) => trim((string) $request->input($k, ''));

        // --- Nota / Tip / Kesilapan Biasa (dengan lampiran fail pilihan) ---
        if ($request->has('add_content')) {
            $filePath = $fileName = $fileType = null;
            $file = $request->file('note_file');

            if ($file) {
                $originalName = $file->getClientOriginalName();
                $ext = strtolower($file->getClientOriginalExtension());

                if (! in_array($ext, self::ALLOWED_EXT)) {
                    return back()->with('error', t('Jenis fail tidak dibenarkan. Hanya PDF, TXT, PPTX, DOC, DOCX, JPG, PNG, GIF, WEBP.', 'File type not allowed. Only PDF, TXT, PPTX, DOC, DOCX, JPG, PNG, GIF, WEBP.'));
                }
                if ($file->getSize() > 100 * 1024 * 1024) {
                    return back()->with('error', t('Saiz fail melebihi had 100MB.', 'File size exceeds the 100MB limit.'));
                }
                if (! $file->isValid()) {
                    return back()->with('error', t('Gagal memuat naik fail.', 'Failed to upload file.'));
                }

                $safeName = uniqid('note_').'.'.$ext;
                $file->move(public_path('uploads/notes'), $safeName);
                $filePath = 'uploads/notes/'.$safeName;
                $fileName = $originalName;
                $fileType = $ext;
            }

            DB::table('learning_content')->insert([
                'chapter_id' => $chapterId,
                'content_type' => $request->input('content_type'),
                'title' => $in('title'),
                'content' => $in('content'),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'order_number' => 1,
            ]);

            return back()->with('success', t('Kandungan ditambah.', 'Content added.'));
        }

        // --- Video ---
        if ($request->has('add_video')) {
            DB::table('learning_content')->insert([
                'chapter_id' => $chapterId,
                'content_type' => 'video',
                'title' => $in('video_title'),
                'video_url' => $in('video_url'),
                'video_duration' => $in('video_duration'),
                'order_number' => 1,
            ]);

            return back()->with('success', t('Video ditambah.', 'Video added.'));
        }

        // --- Contoh kod ---
        if ($request->has('add_code')) {
            DB::table('learning_content')->insert([
                'chapter_id' => $chapterId,
                'content_type' => 'code_example',
                'title' => $in('code_title'),
                'code_example' => (string) $request->input('code_example', ''),
                'order_number' => 1,
            ]);

            return back()->with('success', t('Contoh kod ditambah.', 'Code example added.'));
        }

        // --- Latihan ---
        if ($request->has('add_exercise')) {
            DB::table('exercises')->insert([
                'chapter_id' => $chapterId,
                'question' => $in('ex_question'),
                'instruction' => $in('ex_instruction'),
                'difficulty' => $request->input('ex_difficulty'),
                'sample_input' => $in('ex_sample_input'),
                'expected_output' => $in('ex_expected_output'),
                'hint' => $in('ex_hint'),
                'points' => (int) $request->input('ex_points'),
            ]);

            return back()->with('success', t('Latihan ditambah.', 'Exercise added.'));
        }

        // --- Tambah banyak latihan (CSV) ---
        if ($request->has('bulk_add_exercises')) {
            $csv = $request->file('exercise_csv');
            if (! $csv) {
                return back()->with('error', t('Sila pilih fail CSV.', 'Please choose a CSV file.'));
            }
            if (strtolower($csv->getClientOriginalExtension()) !== 'csv') {
                return back()->with('error', t('Fail mesti berformat CSV.', 'File must be in CSV format.'));
            }
            $handle = fopen($csv->getRealPath(), 'r');
            if (! $handle) {
                return back()->with('error', t('Gagal membaca fail CSV.', 'Failed to read the CSV file.'));
            }

            $allowedDifficulty = ['Beginner', 'Intermediate', 'Advanced'];
            $rowNum = 0;
            $added = 0;
            $skipped = 0;
            while (($row = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
                $rowNum++;
                if ($rowNum === 1 && stripos($row[0] ?? '', 'question') !== false) {
                    continue; // baris tajuk (header)
                }
                $question = trim($row[0] ?? '');
                if ($question === '') {
                    $skipped++;

                    continue;
                }
                $difficulty = trim($row[2] ?? '') ?: 'Beginner';
                if (! in_array($difficulty, $allowedDifficulty, true)) {
                    $difficulty = 'Beginner';
                }

                DB::table('exercises')->insert([
                    'chapter_id' => $chapterId,
                    'question' => $question,
                    'instruction' => trim($row[1] ?? ''),
                    'difficulty' => $difficulty,
                    'sample_input' => trim($row[3] ?? ''),
                    'expected_output' => trim($row[4] ?? ''),
                    'hint' => trim($row[5] ?? ''),
                    'points' => isset($row[6]) && is_numeric($row[6]) ? (int) $row[6] : 20,
                ]);
                $added++;
            }
            fclose($handle);

            if ($added === 0) {
                return back()->with('error', t('Tiada latihan sah dijumpai dalam fail CSV.', 'No valid exercises found in the CSV file.'));
            }

            return back()->with('success', sprintf(t('%d latihan berjaya ditambah.', '%d exercises added successfully.'), $added)
                .($skipped > 0 ? ' '.sprintf(t('(%d baris dilangkau kerana soalan kosong.)', '(%d rows skipped due to empty question.)'), $skipped) : ''));
        }

        // --- Soalan kuiz ---
        if ($request->has('add_quiz')) {
            DB::table('quizzes')->insert([
                'chapter_id' => $chapterId,
                'question' => $in('quiz_question'),
                'question_type' => $request->input('quiz_type'),
                'option_a' => $in('option_a'),
                'option_b' => $in('option_b'),
                'option_c' => $in('option_c'),
                'option_d' => $in('option_d'),
                'correct_answer' => $in('correct_answer'),
                'explanation' => $in('explanation'),
                'points' => (int) $request->input('quiz_points'),
            ]);

            return back()->with('success', t('Soalan kuiz ditambah.', 'Quiz question added.'));
        }

        // --- Padam ---
        foreach (['content' => 'learning_content', 'exercise' => 'exercises', 'quiz' => 'quizzes'] as $key => $table) {
            if ($request->has('delete_'.$key)) {
                DB::table($table)->where('id', (int) $request->input('id'))->delete();

                return back()->with('success', t('Item dipadam.', 'Item deleted.'));
            }
        }

        return back();
    }

    /**
     * Muat turun templat CSV untuk tambah banyak latihan.
     */
    public function csvTemplate()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['question', 'instruction', 'difficulty', 'sample_input', 'expected_output', 'hint', 'points'], ',', '"', '\\');
            fputcsv($out, [
                'Write a function that adds two numbers.',
                'Define add(a, b) that returns the sum of a and b.',
                'Beginner',
                'add(2, 3)',
                '5',
                'Use the + operator.',
                20,
            ], ',', '"', '\\');
            fclose($out);
        }, 'exercise_template.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
