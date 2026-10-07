<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Ujian menyeluruh semua fungsi sistem terhadap pangkalan data isep_db tempatan.
 * Setiap ujian berjalan dalam transaksi yang di-rollback - data sebenar tidak berubah.
 */
class SystemTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql'];

    private const STUDENT = 3;      // ALI AHMAD
    private const LECTURER = 7;     // ADAM - pemilik bahasa 1, 3, 4
    private const ADMIN = 1;        // ADMIN ISEP

    public function createApplication()
    {
        $app = parent::createApplication();
        // phpunit.xml guna sqlite :memory: - ujian ini perlukan skema sebenar isep_db
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'isep_db');

        return $app;
    }

    private function as(int $id): static
    {
        return $this->actingAs(User::findOrFail($id));
    }

    private function xp(int $id): int
    {
        return (int) DB::table('users')->where('id', $id)->value('xp_points');
    }

    // ------------------------------------------------------------------
    // Awam & pengesahan
    // ------------------------------------------------------------------

    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_login_with_valid_and_invalid_password(): void
    {
        DB::table('users')->where('id', self::STUDENT)->update(['password' => Hash::make('secret123')]);

        $this->post('/login', ['username' => 'ali ahmad', 'password' => 'wrong'])->assertSessionHas('error');
        $this->assertGuest();

        $this->post('/login', ['username' => 'ali ahmad', 'password' => 'secret123'])->assertRedirect();
        $this->assertAuthenticatedAs(User::find(self::STUDENT));
    }

    public function test_each_role_is_sent_to_its_own_dashboard_after_login(): void
    {
        foreach ([self::STUDENT => '/student/dashboard', self::LECTURER => '/lecturer/dashboard', self::ADMIN => '/admin'] as $id => $url) {
            DB::table('users')->where('id', $id)->update(['password' => Hash::make('secret123')]);
            $username = DB::table('users')->where('id', $id)->value('username');
            $this->post('/login', ['username' => $username, 'password' => 'secret123'])->assertRedirect($url);
            $this->post('/logout');
        }
    }

    public function test_logout(): void
    {
        $this->as(self::STUDENT)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_logout_by_plain_link_does_not_log_out(): void
    {
        // GET /logout (cth. imej/pautan dari laman lain) tidak lagi log keluar pengguna
        $this->as(self::STUDENT)->get('/logout')->assertRedirect('/');
        $this->assertAuthenticated();
        $this->get('/student/dashboard')->assertOk()->assertSee('isepLogoutForm', false)->assertSee('data-logout', false);
    }

    public function test_roles_cannot_open_other_roles_pages(): void
    {
        $this->as(self::STUDENT)->get('/admin')->assertForbidden();
        $this->as(self::STUDENT)->get('/lecturer/dashboard')->assertForbidden();
        $this->as(self::LECTURER)->get('/admin')->assertForbidden();
        $this->as(self::LECTURER)->get('/student/dashboard')->assertForbidden();
        $this->as(self::ADMIN)->get('/student/dashboard')->assertForbidden();
    }

    public function test_reset_password_page_tells_user_to_contact_admin(): void
    {
        $this->get('/reset-password')->assertOk()->assertSee('pentadbir sistem')->assertDontSee('name="new_password"', false);
    }

    // ------------------------------------------------------------------
    // Pelajar - halaman
    // ------------------------------------------------------------------

    public function test_all_student_pages_load(): void
    {
        $this->as(self::STUDENT);
        foreach ([
            '/student/dashboard', '/student/language/python', '/student/language/php', '/student/enroll/mysql',
            '/student/chapter/1', '/student/chapter/11', '/student/chapter/9', '/student/certificates',
            '/student/leaderboard', '/student/profile', '/student/shop', '/student/games',
            '/student/games/chess', '/student/games/dam', '/student/report',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_pages_in_english_also_load(): void
    {
        $this->as(self::STUDENT)->withUnencryptedCookie('isep_lang', 'en');
        foreach (['/student/dashboard', '/student/chapter/11', '/student/shop', '/student/profile'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_notes_html_is_sanitised_before_students_see_it(): void
    {
        DB::table('learning_content')->insert([
            'chapter_id' => 11, 'content_type' => 'notes', 'title' => 'Nota Ujian', 'order_number' => 1,
            'content' => '<p onclick="steal()">Nota <b>tebal</b></p><script>steal()</script><a href="javascript:steal()">klik</a>',
        ]);
        $this->as(self::STUDENT)->get('/student/chapter/11')->assertOk()
            ->assertSee('<p>Nota <b>tebal</b></p>', false)
            ->assertDontSee('<script>steal()', false)
            ->assertDontSee('onclick="steal()"', false)
            ->assertDontSee('javascript:steal()', false);
    }

    public function test_certificate_page_for_owner_and_unknown_code(): void
    {
        $code = DB::table('certificates')->where('student_id', self::STUDENT)->value('certificate_code');
        $this->as(self::STUDENT)->get('/student/certificate/'.$code)->assertOk();
        $this->as(self::STUDENT)->get('/student/certificate/NOT-A-REAL-CODE')->assertNotFound();
    }

    public function test_student_cannot_view_another_students_certificate(): void
    {
        $other = DB::table('certificates')->where('student_id', '!=', self::STUDENT)->value('certificate_code');
        if (! $other) {
            $this->markTestSkipped('Tiada sijil pelajar lain untuk diuji.');
        }
        $this->assertNotEquals(200, $this->as(self::STUDENT)->get('/student/certificate/'.$other)->status());
    }

    public function test_unknown_language_and_chapter_give_404(): void
    {
        $this->as(self::STUDENT)->get('/student/language/cobol')->assertNotFound();
        $this->as(self::STUDENT)->get('/student/chapter/99999')->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Pelajar - pembelajaran
    // ------------------------------------------------------------------

    public function test_enroll_requires_pledge_then_succeeds(): void
    {
        DB::table('enrollments')->where('student_id', self::STUDENT)->where('language_id', 9)->delete();
        $this->as(self::STUDENT);

        $this->post('/student/enroll/mysql', [])->assertSessionHas('error');
        $this->post('/student/enroll/mysql', ['agree' => 1])->assertRedirect('/student/language/mysql');
        $this->assertDatabaseHas('enrollments', ['student_id' => self::STUDENT, 'language_id' => 9], 'mysql');
    }

    public function test_mark_notes_video_try_it_complete(): void
    {
        $this->as(self::STUDENT);
        foreach (['mark_notes' => 'notes_status', 'mark_video' => 'video_status', 'mark_try_it' => 'try_it_status'] as $btn => $col) {
            $this->post('/student/chapter/11', [$btn => 1])->assertRedirect('/student/chapter/11');
            $this->assertSame('completed', DB::table('student_progress')->where('student_id', self::STUDENT)->where('chapter_id', 11)->value($col));
        }
    }

    public function test_exercise_answer_is_checked_and_correct_exercise_is_hidden(): void
    {
        $exercise = DB::table('exercises')->where('chapter_id', 11)->whereNotNull('expected_output')->where('expected_output', '!=', '')->first();
        if (! $exercise) {
            $this->markTestSkipped('Bab 11 tiada latihan dengan output dijangka.');
        }
        DB::table('exercise_submissions')->where('student_id', self::STUDENT)->where('exercise_id', $exercise->id)->delete();
        DB::table('users')->where('id', self::STUDENT)->update(['xp_booster_until' => null]);
        $submit = fn (string $answer) => $this->as(self::STUDENT)->post('/student/chapter/11', ['submit_exercise' => 1, 'exercise_id' => $exercise->id, 'answer_code' => $answer]);
        $before = $this->xp(self::STUDENT);

        $submit('jawapan yang salah')->assertSessionHas('exercise_error')->assertSessionHas('exercise_last_answer', 'jawapan yang salah');
        $this->assertSame($before, $this->xp(self::STUDENT));
        $this->assertSame('attempted', DB::table('exercise_submissions')->where('student_id', self::STUDENT)->where('exercise_id', $exercise->id)->value('status'));
        $this->as(self::STUDENT)->get('/student/chapter/11')->assertSee('id="exercise-'.$exercise->id.'"', false);

        $submit(strtoupper(preg_replace('/\s+/', '', $exercise->expected_output)).'.')->assertSessionHas('message');
        $this->assertSame($before + (int) $exercise->points, $this->xp(self::STUDENT));
        $this->as(self::STUDENT)->get('/student/chapter/11')->assertDontSee('id="exercise-'.$exercise->id.'"', false);

        $submit($exercise->expected_output);
        $this->assertSame($before + (int) $exercise->points, $this->xp(self::STUDENT));
        $this->assertSame(1, DB::table('exercise_submissions')->where('student_id', self::STUDENT)->where('exercise_id', $exercise->id)->where('status', 'completed')->count());
    }

    public function test_exercise_from_another_chapter_is_rejected(): void
    {
        $foreign = DB::table('exercises')->where('chapter_id', '!=', 11)->value('id');
        $before = DB::table('exercise_submissions')->count();
        $this->as(self::STUDENT)->post('/student/chapter/11', ['submit_exercise' => 1, 'exercise_id' => $foreign, 'answer_code' => 'x']);
        $this->assertSame($before, DB::table('exercise_submissions')->count());
    }

    public function test_empty_exercise_answer_is_rejected(): void
    {
        $exercise = DB::table('exercises')->where('chapter_id', 11)->value('id');
        if (! $exercise) {
            $this->markTestSkipped('Bab 11 tiada latihan.');
        }
        $before = DB::table('exercise_submissions')->count();
        $xpBefore = $this->xp(self::STUDENT);

        foreach (['', "   \n\t "] as $answer) {
            $this->as(self::STUDENT)->post('/student/chapter/11', ['submit_exercise' => 1, 'exercise_id' => $exercise, 'answer_code' => $answer])
                ->assertRedirectContains('/student/chapter/11')
                ->assertSessionHas('exercise_error')
                ->assertSessionHas('active_tab', 'exercise-tab');
        }

        $this->assertSame($before, DB::table('exercise_submissions')->count());
        $this->assertSame($xpBefore, $this->xp(self::STUDENT));
    }

    public function test_quiz_pass_and_fail(): void
    {
        $quizzes = DB::table('quizzes')->where('chapter_id', 11)->get();
        if ($quizzes->isEmpty()) {
            $this->markTestSkipped('Bab 11 tiada kuiz.');
        }
        $this->as(self::STUDENT);
        DB::table('student_progress')->where('student_id', self::STUDENT)->where('chapter_id', 11)->update(['quiz_status' => 'failed']);
        DB::table('users')->where('id', self::STUDENT)->update(['xp_booster_until' => null]);

        $wrong = ['submit_quiz' => 1];
        $right = ['submit_quiz' => 1];
        foreach ($quizzes as $q) {
            $wrong['q'.$q->id] = '__wrong__';
            $right['q'.$q->id] = $q->correct_answer;
        }

        $this->post('/student/chapter/11', $wrong)->assertSessionHas('last_quiz_result', fn ($r) => $r['pass'] === false);
        $before = $this->xp(self::STUDENT);
        $this->post('/student/chapter/11', $right)->assertSessionHas('last_quiz_result', fn ($r) => $r['pass'] === true && (float) $r['percentage'] === 100.0);
        $this->assertSame($before + 30, $this->xp(self::STUDENT));

        // Lulus semula bab yang sama tidak memberi XP lagi
        $this->post('/student/chapter/11', $right)->assertSessionHas('last_quiz_result', fn ($r) => $r['pass'] === true);
        $this->assertSame($before + 30, $this->xp(self::STUDENT));
    }

    public function test_locked_chapter_cannot_be_submitted(): void
    {
        $this->as(self::STUDENT)->post('/student/chapter/9', ['mark_notes' => 1])->assertRedirect('/student/chapter/9');
        $this->assertNotSame('completed', DB::table('student_progress')->where('student_id', self::STUDENT)->where('chapter_id', 9)->value('notes_status'));
    }

    // ------------------------------------------------------------------
    // Pelajar - profil, kedai, laporan
    // ------------------------------------------------------------------

    public function test_profile_update_and_validation(): void
    {
        $this->as(self::STUDENT);
        $this->post('/student/profile', ['update_profile' => 1, 'name' => ''])->assertSessionHas('error');
        $this->post('/student/profile', ['update_profile' => 1, 'name' => 'Ali', 'email' => 'not-an-email'])->assertSessionHas('error');
        $this->post('/student/profile', ['update_profile' => 1, 'name' => 'Ali Test', 'email' => 'ali.test@example.test', 'programme' => 'DIT', 'semester' => 3])
            ->assertSessionHas('success');
        $this->assertSame('Ali Test', DB::table('users')->where('id', self::STUDENT)->value('name'));
    }

    public function test_change_password(): void
    {
        DB::table('users')->where('id', self::STUDENT)->update(['password' => Hash::make('oldpass1')]);
        $this->as(self::STUDENT);
        $this->post('/student/profile', ['change_password' => 1, 'current_password' => 'nope', 'new_password' => 'newpass1', 'confirm_password' => 'newpass1'])->assertSessionHas('error');
        $this->post('/student/profile', ['change_password' => 1, 'current_password' => 'oldpass1', 'new_password' => 'newpass1', 'confirm_password' => 'different'])->assertSessionHas('error');
        $this->post('/student/profile', ['change_password' => 1, 'current_password' => 'oldpass1', 'new_password' => 'newpass1', 'confirm_password' => 'newpass1'])->assertSessionHas('success');
        $this->assertTrue(password_verify('newpass1', DB::table('users')->where('id', self::STUDENT)->value('password')));
    }

    public function test_shop_buy_with_and_without_enough_xp_and_equip(): void
    {
        $this->as(self::STUDENT);

        DB::table('users')->where('id', self::STUDENT)->update(['xp_points' => 0]);
        $this->post('/student/shop', ['buy_item' => 1, 'item_id' => 2])->assertSessionHas('message_type', 'danger');

        DB::table('users')->where('id', self::STUDENT)->update(['xp_points' => 1000]);
        DB::table('student_purchases')->where('student_id', self::STUDENT)->where('item_id', 2)->delete();
        $this->post('/student/shop', ['buy_item' => 1, 'item_id' => 2])->assertSessionHas('message_type', 'success');
        $this->assertSame(950, $this->xp(self::STUDENT));

        $this->post('/student/shop', ['equip_item' => 1, 'item_type' => 'border', 'item_id' => 2])->assertSessionHas('message_type', 'success');
        $this->assertSame(2, (int) DB::table('users')->where('id', self::STUDENT)->value('equipped_border_id'));

        $this->post('/student/shop', ['buy_item' => 1, 'item_id' => 99999])->assertSessionHas('message_type', 'danger');
    }

    public function test_leaderboard_podium_and_sidebar_name_effect(): void
    {
        DB::table('student_purchases')->insertOrIgnore(['student_id' => self::STUDENT, 'item_id' => 31]);
        DB::table('users')->where('id', self::STUDENT)->update(['equipped_name_effect_id' => 31]);

        $page = $this->as(self::STUDENT)->get('/student/leaderboard?period=overall')->assertOk();
        $page->assertSee('class="podium"', false)->assertSee('podium-crown', false);
        $page->assertSee('<div class="name"><span class="name-fx-shimmer">', false);
    }

    public function test_level_rewards_are_granted_and_cannot_be_bought(): void
    {
        $id = DB::table('users')->insertGetId(['name' => 'UJIAN LEVEL', 'username' => 'UJIAN LEVEL '.uniqid(), 'password' => Hash::make('secret123'), 'role' => 'student', 'status' => 'active']);
        DB::table('xp_log')->insert(['student_id' => $id, 'amount' => 1000, 'reason' => 'ujian']);
        DB::table('users')->where('id', $id)->update(['xp_points' => 5000]);

        $page = $this->as($id)->get('/student/dashboard')->assertOk();
        $page->assertSee('Ganjaran level baharu!')->assertSee('id="levelRewardsModal"', false)->assertSee('Pelajar Rajin');
        $owned = DB::table('student_purchases as sp')->join('shop_items as si', 'si.id', '=', 'sp.item_id')
            ->where('sp.student_id', $id)->whereNotNull('si.unlock_level')->pluck('si.unlock_level')->map(fn ($v) => (int) $v)->sort()->values()->all();
        $this->assertSame([2, 3], $owned);
        $this->as($id)->get('/student/dashboard')->assertDontSee('Ganjaran level baharu!');

        $lockedReward = DB::table('shop_items')->where('unlock_level', 5)->value('id');
        $this->as($id)->post('/student/shop', ['buy_item' => 1, 'item_id' => $lockedReward])->assertSessionHas('message_type', 'danger');
        $this->assertSame(5000, $this->xp($id));
        $this->as($id)->get('/student/shop?tab=border')->assertSee('Ganjaran Level 5');
    }

    public function test_only_one_xp_booster_can_be_active(): void
    {
        $this->as(self::STUDENT);
        DB::table('users')->where('id', self::STUDENT)->update(['xp_points' => 1000, 'xp_booster_until' => null]);

        $this->post('/student/shop', ['buy_item' => 1, 'item_id' => 14])->assertSessionHas('message_type', 'success');
        $this->assertSame(850, $this->xp(self::STUDENT));
        $this->get('/student/shop?tab=booster')->assertSee('Tunggu booster tamat');

        $this->post('/student/shop', ['buy_item' => 1, 'item_id' => 15])->assertSessionHas('message_type', 'danger');
        $this->assertSame(850, $this->xp(self::STUDENT));
        $this->assertSame('3.0', (string) DB::table('users')->where('id', self::STUDENT)->value('xp_booster_multiplier'));

        DB::table('users')->where('id', self::STUDENT)->update(['xp_booster_until' => DB::raw('NOW() - INTERVAL 1 MINUTE')]);
        $this->post('/student/shop', ['buy_item' => 1, 'item_id' => 15])->assertSessionHas('message_type', 'success');
        $this->assertSame(450, $this->xp(self::STUDENT));
    }

    public function test_student_and_lecturer_can_submit_report(): void
    {
        $this->as(self::STUDENT)->post('/student/report', ['submit_report' => 1, 'title' => '', 'description' => ''])->assertSessionHas('error');
        $this->as(self::STUDENT)->post('/student/report', ['submit_report' => 1, 'category' => 'bug', 'title' => 'Ujian', 'description' => 'Ujian automatik'])
            ->assertSessionMissing('error');
        $this->assertDatabaseHas('reports', ['user_id' => self::STUDENT, 'title' => 'Ujian'], 'mysql');

        $this->as(self::LECTURER)->post('/lecturer/report', ['submit_report' => 1, 'category' => 'suggestion', 'title' => 'Ujian L', 'description' => 'Ujian automatik'])
            ->assertSessionMissing('error');
        $this->assertDatabaseHas('reports', ['user_id' => self::LECTURER, 'title' => 'Ujian L'], 'mysql');
    }

    // ------------------------------------------------------------------
    // Pelajar - chatbot (Gemini dipalsukan, tiada panggilan sebenar)
    // ------------------------------------------------------------------

    public function test_chatbot_replies_and_validates(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Jawapan ujian']]]]],
        ])]);
        $this->as(self::STUDENT);

        $this->postJson('/student/chatbot', ['message' => ''])->assertJsonStructure(['error']);
        $this->postJson('/student/chatbot', ['message' => str_repeat('a', 1501)])->assertJsonStructure(['error']);
        $this->postJson('/student/chatbot', ['message' => 'hi', 'chapter_id' => 11, 'history' => '[]'])->assertExactJson(['reply' => 'Jawapan ujian']);
    }

    public function test_chatbot_handles_api_failure_gracefully(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response(['error' => ['message' => 'quota']], 429)]);
        $this->as(self::STUDENT)->postJson('/student/chatbot', ['message' => 'hi'])->assertOk()->assertJsonStructure(['error']);
    }

    public function test_chatbot_not_for_lecturer(): void
    {
        $this->as(self::LECTURER)->postJson('/student/chatbot', ['message' => 'hi'])->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Pelajar - Cuba Sendiri (Judge0 & Gemini dipalsukan)
    // ------------------------------------------------------------------

    public function test_code_lab_runs_java_and_php_on_the_server(): void
    {
        Http::fake(['ce.judge0.com/*' => Http::response([
            'stdout' => base64_encode("Jumlah: 6\n"), 'stderr' => null, 'compile_output' => null,
            'status' => ['id' => 3, 'description' => 'Accepted'], 'time' => '0.05',
        ])]);
        $this->as(self::STUDENT);

        $this->postJson('/student/code/run', ['language' => 'java', 'code' => 'public class Hello { public static void main(String[] a) { new Hello(); } }'])
            ->assertOk()->assertJsonPath('ok', true)->assertJsonPath('stdout', "Jumlah: 6\n");
        // Kelas public pelajar dinamakan semula kepada Main (keperluan Judge0), termasuk rujukan kepadanya
        Http::assertSent(fn ($r) => $r['language_id'] === 91
            && base64_decode($r['source_code']) === 'public class Main { public static void main(String[] a) { new Main(); } }');

        $this->postJson('/student/code/run', ['language' => 'php', 'code' => '<?php echo 1;'])->assertOk()->assertJsonPath('ok', true);
        Http::assertSent(fn ($r) => $r['language_id'] === 98);

        $this->postJson('/student/code/run', ['language' => 'python', 'code' => 'print(1)'])->assertStatus(400);
        $this->postJson('/student/code/run', ['language' => 'java', 'code' => '   '])->assertStatus(422);
    }

    public function test_code_lab_reports_compile_errors_and_runner_outage(): void
    {
        Http::fake(['ce.judge0.com/*' => Http::sequence()
            ->push([
                'stdout' => null, 'stderr' => null, 'compile_output' => base64_encode('Main.java:1: error: incompatible types'),
                'status' => ['id' => 6, 'description' => 'Compilation Error'],
            ])
            ->push('Service Unavailable', 503),
        ]);
        $this->as(self::STUDENT)->postJson('/student/code/run', ['language' => 'java', 'code' => 'x'])
            ->assertOk()->assertJsonPath('ok', false)->assertJsonPath('compile_output', 'Main.java:1: error: incompatible types');

        $this->postJson('/student/code/run', ['language' => 'java', 'code' => 'x'])->assertStatus(502)->assertJsonStructure(['error']);
    }

    public function test_code_lab_run_is_rate_limited(): void
    {
        Http::fake(['ce.judge0.com/*' => Http::response(['stdout' => '', 'status' => ['id' => 3, 'description' => 'Accepted']])]);
        $this->as(self::STUDENT);
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/student/code/run', ['language' => 'php', 'code' => '<?php echo 1;'])->assertOk();
        }
        $this->postJson('/student/code/run', ['language' => 'php', 'code' => '<?php echo 1;'])->assertStatus(429);
    }

    public function test_code_lab_ai_tutor_explains_challenges_and_reviews(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Penerangan AI']]]]]])]);
        $this->as(self::STUDENT);

        foreach (['explain', 'challenge', 'review'] as $mode) {
            $this->postJson('/student/code/assist', ['mode' => $mode, 'chapter_id' => 11, 'code' => '<?php echo 1;', 'output' => '1'])
                ->assertOk()->assertJsonPath('reply', 'Penerangan AI');
        }
        // Prompt semakan menyertakan kod, output dan cabaran pelajar
        $this->postJson('/student/code/assist', ['mode' => 'review', 'chapter_id' => 11, 'code' => '<?php echo 42;', 'output' => '42', 'challenge' => 'Cetak 42']);
        Http::assertSent(fn ($r) => str_contains($r['contents'][0]['parts'][0]['text'], 'echo 42') && str_contains($r['contents'][0]['parts'][0]['text'], 'Cetak 42'));

        $this->postJson('/student/code/assist', ['mode' => 'hack', 'chapter_id' => 11, 'code' => 'x'])->assertStatus(400);
        $this->postJson('/student/code/assist', ['mode' => 'explain', 'chapter_id' => 11, 'code' => ''])->assertStatus(422);
        $this->postJson('/student/code/assist', ['mode' => 'challenge', 'chapter_id' => 99999])->assertNotFound();
    }

    public function test_code_lab_is_for_students_only(): void
    {
        $this->as(self::LECTURER)->postJson('/student/code/run', ['language' => 'java', 'code' => 'x'])->assertForbidden();
    }

    public function test_try_it_tab_shows_for_every_language(): void
    {
        $this->as(self::STUDENT);
        foreach ([1 => 'Pyodide', 18 => 'console.log()', 16 => 'pratonton', 11 => 'PHP 8.3', 6 => 'JDK 17'] as $chapter => $hint) {
            $this->get('/student/chapter/'.$chapter)->assertOk()->assertSee('tryitEditor', false)->assertSee($hint)->assertSee('Semak Kod Saya');
        }
    }

    // ------------------------------------------------------------------
    // Pelajar - permainan
    // ------------------------------------------------------------------

    public function test_game_vs_computer_flow(): void
    {
        $this->as(self::STUDENT);
        $match = $this->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'chess', 'mode' => 'ai', 'ai_difficulty' => 'easy'])
            ->assertOk()->json('match');
        $this->assertNotEmpty($match['match_id']);

        $this->getJson('/student/games/api?action=state&match_id='.$match['match_id'])->assertOk()->assertJsonPath('match.match_id', $match['match_id']);
        $this->postJson('/student/games/api', ['action' => 'finish', 'match_id' => $match['match_id'], 'winner' => 'player1'])->assertOk();
    }

    public function test_game_pvp_create_join_move_resign(): void
    {
        $other = (int) DB::table('users')->where('role', 'student')->where('id', '!=', self::STUDENT)->value('id');

        $match = $this->as(self::STUDENT)->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'dam', 'mode' => 'pvp'])->assertOk()->json('match');
        $this->getJson('/student/games/api?action=lobby&game_type=dam')->assertOk();
        $this->getJson('/student/games/api?action=my_waiting&game_type=dam')->assertOk();

        $this->as(self::STUDENT)->postJson('/student/games/api', ['action' => 'join', 'match_id' => $match['match_id']])->assertStatus(400); // sendiri
        $this->as($other)->postJson('/student/games/api', ['action' => 'join', 'match_id' => $match['match_id']])->assertOk();

        $this->as($other)->postJson('/student/games/api', ['action' => 'move', 'match_id' => $match['match_id'], 'board_state' => 'x', 'next_turn' => 'player2'])
            ->assertStatus(409); // bukan giliran
        $this->as(self::STUDENT)->postJson('/student/games/api', ['action' => 'move', 'match_id' => $match['match_id'], 'board_state' => 'x', 'next_turn' => 'player2'])->assertOk();

        $third = (int) DB::table('users')->where('role', 'student')->whereNotIn('id', [self::STUDENT, $other])->value('id');
        $this->as($third)->getJson('/student/games/api?action=state&match_id='.$match['match_id'])->assertForbidden();

        $this->as($other)->postJson('/student/games/api', ['action' => 'resign', 'match_id' => $match['match_id']])->assertOk();
        $this->as(self::STUDENT)->postJson('/student/games/api', ['action' => 'move', 'match_id' => $match['match_id'], 'board_state' => 'y', 'next_turn' => 'player1'])->assertStatus(409);
    }

    public function test_pvp_opponent_who_leaves_loses_and_dead_matches_leave_the_lobby(): void
    {
        $other = (int) DB::table('users')->where('role', 'student')->where('id', '!=', self::STUDENT)->value('id');

        // Perlawanan menunggu yang pembuatnya sudah keluar tidak dipaparkan di lobi
        $dead = $this->as(self::STUDENT)->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'chess', 'mode' => 'pvp'])->json('match.match_id');
        \Illuminate\Support\Facades\Cache::forget('game-presence:'.$dead.':'.self::STUDENT);
        $lobby = $this->as($other)->getJson('/student/games/api?action=lobby&game_type=chess')->json('open_matches');
        $this->assertNotContains($dead, array_column($lobby, 'id'));
        $this->assertSame('abandoned', DB::table('game_matches')->where('id', $dead)->value('status'));

        // Perlawanan aktif: yang masih ada kekal ongoing selagi lawan aktif
        $id = $this->as(self::STUDENT)->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'chess', 'mode' => 'pvp'])->json('match.match_id');
        $this->assertContains($id, array_column($this->as($other)->getJson('/student/games/api?action=lobby&game_type=chess')->json('open_matches'), 'id'));
        $this->as($other)->postJson('/student/games/api', ['action' => 'join', 'match_id' => $id])->assertOk();
        $this->as(self::STUDENT)->getJson('/student/games/api?action=state&match_id='.$id)->assertJsonPath('match.status', 'ongoing');

        // Lawan menutup tab -> selepas tempoh kehadiran tamat, pemain yang tinggal menang
        \Illuminate\Support\Facades\Cache::forget('game-presence:'.$id.':'.$other);
        $this->as(self::STUDENT)->getJson('/student/games/api?action=state&match_id='.$id)
            ->assertJsonPath('match.status', 'finished')->assertJsonPath('match.winner', 'player1');
    }

    public function test_game_api_rejects_bad_input(): void
    {
        $this->as(self::STUDENT);
        $this->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'poker', 'mode' => 'ai'])->assertStatus(400);
        $this->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'chess', 'mode' => 'ai', 'ai_difficulty' => 'godlike'])->assertStatus(400);
        $this->getJson('/student/games/api?action=state&match_id=99999999')->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Pensyarah
    // ------------------------------------------------------------------

    public function test_all_lecturer_pages_load(): void
    {
        $this->as(self::LECTURER);
        foreach (['/lecturer/dashboard', '/lecturer/reports', '/lecturer/profile', '/lecturer/report',
            '/manage/languages/1/chapters', '/manage/chapters/1/content', '/manage/exercise-template.csv'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_lecturer_profile_and_password(): void
    {
        DB::table('users')->where('id', self::LECTURER)->update(['password' => Hash::make('oldpass1')]);
        $this->as(self::LECTURER);
        $this->post('/lecturer/profile', ['update_profile' => 1, 'name' => 'Adam Test', 'email' => 'adam.test@example.test'])->assertSessionMissing('error');
        $this->post('/lecturer/profile', ['change_password' => 1, 'current_password' => 'oldpass1', 'new_password' => 'newpass1', 'confirm_password' => 'newpass1'])
            ->assertSessionMissing('error');
        $this->assertTrue(password_verify('newpass1', DB::table('users')->where('id', self::LECTURER)->value('password')));
    }

    public function test_lecturer_cannot_manage_another_lecturers_language(): void
    {
        // Bahasa 2 (Java) milik lecturer 2, bukan ADAM
        $this->as(self::LECTURER)->post('/manage/languages/2/chapters', [
            'add_chapter' => 1, 'chapter_number' => 99, 'title' => 'Bab Ceroboh', 'minimum_quiz_score' => 50,
        ]);
        $this->assertDatabaseMissing('chapters', ['language_id' => 2, 'title' => 'Bab Ceroboh'], 'mysql');
    }

    // ------------------------------------------------------------------
    // Urus kandungan (admin)
    // ------------------------------------------------------------------

    public function test_chapter_and_content_crud(): void
    {
        $this->as(self::ADMIN);

        $this->post('/manage/languages/9/chapters', ['add_chapter' => 1, 'chapter_number' => 50, 'title' => 'Bab Ujian', 'learning_objective' => 'x', 'minimum_quiz_score' => 60])
            ->assertSessionMissing('error');
        $chapterId = (int) DB::table('chapters')->where('language_id', 9)->where('title', 'Bab Ujian')->value('id');
        $this->assertGreaterThan(0, $chapterId);
        $this->get("/manage/chapters/$chapterId/content")->assertOk();

        $url = "/manage/chapters/$chapterId/content";
        $this->post($url, ['add_content' => 1, 'content_type' => 'notes', 'title' => 'Nota', 'content' => 'Isi nota'])->assertSessionHas('success');
        $this->post($url, ['add_video' => 1, 'title' => 'Video', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'])->assertSessionMissing('error');
        $this->post($url, ['add_code' => 1, 'title' => 'Kod', 'code_example' => 'SELECT 1;'])->assertSessionMissing('error');
        $this->post($url, ['add_exercise' => 1, 'ex_title' => 'Latihan', 'ex_question' => 'Soalan?', 'ex_difficulty' => 'Beginner', 'ex_points' => 10])->assertSessionMissing('error');
        $this->post($url, ['add_quiz' => 1, 'quiz_question' => '1+1?', 'quiz_type' => 'multiple_choice', 'option_a' => '2', 'option_b' => '3', 'correct_answer' => '2', 'quiz_points' => 5])
            ->assertSessionMissing('error');

        $this->assertSame(3, DB::table('learning_content')->where('chapter_id', $chapterId)->count());
        $this->assertSame(1, DB::table('exercises')->where('chapter_id', $chapterId)->count());
        $this->assertSame(1, DB::table('quizzes')->where('chapter_id', $chapterId)->count());
        $this->get($url)->assertOk();

        $this->post('/manage/languages/9/chapters', ['delete_chapter' => 1, 'id' => $chapterId]);
        $this->assertDatabaseMissing('chapters', ['id' => $chapterId], 'mysql');
    }

    public function test_bulk_exercise_csv_upload(): void
    {
        $csv = \Illuminate\Http\UploadedFile::fake()->createWithContent('ex.csv', "question,instruction,difficulty,sample_input,expected_output,hint,points\nCSV 1,Arahan,Beginner,,,,10\nCSV 2,Arahan,Advanced,,,,20\n");
        $this->as(self::ADMIN)->post('/manage/chapters/20/content', ['bulk_add_exercises' => 1, 'exercise_csv' => $csv])->assertSessionMissing('error');
        $this->assertSame(2, DB::table('exercises')->where('chapter_id', 20)->whereIn('question', ['CSV 1', 'CSV 2'])->count());
    }

    public function test_disallowed_note_file_type_is_rejected(): void
    {
        $bad = \Illuminate\Http\UploadedFile::fake()->create('virus.php', 1);
        $this->as(self::ADMIN)->post('/manage/chapters/20/content', ['add_content' => 1, 'content_type' => 'notes', 'title' => 'x', 'note_file' => $bad])
            ->assertSessionHas('error');
    }

    // ------------------------------------------------------------------
    // Admin
    // ------------------------------------------------------------------

    public function test_all_admin_pages_load(): void
    {
        $this->as(self::ADMIN);
        foreach (['/admin', '/admin/languages', '/admin/users', '/admin/users?role=student', '/admin/users?search=ali',
            '/admin/feedback', '/admin/analytics', '/admin/backfill-badges', '/manage/languages/1/chapters', '/manage/chapters/11/content'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_language_crud(): void
    {
        $this->as(self::ADMIN);
        $this->post('/admin/languages', ['add_language' => 1, 'name' => 'Ruby', 'slug' => 'ruby', 'difficulty' => 'Beginner', 'description' => 'x', 'lecturer_ids' => [self::LECTURER]])
            ->assertSessionHas('success');
        $id = (int) DB::table('languages')->where('slug', 'ruby')->value('id');
        $this->post('/admin/languages', ['add_language' => 1, 'name' => 'Ruby 2', 'slug' => 'ruby', 'difficulty' => 'Beginner'])->assertSessionHas('error');
        $this->post('/admin/languages', ['edit_language' => 1, 'id' => $id, 'name' => 'Ruby Lang', 'slug' => 'ruby', 'difficulty' => 'Intermediate'])->assertSessionHas('success');
        $this->assertSame('Ruby Lang', DB::table('languages')->where('id', $id)->value('name'));
        $this->post('/admin/languages', ['update_lecturers' => 1, 'id' => $id, 'lecturer_ids' => [self::LECTURER]])->assertSessionHas('success');
        $this->post('/admin/languages', ['delete_language' => 1, 'id' => $id])->assertSessionHas('success');
        $this->assertDatabaseMissing('languages', ['id' => $id], 'mysql');
    }

    public function test_admin_user_management(): void
    {
        $this->as(self::ADMIN);
        $this->post('/admin/users', ['add_user' => 1, 'name' => 'x', 'username' => 'x', 'password' => '1', 'role' => 'student'])->assertSessionHas('error');
        $this->post('/admin/users', ['add_user' => 1, 'name' => 'x', 'username' => 'x2', 'password' => 'abcdef', 'role' => 'hacker'])->assertSessionHas('error');
        $this->post('/admin/users', ['add_user' => 1, 'name' => 'Pelajar Ujian', 'username' => 'pelajar ujian', 'password' => 'abcdef', 'role' => 'student', 'email' => 'pu@example.test'])
            ->assertSessionHas('success');
        $id = (int) DB::table('users')->where('username', 'PELAJAR UJIAN')->value('id');
        $this->assertGreaterThan(0, $id);

        $this->post('/admin/users', ['add_user' => 1, 'name' => 'Dup', 'username' => 'pelajar ujian', 'password' => 'abcdef', 'role' => 'student'])->assertSessionHas('error');
        $this->post('/admin/users', ['toggle_status' => 1, 'id' => $id, 'new_status' => 'inactive'])->assertSessionHas('success');
        $this->post('/admin/users', ['delete_user' => 1, 'id' => self::ADMIN])->assertSessionHas('error');
        $this->post('/admin/users', ['delete_user' => 1, 'id' => $id])->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $id], 'mysql');
    }

    public function test_admin_feedback_update(): void
    {
        $reportId = DB::table('reports')->insertGetId(['user_id' => self::STUDENT, 'role' => 'student', 'category' => 'bug', 'title' => 't', 'description' => 'd', 'status' => 'new']);
        $this->as(self::ADMIN)->post('/admin/feedback', ['update_report' => 1, 'report_id' => $reportId, 'status' => 'resolved', 'admin_notes' => 'ok'])->assertSessionHas('success');
        $this->assertSame('resolved', DB::table('reports')->where('id', $reportId)->value('status'));
        $this->get('/admin/feedback?status=resolved')->assertOk();
    }

    // ------------------------------------------------------------------
    // Keselamatan
    // ------------------------------------------------------------------

    public function test_security_password_reset_should_not_work_with_only_username_and_email(): void
    {
        DB::table('users')->where('id', self::ADMIN)->update(['email' => 'admin@example.test', 'password' => Hash::make('original1')]);
        $this->post('/reset-password', ['username' => 'ADMIN ISEP', 'email' => 'admin@example.test', 'new_password' => 'hacked1', 'confirm_password' => 'hacked1'])
            ->assertStatus(405);
        $this->assertTrue(password_verify('original1', DB::table('users')->where('id', self::ADMIN)->value('password')), 'Sesiapa boleh ambil alih akaun admin melalui reset kata laluan');
    }

    public function test_admin_can_reset_a_users_password(): void
    {
        $this->as(self::ADMIN);
        $this->post('/admin/users', ['reset_password' => 1, 'id' => self::STUDENT, 'new_password' => '123'])->assertSessionHas('error');
        $this->post('/admin/users', ['reset_password' => 1, 'id' => self::STUDENT, 'new_password' => 'temp1234'])->assertSessionHas('success');
        $this->assertTrue(password_verify('temp1234', DB::table('users')->where('id', self::STUDENT)->value('password')));
        $this->post('/admin/users', ['toggle_status' => 1, 'id' => self::STUDENT, 'new_status' => 'superuser'])->assertSessionHas('error');
        $this->get('/admin/users')->assertOk()->assertSee('reset_password', false);
    }

    public function test_security_login_attempts_are_rate_limited(): void
    {
        DB::table('users')->where('id', self::STUDENT)->update(['password' => Hash::make('secret123')]);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'ALI AHMAD', 'password' => 'guess'.$i]);
        }
        // Cubaan ke-6, walaupun dengan kata laluan betul, disekat buat sementara
        $this->post('/login', ['username' => 'ALI AHMAD', 'password' => 'secret123'])
            ->assertSessionHas('error', fn ($e) => str_contains($e, 'Terlalu banyak cubaan'));
        $this->assertGuest();
    }

    public function test_successful_login_resets_the_attempt_counter(): void
    {
        DB::table('users')->where('id', self::STUDENT)->update(['password' => Hash::make('secret123')]);
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['username' => 'ALI AHMAD', 'password' => 'guess'.$i]);
        }
        $this->post('/login', ['username' => 'ALI AHMAD', 'password' => 'secret123'])->assertRedirect('/student/dashboard');
    }

    public function test_game_xp_is_given_once_a_day_for_each_difficulty(): void
    {
        $this->as(self::STUDENT);
        DB::table('game_matches')->where('player1_id', self::STUDENT)->where('mode', 'ai')->whereRaw('DATE(created_at) = CURDATE()')->delete();
        DB::table('users')->where('id', self::STUDENT)->update(['xp_booster_until' => null]);

        $win = function (string $game, string $diff) {
            $id = $this->postJson('/student/games/api', ['action' => 'create', 'game_type' => $game, 'mode' => 'ai', 'ai_difficulty' => $diff])->json('match.match_id');
            $before = $this->xp(self::STUDENT);
            $res = $this->postJson('/student/games/api', ['action' => 'finish', 'match_id' => $id, 'winner' => 'player1'])->assertOk();

            return [$this->xp(self::STUDENT) - $before, $res->json('xp_message')];
        };

        $this->assertSame(15, $win('chess', 'easy')[0]);
        [$again, $message] = $win('chess', 'easy');
        $this->assertSame(0, $again);
        $this->assertStringContainsString('sudah diperoleh hari ini', $message);
        $this->assertSame(30, $win('chess', 'medium')[0]);
        $this->assertSame(5, $win('tictactoe', 'easy')[0]);
        $this->assertSame(30, $win('connect4', 'hard')[0]);
        $this->assertSame(15, $win('snakes', 'medium')[0]);
        $this->get('/student/games/chess')->assertSee('XP hari ini');
    }

    public function test_new_board_games_start_with_the_right_board(): void
    {
        $this->as(self::STUDENT);
        $expected = ['tictactoe' => '.........', 'connect4' => str_repeat('0', 42)];
        foreach ($expected as $game => $board) {
            $this->postJson('/student/games/api', ['action' => 'create', 'game_type' => $game, 'mode' => 'pvp'])->assertJsonPath('match.board_state', $board);
            $this->get('/student/games/'.$game)->assertOk()->assertSee('game-engine.js', false);
        }
        $snakes = json_decode($this->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'snakes', 'mode' => 'ai', 'ai_difficulty' => 'easy'])->json('match.board_state'), true);
        $this->assertSame([0, 0], $snakes['pos']);
        $this->getJson('/student/games/api?action=question')->assertJsonStructure(['question' => ['text', 'options', 'answer']]);
        $this->postJson('/student/games/api', ['action' => 'create', 'game_type' => 'poker', 'mode' => 'pvp'])->assertStatus(400);
    }

    public function test_quick_code_quiz_is_scored_on_the_server_once_a_day(): void
    {
        $this->as(self::STUDENT);
        DB::table('game_matches')->where('player1_id', self::STUDENT)->where('game_type', 'quizrush')->delete();
        DB::table('users')->where('id', self::STUDENT)->update(['xp_booster_until' => null]);

        $start = $this->postJson('/student/games/api', ['action' => 'quiz_start'])->assertOk();
        $this->assertStringNotContainsString('correct_answer', $start->getContent());
        $questions = $start->json('questions');
        $answers = [];
        foreach (array_slice($questions, 0, 5) as $q) {
            $answers[$q['id']] = DB::table('quizzes')->where('id', $q['id'])->value('correct_answer');
        }
        $answers[$questions[5]['id']] = 'jawapan salah';

        $this->postJson('/student/games/api', ['action' => 'finish', 'match_id' => $start->json('match_id'), 'winner' => 'player1'])->assertStatus(400);

        $before = $this->xp(self::STUDENT);
        $this->postJson('/student/games/api', ['action' => 'quiz_finish', 'match_id' => $start->json('match_id'), 'answers' => json_encode($answers)])
            ->assertOk()->assertJsonPath('score', 5)->assertJsonPath('answered', 6);
        $this->assertSame($before + 10, $this->xp(self::STUDENT));

        $this->postJson('/student/games/api', ['action' => 'quiz_start'])->assertStatus(429);
        $this->getJson('/student/games/api?action=quiz_stats')->assertJsonPath('runs_left', 0);
    }

    public function test_security_chatbot_is_rate_limited(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'ok']]]]]])]);
        $this->as(self::STUDENT);
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/student/chatbot', ['message' => 'hi'])->assertJsonPath('reply', 'ok');
        }
        $this->postJson('/student/chatbot', ['message' => 'hi'])->assertStatus(429)->assertJsonStructure(['error']);
        Http::assertSentCount(10);
    }

    public function test_lecturer_can_manage_own_language_but_not_others(): void
    {
        $this->as(self::LECTURER);
        $this->get('/manage/languages/1/chapters')->assertOk();
        $this->get('/manage/languages/2/chapters')->assertForbidden();
        $javaChapter = DB::table('chapters')->where('language_id', 2)->value('id');
        $this->get("/manage/chapters/$javaChapter/content")->assertForbidden();
        $this->post("/manage/chapters/$javaChapter/content", ['add_content' => 1, 'content_type' => 'notes', 'title' => 'x', 'content' => 'x'])->assertForbidden();
        $this->post('/manage/languages/2/chapters', ['delete_chapter' => 1, 'id' => $javaChapter])->assertForbidden();
        $this->assertDatabaseHas('chapters', ['id' => $javaChapter], 'mysql');
    }

    public function test_delete_only_affects_items_in_the_current_chapter(): void
    {
        // Padam guna id bab bahasa lain melalui URL bahasa sendiri tidak sepatutnya berkesan
        $javaChapter = DB::table('chapters')->where('language_id', 2)->value('id');
        $this->as(self::LECTURER)->post('/manage/languages/1/chapters', ['delete_chapter' => 1, 'id' => $javaChapter]);
        $this->assertDatabaseHas('chapters', ['id' => $javaChapter], 'mysql');

        $javaQuiz = DB::table('quizzes')->where('chapter_id', $javaChapter)->value('id');
        if ($javaQuiz) {
            $this->as(self::LECTURER)->post('/manage/chapters/1/content', ['delete_quiz' => 1, 'id' => $javaQuiz]);
            $this->assertDatabaseHas('quizzes', ['id' => $javaQuiz], 'mysql');
        }
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        DB::table('users')->where('id', self::STUDENT)->update(['password' => Hash::make('secret123'), 'status' => 'inactive']);
        $this->post('/login', ['username' => 'ALI AHMAD', 'password' => 'secret123']);
        $this->assertGuest();
    }
}
