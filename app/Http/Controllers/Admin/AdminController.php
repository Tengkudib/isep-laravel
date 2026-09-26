<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LearningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Halaman admin: dashboard, bahasa, pengguna, maklum balas, analitik & backfill lencana.
 * Menggantikan admin/index.php, languages.php, users.php, feedback.php, analytics.php, backfill_badges.php.
 */
class AdminController extends Controller
{
    /** Semester dari nama kelas (contoh "DDT3A" -> 3), 0 jika tidak diketahui. */
    private static function semesterFromClass(?string $kelas): int
    {
        if ($kelas && preg_match('/(\d+)[A-Z]$/', $kelas, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    /** Kumpul pelajar ikut Jabatan -> Semester -> Sesi supaya senarai 600+ tidak dipaparkan rata. */
    private static function groupStudents(array $students): array
    {
        $groups = [];
        foreach ($students as $s) {
            $dept = $s['department'] ?: t('Lain-lain', 'Other');
            $sem = self::semesterFromClass($s['class_name']);
            $sesi = $s['session_year'] ?: t('Tiada Sesi', 'No Session');
            $groups[$dept][$sem][$sesi][] = $s;
        }
        ksort($groups);
        foreach ($groups as &$semGroups) {
            ksort($semGroups);
        }
        unset($semGroups);

        return $groups;
    }

    public function dashboard()
    {
        $stats = [
            'students' => DB::table('users')->where('role', 'student')->count(),
            'active_students' => DB::table('users')->where('role', 'student')->where('status', 'active')->count(),
            'languages' => DB::table('languages')->count(),
            'chapters' => DB::table('chapters')->count(),
            'chapters_completed' => DB::table('student_progress')->where('chapter_status', 'completed')->count(),
            'quiz_attempts' => DB::table('quiz_results')->count(),
            'avg_quiz_score' => round(DB::table('quiz_results')->avg('percentage') ?? 0),
            'exercises_done' => DB::table('exercise_submissions')->where('status', 'completed')->count(),
        ];

        // Guna jumlah XP yang pernah diperoleh (bukan baki xp_points)
        $students = array_map(fn ($r) => (array) $r, DB::select(
            "SELECT u.id, u.name, u.student_id, u.programme, u.learning_streak, u.department, u.class_name, u.session_year,
                    COALESCE((SELECT SUM(amount) FROM xp_log WHERE student_id = u.id AND amount > 0), 0) as total_xp,
                    (SELECT AVG(percentage) FROM quiz_results WHERE student_id = u.id) as quiz_avg,
                    (SELECT COUNT(*) FROM student_progress WHERE student_id = u.id AND chapter_status = 'completed') as chapters_done
             FROM users u WHERE u.role = 'student' ORDER BY total_xp DESC"
        ));

        $difficult = array_map(fn ($r) => (array) $r, DB::select(
            'SELECT c.title, l.name as lang_name, AVG(qr.percentage) as avg_pct
             FROM quiz_results qr
             JOIN chapters c ON c.id = qr.chapter_id
             JOIN languages l ON l.id = c.language_id
             GROUP BY c.id ORDER BY avg_pct ASC LIMIT 5'
        ));

        return view('admin.dashboard', [
            'stats' => $stats,
            'student_monitor_groups' => self::groupStudents($students),
            'difficult' => $difficult,
        ]);
    }

    // ------------------------------------------------------------------
    // Bahasa pengaturcaraan
    // ------------------------------------------------------------------

    public function languages()
    {
        $languages = array_map(fn ($r) => (array) $r, DB::select(
            "SELECT l.*,
                    (SELECT COUNT(*) FROM chapters c WHERE c.language_id = l.id) as chapter_count,
                    (SELECT GROUP_CONCAT(u.name SEPARATOR ', ') FROM language_lecturers ll JOIN users u ON u.id = ll.lecturer_id WHERE ll.language_id = l.id) as lecturer_names
             FROM languages l
             ORDER BY l.name ASC"
        ));

        $langLecturerMap = [];
        foreach (DB::table('language_lecturers')->select('language_id', 'lecturer_id')->get() as $r) {
            $langLecturerMap[$r->language_id][] = (int) $r->lecturer_id;
        }

        return view('admin.languages', [
            'languages' => $languages,
            'all_lecturers' => rows(DB::table('users')->select('id', 'name')->where('role', 'lecturer')->orderBy('name')),
            'lang_lecturer_map' => $langLecturerMap,
            // Preset ikon untuk bahasa popular - admin klik terus tanpa perlu tahu nama class FontAwesome
            'icon_presets' => [
                ['label' => 'HTML', 'icon' => 'fa-brands fa-html5'],
                ['label' => 'CSS', 'icon' => 'fa-brands fa-css3-alt'],
                ['label' => 'JavaScript', 'icon' => 'fa-brands fa-js'],
                ['label' => 'Python', 'icon' => 'fa-brands fa-python'],
                ['label' => 'Java', 'icon' => 'fa-brands fa-java'],
                ['label' => 'PHP', 'icon' => 'fa-brands fa-php'],
                ['label' => 'MySQL', 'icon' => 'fa-solid fa-database'],
                ['label' => 'C / C++', 'icon' => 'fa-solid fa-code'],
                ['label' => 'React', 'icon' => 'fa-brands fa-react'],
                ['label' => 'Node.js', 'icon' => 'fa-brands fa-node-js'],
                ['label' => 'Swift', 'icon' => 'fa-brands fa-swift'],
                ['label' => 'Git', 'icon' => 'fa-brands fa-git-alt'],
            ],
            'error' => session('error'),
            'success' => session('success'),
        ]);
    }

    public function languagesAction(Request $request)
    {
        $in = fn (string $k) => trim((string) $request->input($k, ''));
        $lecturerIds = array_values(array_filter(array_map('intval', (array) $request->input('lecturer_ids', []))));

        if ($request->has('add_language')) {
            $name = $in('name');
            $slug = strtolower($in('slug'));
            if (! $name || ! $slug) {
                return back()->with('error', t('Nama dan slug diperlukan.', 'Name and slug are required.'));
            }
            if (DB::table('languages')->where('slug', $slug)->exists()) {
                return back()->with('error', t('Slug sudah digunakan.', 'Slug already in use.'));
            }

            $newId = DB::table('languages')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'description' => $in('description'),
                'icon' => $in('icon'),
                'difficulty' => $request->input('difficulty'),
                'color_theme' => $in('color_theme') ?: '#0d6efd',
                'lecturer_id' => (int) $request->input('lecturer_id', 0) ?: null,
                'total_chapters' => 0,
            ]);
            foreach ($lecturerIds as $lid) {
                DB::table('language_lecturers')->insertOrIgnore(['language_id' => $newId, 'lecturer_id' => $lid]);
            }

            return back()->with('success', t('Bahasa pengaturcaraan berjaya ditambah.', 'Programming language added successfully.'));
        }

        if ($request->has('edit_language')) {
            $id = (int) $request->input('id');
            $name = $in('name');
            $slug = strtolower($in('slug'));
            if (! $name || ! $slug) {
                return back()->with('error', t('Nama dan slug diperlukan.', 'Name and slug are required.'));
            }
            if (DB::table('languages')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
                return back()->with('error', t('Slug sudah digunakan oleh bahasa lain.', 'Slug is already used by another language.'));
            }

            DB::table('languages')->where('id', $id)->update([
                'name' => $name,
                'slug' => $slug,
                'description' => $in('description'),
                'icon' => $in('icon'),
                'difficulty' => $request->input('difficulty'),
                'color_theme' => $in('color_theme') ?: '#0d6efd',
            ]);

            return back()->with('success', t('Bahasa pengaturcaraan dikemaskini.', 'Programming language updated.'));
        }

        if ($request->has('delete_language')) {
            DB::table('languages')->where('id', (int) $request->input('id'))->delete();

            return back()->with('success', t('Bahasa pengaturcaraan dipadam.', 'Programming language deleted.'));
        }

        if ($request->has('update_lecturers')) {
            $id = (int) $request->input('id');
            DB::table('language_lecturers')->where('language_id', $id)->delete();
            foreach ($lecturerIds as $lid) {
                DB::table('language_lecturers')->insertOrIgnore(['language_id' => $id, 'lecturer_id' => $lid]);
            }
            // Kekalkan lecturer_id (lajur lama) sebagai "lecturer utama" untuk keserasian ke belakang
            DB::table('languages')->where('id', $id)->update(['lecturer_id' => $lecturerIds[0] ?? null]);

            return back()->with('success', t('Lecturer dikemaskini.', 'Lecturers updated.'));
        }

        return back();
    }

    // ------------------------------------------------------------------
    // Pengguna
    // ------------------------------------------------------------------

    public function users(Request $request)
    {
        $usersByRole = ['admin' => [], 'lecturer' => [], 'student' => []];
        foreach (rows(DB::table('users')->orderByRaw("FIELD(role,'admin','lecturer','student')")->orderBy('name')) as $u) {
            $usersByRole[$u['role']][] = $u;
        }

        return view('admin.users', [
            'users_by_role' => $usersByRole,
            'student_groups' => self::groupStudents($usersByRole['student']),
            'current_user_id' => (int) $request->user()->id,
            'error' => session('error'),
            'success' => session('success'),
        ]);
    }

    public function usersAction(Request $request)
    {
        if ($request->has('add_user')) {
            $name = trim((string) $request->input('name', ''));
            $username = strtoupper(trim((string) $request->input('username', '')));
            $email = trim((string) $request->input('email', '')) ?: null;
            $role = (string) $request->input('role');
            $password = (string) $request->input('password', '');
            $studentId = trim((string) $request->input('student_id', ''));
            $programme = trim((string) $request->input('programme', ''));
            $semester = (string) $request->input('semester', '') !== '' ? (int) $request->input('semester') : null;
            $sessionYear = trim((string) $request->input('session_year', '')) ?: null;

            if (! $name || ! $username || ! $password) {
                return back()->with('error', t('Nama, username dan kata laluan diperlukan.', 'Name, username and password are required.'));
            }
            if (strlen($password) < 6) {
                return back()->with('error', t('Kata laluan mesti sekurang-kurangnya 6 aksara.', 'Password must be at least 6 characters.'));
            }
            if (! in_array($role, ['student', 'lecturer', 'admin'])) {
                return back()->with('error', t('Peranan tidak sah.', 'Invalid role.'));
            }
            if (DB::table('users')->where('username', $username)->exists()) {
                return back()->with('error', t('Username sudah digunakan.', 'Username already in use.'));
            }
            if ($email && DB::table('users')->where('email', $email)->exists()) {
                return back()->with('error', t('Emel sudah digunakan.', 'Email already in use.'));
            }

            try {
                DB::table('users')->insert([
                    'student_id' => $role === 'student' ? ($studentId ?: null) : null,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role,
                    'programme' => $role === 'student' ? ($programme ?: null) : null,
                    'semester' => $semester,
                    'session_year' => $role === 'student' ? $sessionYear : null,
                    'status' => 'active',
                ]);
            } catch (\Throwable $e) {
                return back()->with('error', t('Gagal mendaftarkan pengguna.', 'Failed to register user.'));
            }

            return back()->with('success', ucfirst($role).' '.t('berjaya didaftarkan.', 'registered successfully.'));
        }

        if ($request->has('delete_user')) {
            $id = (int) $request->input('id');
            if ($id === (int) $request->user()->id) {
                return back()->with('error', t('Anda tidak boleh memadam akaun sendiri.', 'You cannot delete your own account.'));
            }
            DB::table('users')->where('id', $id)->delete();

            return back()->with('success', t('Pengguna dipadam.', 'User deleted.'));
        }

        if ($request->has('toggle_status')) {
            DB::table('users')->where('id', (int) $request->input('id'))->update(['status' => $request->input('new_status')]);

            return back()->with('success', t('Status pengguna dikemaskini.', 'User status updated.'));
        }

        return back();
    }

    // ------------------------------------------------------------------
    // Maklum balas / laporan pengguna
    // ------------------------------------------------------------------

    public function feedback(Request $request)
    {
        $filterStatus = $request->query('status', 'all');
        $filterCategory = $request->query('category', 'all');

        $query = DB::table('reports as r')->join('users as u', 'u.id', '=', 'r.user_id')
            ->select('r.*', 'u.name as submitter_name', 'u.role as submitter_role')
            ->orderByDesc('r.created_at');
        if (in_array($filterStatus, ['new', 'in_review', 'resolved'], true)) {
            $query->where('r.status', $filterStatus);
        }
        if (in_array($filterCategory, ['bug', 'suggestion', 'course_feedback', 'other'], true)) {
            $query->where('r.category', $filterCategory);
        }

        $countMap = ['new' => 0, 'in_review' => 0, 'resolved' => 0];
        foreach (DB::table('reports')->select('status', DB::raw('COUNT(*) c'))->groupBy('status')->get() as $c) {
            $countMap[$c->status] = (int) $c->c;
        }

        return view('admin.feedback', [
            'reports' => rows($query),
            'filter_status' => $filterStatus,
            'count_map' => $countMap,
            'total_count' => array_sum($countMap),
            'category_labels' => [
                'bug' => t('Bug', 'Bug'),
                'suggestion' => t('Cadangan', 'Suggestion'),
                'course_feedback' => t('Maklum Balas Kursus', 'Course Feedback'),
                'other' => t('Lain-lain', 'Other'),
            ],
            'status_labels' => [
                'new' => t('Baharu', 'New'),
                'in_review' => t('Sedang Disemak', 'In Review'),
                'resolved' => t('Selesai', 'Resolved'),
            ],
            'success' => session('success'),
        ]);
    }

    public function feedbackAction(Request $request)
    {
        if ($request->has('update_report')) {
            $status = $request->input('status');
            if (in_array($status, ['new', 'in_review', 'resolved'], true)) {
                DB::table('reports')->where('id', (int) $request->input('report_id'))->update([
                    'status' => $status,
                    'admin_notes' => trim((string) $request->input('admin_notes', '')),
                ]);

                return back()->with('success', t('Laporan dikemaskini.', 'Report updated.'));
            }
        }

        return back();
    }

    // ------------------------------------------------------------------
    // Analitik
    // ------------------------------------------------------------------

    public function analytics()
    {
        // "Aktiviti yang ditawarkan" = latihan + kuiz + nota dalam bab kursus yang diajar lecturer
        $lecturerStats = array_map(fn ($r) => (array) $r, DB::select(
            "SELECT u.id, u.name,
                (SELECT COUNT(*) FROM language_lecturers WHERE lecturer_id = u.id) as course_count,
                (SELECT COUNT(*) FROM chapters ch JOIN language_lecturers ll ON ll.language_id = ch.language_id WHERE ll.lecturer_id = u.id) as chapter_count,
                (SELECT COUNT(*) FROM exercises ex JOIN chapters ch ON ch.id = ex.chapter_id JOIN language_lecturers ll ON ll.language_id = ch.language_id WHERE ll.lecturer_id = u.id) as exercise_count,
                (SELECT COUNT(*) FROM quizzes q JOIN chapters ch ON ch.id = q.chapter_id JOIN language_lecturers ll ON ll.language_id = ch.language_id WHERE ll.lecturer_id = u.id) as quiz_count,
                (SELECT COUNT(*) FROM learning_content lc JOIN chapters ch ON ch.id = lc.chapter_id JOIN language_lecturers ll ON ll.language_id = ch.language_id WHERE ll.lecturer_id = u.id) as content_count,
                (SELECT COUNT(DISTINCT en.student_id) FROM enrollments en JOIN language_lecturers ll ON ll.language_id = en.language_id WHERE ll.lecturer_id = u.id) as enrolled_count
             FROM users u WHERE u.role = 'lecturer'"
        ));
        foreach ($lecturerStats as &$ls) {
            $ls['activity_score'] = (int) $ls['exercise_count'] + (int) $ls['quiz_count'] + (int) $ls['content_count'];
        }
        unset($ls);
        usort($lecturerStats, fn ($a, $b) => $b['activity_score'] <=> $a['activity_score']);

        $minStudents = 15;
        $subjectStats = array_map(fn ($r) => (array) $r, DB::select(
            'SELECT l.id, l.name,
                (SELECT COUNT(DISTINCT student_id) FROM enrollments WHERE language_id = l.id) as enrolled_count,
                (SELECT COUNT(*) FROM chapters WHERE language_id = l.id) as chapter_count,
                (SELECT COUNT(*) FROM exercises ex JOIN chapters ch ON ch.id = ex.chapter_id WHERE ch.language_id = l.id) as exercise_count,
                (SELECT COUNT(*) FROM quizzes q JOIN chapters ch ON ch.id = q.chapter_id WHERE ch.language_id = l.id) as quiz_count
             FROM languages l'
        ));
        $qualified = array_values(array_filter($subjectStats, fn ($s) => (int) $s['enrolled_count'] >= $minStudents
            && ((int) $s['exercise_count'] > 0 || (int) $s['quiz_count'] > 0)));
        usort($qualified, fn ($a, $b) => $b['enrolled_count'] <=> $a['enrolled_count']);

        return view('admin.analytics', [
            'lecturer_stats' => $lecturerStats,
            'qualified_subjects' => $qualified,
            'MIN_STUDENTS' => $minStudents,
        ]);
    }

    /**
     * Semak semula semua pelajar dan anugerahkan lencana yang sepatutnya sudah diperoleh.
     * Selamat dijalankan berkali-kali (tidak akan duplicate).
     */
    public function backfillBadges(Request $request, LearningService $learning)
    {
        $log = [];
        $ran = $request->has('run');

        if ($ran) {
            foreach (DB::table('users')->select('id', 'name')->where('role', 'student')->get() as $s) {
                foreach (DB::table('certificates')->where('student_id', $s->id)->pluck('language_id') as $langId) {
                    $learning->checkLanguageBadge($s->id, $langId);
                }
                $learning->checkStreakBadges($s->id);
                $learning->checkFirstCodeBadge($s->id);
                $learning->checkQuizMasterBadge($s->id);
                $log[] = $s->name;
            }
        }

        return view('admin.backfill_badges', ['log' => $log, 'ran' => $ran]);
    }
}
