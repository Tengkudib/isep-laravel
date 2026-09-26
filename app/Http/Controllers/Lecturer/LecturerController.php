<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Halaman pensyarah: dashboard, laporan statistik kursus & profil.
 * Menggantikan lecturer/dashboard.php, reports.php dan profile.php.
 */
class LecturerController extends Controller
{
    /** Bahasa yang diajar oleh pensyarah ini (boleh dikongsi dengan pensyarah lain). */
    private function myLanguages(int $lecturerId): array
    {
        return rows(DB::table('languages as l')->join('language_lecturers as ll', 'll.language_id', '=', 'l.id')
            ->select('l.*')->where('ll.lecturer_id', $lecturerId));
    }

    public function dashboard(Request $request)
    {
        $lecturer = $request->user();
        $myLanguages = $this->myLanguages($lecturer->id);
        $langIds = array_column($myLanguages, 'id') ?: [0];

        $stats = [
            'courses' => count($myLanguages),
            'total_chapters' => DB::table('chapters')->whereIn('language_id', $langIds)->count(),
            'enrolled_students' => DB::table('enrollments')->whereIn('language_id', $langIds)->distinct()->count('student_id'),
            'avg_quiz_score' => round(DB::table('quiz_results as qr')->join('chapters as c', 'c.id', '=', 'qr.chapter_id')
                ->whereIn('c.language_id', $langIds)->avg('qr.percentage') ?? 0),
            'certificates_issued' => DB::table('certificates')->whereIn('language_id', $langIds)->count(),
        ];

        $courseProgress = [];
        foreach ($myLanguages as $lang) {
            $courseProgress[] = [
                'lang' => $lang,
                'enrolled' => DB::table('enrollments')->where('language_id', $lang['id'])->count(),
                'completed' => DB::table('certificates')->where('language_id', $lang['id'])->distinct()->count('student_id'),
                'avg_score' => round(DB::table('quiz_results as qr')->join('chapters as c', 'c.id', '=', 'qr.chapter_id')
                    ->where('c.language_id', $lang['id'])->avg('qr.percentage') ?? 0),
            ];
        }

        return view('lecturer.dashboard', [
            'lecturer' => $lecturer,
            'stats' => $stats,
            'my_languages' => $myLanguages,
            'course_progress' => $courseProgress,
        ]);
    }

    public function reports(Request $request)
    {
        $langIds = array_column($this->myLanguages($request->user()->id), 'id') ?: [0];
        $in = implode(',', array_map('intval', $langIds));

        // Prestasi setiap pelajar berdaftar dalam kursus pensyarah ini
        $studentReport = array_map(fn ($r) => (array) $r, DB::select(
            "SELECT u.name, u.student_id, l.name as lang_name,
                    e.enrolled_at,
                    (SELECT COUNT(*) FROM chapters c2 WHERE c2.language_id = l.id) as total_chapters,
                    (SELECT COUNT(*) FROM student_progress sp WHERE sp.student_id = u.id AND sp.language_id = l.id AND sp.chapter_status = 'completed') as chapters_done,
                    (SELECT AVG(qr.percentage) FROM quiz_results qr JOIN chapters c3 ON c3.id = qr.chapter_id WHERE qr.student_id = u.id AND c3.language_id = l.id) as quiz_avg,
                    (SELECT COUNT(*) FROM certificates cert WHERE cert.student_id = u.id AND cert.language_id = l.id) as has_certificate
             FROM enrollments e
             JOIN users u ON u.id = e.student_id
             JOIN languages l ON l.id = e.language_id
             WHERE e.language_id IN ($in)
             ORDER BY l.name, chapters_done DESC"
        ));

        // Bab paling sukar (skor kuiz terendah)
        $difficult = array_map(fn ($r) => (array) $r, DB::select(
            "SELECT c.title, l.name as lang_name, AVG(qr.percentage) as avg_pct, COUNT(qr.id) as attempts
             FROM quiz_results qr
             JOIN chapters c ON c.id = qr.chapter_id
             JOIN languages l ON l.id = c.language_id
             WHERE c.language_id IN ($in)
             GROUP BY c.id ORDER BY avg_pct ASC LIMIT 5"
        ));

        // Funnel penyelesaian bab
        $funnel = array_map(fn ($r) => (array) $r, DB::select(
            "SELECT c.chapter_number, c.title, l.name as lang_name,
                    COUNT(CASE WHEN sp.chapter_status = 'completed' THEN 1 END) as completed_count
             FROM chapters c
             JOIN languages l ON l.id = c.language_id
             LEFT JOIN student_progress sp ON sp.chapter_id = c.id
             WHERE c.language_id IN ($in)
             GROUP BY c.id ORDER BY l.name, c.chapter_number ASC"
        ));

        return view('lecturer.reports', [
            'student_report' => $studentReport,
            'difficult_chapters' => $difficult,
            'funnel' => $funnel,
        ]);
    }

    public function profile(Request $request)
    {
        return view('lecturer.profile', [
            'user' => row(DB::table('users')->where('id', $request->user()->id)),
            'error' => session('error'),
            'error_field' => session('error_field'),
            'success' => session('success'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $id = (int) $request->user()->id;

        if ($request->has('update_profile')) {
            $name = trim((string) $request->input('name'));
            if (! $name) {
                return back()->with('error', t('Nama diperlukan.', 'Name is required.'));
            }
            DB::table('users')->where('id', $id)->update(['name' => $name]);

            return back()->with('success', t('Profil berjaya dikemaskini.', 'Profile updated successfully.'));
        }

        if ($request->has('change_password')) {
            $current = (string) $request->input('current_password');
            $new = (string) $request->input('new_password');
            $confirm = (string) $request->input('confirm_password');

            if (! password_verify($current, DB::table('users')->where('id', $id)->value('password'))) {
                return back()->with(['error' => t('Kata laluan semasa tidak tepat.', 'Current password is incorrect.'), 'error_field' => 'current_password']);
            }
            if (strlen($new) < 6) {
                return back()->with(['error' => t('Kata laluan baharu mesti sekurang-kurangnya 6 aksara.', 'New password must be at least 6 characters.'), 'error_field' => 'new_password']);
            }
            if ($new !== $confirm) {
                return back()->with(['error' => t('Pengesahan kata laluan tidak sepadan.', 'Password confirmation does not match.'), 'error_field' => 'confirm_password']);
            }
            DB::table('users')->where('id', $id)->update(['password' => Hash::make($new)]);

            return back()->with('success', t('Kata laluan berjaya ditukar.', 'Password changed successfully.'));
        }

        return back();
    }
}
