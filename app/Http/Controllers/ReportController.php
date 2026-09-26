<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Laporan & maklum balas (bug, cadangan, maklum balas kursus) untuk pelajar dan pensyarah.
 * Menggantikan includes/report_page.php + student/report.php + lecturer/report.php.
 */
class ReportController extends Controller
{
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function show(Request $request)
    {
        $user = $request->user();

        return view($user->role === 'lecturer' ? 'lecturer.report' : 'student.report', [
            'back_url' => dashboard_route_for($user->role),
            'my_reports' => rows(DB::table('reports')->where('user_id', $user->id)->orderByDesc('created_at')),
            'category_labels' => [
                'bug' => t('Laporkan Bug', 'Report a Bug'),
                'suggestion' => t('Cadangan', 'Suggestion'),
                'course_feedback' => t('Maklum Balas Kursus', 'Course Feedback'),
                'other' => t('Lain-lain', 'Other'),
            ],
            'status_labels' => [
                'new' => t('Baharu', 'New'),
                'in_review' => t('Sedang Disemak', 'In Review'),
                'resolved' => t('Selesai', 'Resolved'),
            ],
            'error' => session('error'),
            'success' => session('success'),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Fail melebihi post_max_size - PHP buang seluruh badan permintaan
        if (empty($request->all()) && empty($request->allFiles()) && (int) $request->server('CONTENT_LENGTH', 0) > 0) {
            return back()->with('error', t('Fail terlalu besar untuk pelayan menerimanya.', 'File is too large for the server to accept.'));
        }

        if (! $request->has('submit_report')) {
            return back();
        }

        $category = $request->input('category', 'other');
        if (! in_array($category, ['bug', 'suggestion', 'course_feedback', 'other'], true)) {
            $category = 'other';
        }
        $title = trim((string) $request->input('title', ''));
        $description = trim((string) $request->input('description', ''));

        if (! $title || ! $description) {
            return back()->with('error', t('Sila isi tajuk dan penerangan.', 'Please fill in the title and description.'));
        }

        $screenshotPath = null;
        $file = $request->file('screenshot');
        if ($file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, self::ALLOWED_EXT)) {
                return back()->with('error', t('Jenis fail tidak dibenarkan. Hanya JPG, PNG, GIF, WEBP.', 'File type not allowed. Only JPG, PNG, GIF, WEBP.'));
            }
            if ($file->getSize() > 10 * 1024 * 1024) {
                return back()->with('error', t('Saiz gambar melebihi had 10MB.', 'Image size exceeds the 10MB limit.'));
            }
            if (! $file->isValid()) {
                return back()->with('error', t('Gagal memuat naik gambar.', 'Failed to upload the image.'));
            }

            $safeName = uniqid('report_').'.'.$ext;
            $file->move(public_path('uploads/reports'), $safeName);
            $screenshotPath = 'uploads/reports/'.$safeName;
        }

        DB::table('reports')->insert([
            'user_id' => $user->id,
            'role' => $user->role,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'screenshot_path' => $screenshotPath,
        ]);

        return back()->with('success', t('Laporan anda telah dihantar. Terima kasih atas maklum balas anda!', 'Your report has been submitted. Thank you for your feedback!'));
    }
}
