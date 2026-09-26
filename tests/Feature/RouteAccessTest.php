<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Ujian laluan asas yang tidak memerlukan pangkalan data isep_db.
 */
class RouteAccessTest extends TestCase
{
    public function test_login_page_is_shown_in_malay_by_default(): void
    {
        $this->get('/login')->assertOk()->assertSee('Log Masuk')->assertSee('Lupa Kata Laluan?');
    }

    public function test_login_page_follows_english_language_cookie(): void
    {
        $this->withUnencryptedCookie('isep_lang', 'en')
            ->get('/login')->assertOk()->assertSee('Forgot Password?');
    }

    public function test_guests_are_redirected_to_login_from_protected_pages(): void
    {
        foreach (['/student/dashboard', '/admin', '/lecturer/dashboard', '/manage/chapters/1/content'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_unknown_page_shows_custom_404(): void
    {
        $this->get('/halaman-tiada')->assertNotFound()->assertSee('Alamak, halaman ini tak jumpa!');
    }

    public function test_reset_password_page_is_available(): void
    {
        $this->get('/reset-password')->assertOk()->assertSee('Emel Berdaftar');
    }
}
