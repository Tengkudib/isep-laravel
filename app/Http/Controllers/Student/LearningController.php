<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LearningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard pelajar, laluan pembelajaran (bahasa), pendaftaran kursus dan halaman bab.
 * Menggantikan student/dashboard.php, language.php, enroll.php dan chapter.php.
 */
class LearningController extends Controller
{
    public function __construct(private LearningService $learning)
    {
    }

    public function dashboard(Request $request)
    {
        $studentId = (int) $request->user()->id;

        $this->learning->updateLearningStreak($studentId);

        // Data user terkini (streak/xp mungkin berubah)
        $user = row(DB::table('users')->where('id', $studentId));
        $cosmetics = $this->learning->getEquippedCosmetics($user);

        // Toast XP selepas menang permainan lawan komputer
        $xpGained = (int) $request->query('xp_gained', 0);
        $xpGame = mb_substr(trim((string) $request->query('xp_game', '')), 0, 40);

        // Level dikira dari jumlah XP yang PERNAH diperoleh (bukan baki xp_points)
        $totalXpEarned = $this->learning->getTotalXpEarned($studentId);
        $xpPerLevel = 500;

        $todayXp = (int) DB::table('xp_log')->where('student_id', $studentId)->where('amount', '>', 0)
            ->whereRaw('DATE(created_at) = CURDATE()')->sum('amount');
        $todayActivityCount = DB::table('xp_log')->where('student_id', $studentId)->where('amount', '>', 0)
            ->whereRaw('DATE(created_at) = CURDATE()')->count();

        $languages = rows(DB::table('languages')->where('status', 'active')->orderBy('name'));
        foreach ($languages as &$lang) {
            $lang['progress'] = $this->learning->getLanguageOverallProgress($studentId, $lang['id']);
        }
        unset($lang);

        // "Teruskan Belajar" - bab belum selesai yang paling baru diakses
        $continueLearning = row(DB::table('student_progress as sp')
            ->join('chapters as c', 'c.id', '=', 'sp.chapter_id')
            ->join('languages as l', 'l.id', '=', 'c.language_id')
            ->select('l.name', 'l.slug', 'c.id as chapter_id', 'c.chapter_number', 'c.title as chapter_title', 'sp.completion_percentage')
            ->where('sp.student_id', $studentId)->where('sp.chapter_status', '!=', 'completed')->where('sp.unlock_status', 'unlocked')
            ->orderByDesc('sp.last_accessed')->limit(1));

        $stats = [
            'chapters_completed' => DB::table('student_progress')->where('student_id', $studentId)->where('chapter_status', 'completed')->count(),
            'exercises_completed' => DB::table('exercise_submissions')->where('student_id', $studentId)->where('status', 'completed')->count(),
            'quiz_average' => round(DB::table('quiz_results')->where('student_id', $studentId)->avg('percentage') ?? 0),
        ];

        $earnedBadges = rows(DB::table('student_badges as sb')->join('badges as b', 'b.id', '=', 'sb.badge_id')
            ->select('b.name', 'b.icon')->where('sb.student_id', $studentId)->orderByDesc('sb.earned_at')->limit(6));

        return view('student.dashboard', [
            'user' => $user,
            'student_id' => $studentId,
            'cosmetics' => $cosmetics,
            'flame_emoji' => $cosmetics['flame_emoji'] ?: '🔥',
            'xp_gained' => $xpGained,
            'xp_game' => $xpGame,
            'level' => intdiv($totalXpEarned, $xpPerLevel) + 1,
            'xp_into_level' => $totalXpEarned % $xpPerLevel,
            'xp_to_next' => $xpPerLevel - ($totalXpEarned % $xpPerLevel),
            'level_progress_pct' => round((($totalXpEarned % $xpPerLevel) / $xpPerLevel) * 100),
            'xp_per_level' => $xpPerLevel,
            'leaderboard_preview' => $this->learning->getLeaderboard('weekly', 3),
            'today_xp' => $todayXp,
            'today_activity_count' => $todayActivityCount,
            'languages' => $languages,
            'continue_learning' => $continueLearning,
            'stats' => $stats,
            'earned_badges' => $earnedBadges,
        ]);
    }

