<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Laman utama awam - statistik & kursus diambil terus dari database.
     */
    public function index()
    {
        $stats = [
            'students' => DB::table('users')->where('role', 'student')->count(),
            'languages' => DB::table('languages')->where('status', 'active')->count(),
            'quizzes' => DB::table('quizzes')->count(),
        ];

        $user = auth()->user();
        $dashboardUrl = $user ? dashboard_route_for($user->role) : null;

        $query = fn () => DB::table('languages as l')
            ->select('l.*', DB::raw('(SELECT COUNT(*) FROM chapters c WHERE c.language_id = l.id) as chapter_count'))
            ->where('l.status', 'active')->orderBy('l.name');

        return view('home', [
            'stats' => $stats,
            'dashboardUrl' => $dashboardUrl,
            'languages' => rows($query()->limit(3)),
            'allLanguages' => rows($query()),
            'diffMap' => ['Beginner' => t('Pemula', 'Beginner'), 'Intermediate' => t('Pertengahan', 'Intermediate'), 'Advanced' => t('Lanjutan', 'Advanced')],
        ]);
    }
}
