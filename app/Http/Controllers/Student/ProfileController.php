<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LearningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Profil pelajar: kemaskini maklumat & tukar kata laluan (student/profile.php).
 */
class ProfileController extends Controller
{
    public function show(Request $request, LearningService $learning)
    {
        $studentId = (int) $request->user()->id;
        $user = row(DB::table('users')->where('id', $studentId));
        $cosmetics = $learning->getEquippedCosmetics($user);

        return view('student.profile', [
            'user' => $user,
            'student_id' => $studentId,
            'total_xp_earned' => $learning->getTotalXpEarned($studentId),
            'stats' => [
                'chapters' => DB::table('student_progress')->where('student_id', $studentId)->where('chapter_status', 'completed')->count(),
                'certificates' => DB::table('certificates')->where('student_id', $studentId)->count(),
                'badges' => DB::table('student_badges')->where('student_id', $studentId)->count(),
            ],
            'cosmetics' => $cosmetics,
            'border_style' => $cosmetics['border_style'],
            'error' => session('error'),
            'error_field' => session('error_field'),
            'success' => session('success'),
        ]);
    }

    public function update(Request $request)
    {
        $studentId = (int) $request->user()->id;

        if ($request->has('update_profile')) {
            $name = trim((string) $request->input('name'));
            $email = trim((string) $request->input('email', '')) ?: null;
            $programme = trim((string) $request->input('programme'));
            $semester = $request->input('semester') !== null && $request->input('semester') !== '' ? (int) $request->input('semester') : null;
            $sessionYear = trim((string) $request->input('session_year', '')) ?: null;

            if (! $name) {
                return back()->with('error', t('Nama diperlukan.', 'Name is required.'));
            }
            if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->with(['error' => t('Format emel tidak sah.', 'Invalid email format.'), 'error_field' => 'email']);
            }
            if ($email && DB::table('users')->where('email', $email)->where('id', '!=', $studentId)->exists()) {
                return back()->with(['error' => t('Emel ini sudah digunakan oleh akaun lain.', 'This email is already used by another account.'), 'error_field' => 'email']);
            }

            DB::table('users')->where('id', $studentId)->update([
                'name' => $name, 'email' => $email, 'programme' => $programme,
                'semester' => $semester, 'session_year' => $sessionYear,
            ]);

            return back()->with('success', t('Profil berjaya dikemaskini.', 'Profile updated successfully.'));
        }

        if ($request->has('change_password')) {
            $current = (string) $request->input('current_password');
            $new = (string) $request->input('new_password');
            $confirm = (string) $request->input('confirm_password');
            $hash = DB::table('users')->where('id', $studentId)->value('password');

            if (! password_verify($current, $hash)) {
                return back()->with(['error' => t('Kata laluan semasa tidak tepat.', 'Current password is incorrect.'), 'error_field' => 'current_password']);
            }
            if (strlen($new) < 6) {
                return back()->with(['error' => t('Kata laluan baharu mesti sekurang-kurangnya 6 aksara.', 'New password must be at least 6 characters.'), 'error_field' => 'new_password']);
            }
            if ($new !== $confirm) {
                return back()->with(['error' => t('Pengesahan kata laluan tidak sepadan.', 'Password confirmation does not match.'), 'error_field' => 'confirm_password']);
            }

            DB::table('users')->where('id', $studentId)->update(['password' => Hash::make($new)]);

            return back()->with('success', t('Kata laluan berjaya ditukar.', 'Password changed successfully.'));
        }

        return back();
    }
}