    public function language(Request $request, string $slug)
    {
        $studentId = (int) $request->user()->id;
        $language = row(DB::table('languages')->where('slug', $slug));
        abort_unless($language, 404, t('Bahasa pengaturcaraan tidak dijumpai.', 'Programming language not found.'));

        // Pelajar mesti enroll (ikrar) dulu sebelum boleh akses bab
        if (! $this->learning->isEnrolled($studentId, $language['id'])) {
            return redirect()->route('student.enroll', $slug);
        }

        $chapters = rows(DB::table('chapters')->where('language_id', $language['id'])->where('status', 'active')->orderBy('chapter_number'));

        foreach ($chapters as $c) {
            $this->learning->getOrCreateProgress($studentId, $c['id']);
        }
        $this->learning->refreshUnlockStatus($studentId, $language['id']);

        $progressMap = [];
        foreach (rows(DB::table('student_progress')->select('chapter_id', 'chapter_status', 'unlock_status', 'completion_percentage')
            ->where('student_id', $studentId)->where('language_id', $language['id'])) as $p) {
            $progressMap[$p['chapter_id']] = $p;
        }

        $cert = row(DB::table('certificates')->select('certificate_code')->where('student_id', $studentId)->where('language_id', $language['id']));

        return view('student.language', [
            'language' => $language,
            'chapters' => $chapters,
            'progress_map' => $progressMap,
            'cert' => $cert,
        ]);
    }

    public function showEnroll(Request $request, string $slug)
    {
        $language = row(DB::table('languages')->where('slug', $slug));
        abort_unless($language, 404, t('Bahasa pengaturcaraan tidak dijumpai.', 'Programming language not found.'));

        if ($this->learning->isEnrolled($request->user()->id, $language['id'])) {
            return redirect()->route('student.language', $slug);
        }

        return view('student.enroll', ['language' => $language, 'user' => $request->user()]);
    }

    public function enroll(Request $request, string $slug)
    {
        $studentId = (int) $request->user()->id;
        $language = row(DB::table('languages')->where('slug', $slug));
        abort_unless($language, 404, t('Bahasa pengaturcaraan tidak dijumpai.', 'Programming language not found.'));

        if ($this->learning->isEnrolled($studentId, $language['id'])) {
            return redirect()->route('student.language', $slug);
        }

        if (! $request->has('agree')) {
            return back()->with('error', t('Anda mesti bersetuju dengan ikrar sebelum meneruskan pendaftaran.', 'You must agree to the pledge before continuing with enrollment.'));
        }

        DB::table('enrollments')->insert(['student_id' => $studentId, 'language_id' => $language['id'], 'agreed_pledge' => 1]);

        return redirect()->route('student.language', $slug);
    }

    private function loadChapter(int $chapterId): array
    {
        $chapter = row(DB::table('chapters as c')->join('languages as l', 'l.id', '=', 'c.language_id')
            ->select('c.*', 'l.name as language_name', 'l.id as language_id', 'l.slug as language_slug')
            ->where('c.id', $chapterId));
        abort_unless($chapter, 404, t('Bab tidak dijumpai.', 'Chapter not found.'));

        return $chapter;
    }

    public function chapter(Request $request, int $id)
    {
        $studentId = (int) $request->user()->id;
        $chapter = $this->loadChapter($id);
        $progress = $this->learning->getOrCreateProgress($studentId, $id);

        // Skrin berkunci: halang akses terus jika bab belum unlocked
        if ($progress['unlock_status'] !== 'unlocked') {
            return view('student.chapter_locked', ['chapter' => $chapter]);
        }

        $allContent = rows(DB::table('learning_content')->where('chapter_id', $id)->orderBy('order_number'));

        return view('student.chapter', [
            'chapter' => $chapter,
            'chapter_id' => $id,
            'chatbotChapterId' => $id,
            'progress' => $progress,
            'message' => session('message'),
            'quiz_result' => session('last_quiz_result'),
            'notes' => array_filter($allContent, fn ($c) => $c['content_type'] === 'notes'),
            'videos' => array_filter($allContent, fn ($c) => $c['content_type'] === 'video'),
            'code_examples' => array_filter($allContent, fn ($c) => $c['content_type'] === 'code_example'),
            'tips' => array_filter($allContent, fn ($c) => in_array($c['content_type'], ['tip', 'common_error'])),
            'exercises' => rows(DB::table('exercises')->where('chapter_id', $id)),
            'quizzes' => rows(DB::table('quizzes')->where('chapter_id', $id)),
        ]);
    }

    /**
     * Tindakan tanda-selesai, hantar latihan dan hantar kuiz pada satu bab.
     */
    public function chapterAction(Request $request, int $id)
    {
        $studentId = (int) $request->user()->id;
        $chapter = $this->loadChapter($id);
        $progress = $this->learning->getOrCreateProgress($studentId, $id);

        if ($progress['unlock_status'] !== 'unlocked') {
            return redirect()->route('student.chapter', $id);
        }

        $setStatus = fn (string $col) => DB::table('student_progress')->where('id', $progress['id'])->update([$col => 'completed']);
        $message = null;

        if ($request->has('mark_notes')) {
            $setStatus('notes_status');
            $this->learning->recalculateChapterCompletion($studentId, $id);
            $message = t('Nota ditandakan selesai!', 'Notes marked as complete!');
        }
        if ($request->has('mark_video')) {
            $setStatus('video_status');
            $this->learning->recalculateChapterCompletion($studentId, $id);
            $message = t('Video ditandakan selesai!', 'Video marked as complete!');
        }
        if ($request->has('mark_try_it')) {
            $setStatus('try_it_status');
            $this->learning->recalculateChapterCompletion($studentId, $id);
            $message = t('Aktiviti "Try It Yourself" ditandakan selesai!', '"Try It Yourself" activity marked as complete!');
        }
        if ($request->has('submit_exercise')) {
            DB::table('exercise_submissions')->insert([
                'student_id' => $studentId,
                'exercise_id' => (int) $request->input('exercise_id'),
                'answer_code' => trim((string) $request->input('answer_code', '')),
                'status' => 'completed',
            ]);
            $setStatus('exercise_status');
            $this->learning->addXp($studentId, 20, 'Exercise Completed');
            $this->learning->checkFirstCodeBadge($studentId);
            $this->learning->recalculateChapterCompletion($studentId, $id);
            $message = t('Latihan dihantar! +20 XP', 'Exercise submitted! +20 XP');
        }
        if ($request->has('submit_quiz')) {
            $quizQuestions = rows(DB::table('quizzes')->where('chapter_id', $id));

            $totalPoints = 0;
            $score = 0;
            $answers = [];
            foreach ($quizQuestions as $q) {
                $totalPoints += $q['points'];
                $given = (string) $request->input('q'.$q['id'], '');
                $answers[$q['id']] = $given;
                if (trim($given) === trim($q['correct_answer'])) {
                    $score += $q['points'];
                }
            }
            $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
            $passStatus = $percentage >= $chapter['minimum_quiz_score'] ? 'pass' : 'fail';

            $attemptNo = DB::table('quiz_results')->where('student_id', $studentId)->where('chapter_id', $id)->count() + 1;

            DB::table('quiz_results')->insert([
                'student_id' => $studentId,
                'chapter_id' => $id,
                'attempt_number' => $attemptNo,
                'score' => $score,
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'pass_status' => $passStatus,
                'answers_json' => json_encode($answers),
            ]);

            DB::update(
                'UPDATE student_progress SET quiz_status = ?, quiz_best_score = GREATEST(quiz_best_score, ?) WHERE id = ?',
                [$passStatus === 'pass' ? 'passed' : 'failed', $percentage, $progress['id']]
            );

            if ($passStatus === 'pass') {
                $this->learning->addXp($studentId, 30, 'Quiz Passed');
            }
            $this->learning->checkQuizMasterBadge($studentId);
            $this->learning->recalculateChapterCompletion($studentId, $id);

            return redirect()->route('student.chapter', $id)->with('last_quiz_result', [
                'score' => $score, 'total' => $totalPoints, 'percentage' => $percentage, 'pass' => $passStatus === 'pass',
            ]);
        }

        return redirect()->route('student.chapter', $id)->with('message', $message);
    }

    public function certificates(Request $request)
    {
        $certs = rows(DB::table('certificates as cert')->join('languages as l', 'l.id', '=', 'cert.language_id')
            ->select('cert.*', 'l.name as lang_name', 'l.icon', 'l.color_theme')
            ->where('cert.student_id', $request->user()->id)->orderByDesc('cert.issued_at'));

        return view('student.certificates', ['certs' => $certs]);
    }

    public function certificate(Request $request, string $code)
    {
        $user = $request->user();
        $cosmetics = $this->learning->getEquippedCosmetics($user);

        $cert = row(DB::table('certificates as cert')
            ->join('languages as l', 'l.id', '=', 'cert.language_id')
            ->join('users as u', 'u.id', '=', 'cert.student_id')
            ->select('cert.*', 'l.name as language_name', 'u.name as student_name')
            ->where('cert.certificate_code', $code)->where('cert.student_id', $user->id));

        abort_unless($cert, 404, t('Sijil tidak dijumpai atau bukan milik anda.', 'Certificate not found or does not belong to you.'));

        return view('student.certificate', ['cert' => $cert, 'cosmetics' => $cosmetics]);
    }

    public function leaderboard(Request $request)
    {
        $studentId = (int) $request->user()->id;
        $period = $request->query('period', 'weekly');
        if (! in_array($period, ['weekly', 'monthly', 'overall'])) {
            $period = 'weekly';
        }

        $leaderboard = $this->learning->getLeaderboard($period, 50);

        $myRank = null;
        foreach ($leaderboard as $i => $r) {
            if ($r['id'] == $studentId) {
                $myRank = $i + 1;
                break;
            }
        }

        return view('student.leaderboard', [
            'student_id' => $studentId,
            'period' => $period,
            'leaderboard' => $leaderboard,
            'my_rank' => $myRank,
            'period_labels' => ['weekly' => t('Mingguan', 'Weekly'), 'monthly' => t('Bulanan', 'Monthly'), 'overall' => t('Keseluruhan', 'Overall')],
        ]);
    }
}
