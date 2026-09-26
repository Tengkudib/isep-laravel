# iSEP (Laravel 13)

Versi Laravel 13 bagi iSEP (Improve Self Education Platform). Sistem ini menggantikan projek PHP biasa dalam folder `isep` dan menggunakan pangkalan data yang sama, `isep_db`, tanpa sebarang perubahan skema.

## Keperluan

| Perkara | Versi |
|---|---|
| PHP | 8.3 atau lebih baru (dipasang di `C:\php84`) |
| Composer | 2.x (dipasang di `C:\php84\composer.bat`) |
| MySQL / MariaDB | MySQL XAMPP sedia ada, pangkalan data `isep_db` |

PHP dalam XAMPP ialah 8.2, jadi ia tidak boleh menjalankan Laravel 13. Gunakan PHP 8.4 di `C:\php84`.

## Cara menjalankan

1. Hidupkan **MySQL** dalam XAMPP Control Panel.
2. Klik dua kali `jalankan.bat`, atau jalankan:

   ```
   C:\php84\php.exe artisan serve
   ```

3. Buka http://127.0.0.1:8000 dalam pelayar.

Akaun demo sama seperti sistem asal: `ADMIN ISEP / admin123` dan `ALI AHMAD / admin123`.

## Konfigurasi (`.env`)

- `DB_DATABASE=isep_db` menyambung ke pangkalan data sedia ada.
- `ANTHROPIC_API_KEY=` mengaktifkan chatbot iSEP Tutor. Biarkan kosong untuk nyahaktif.
- `APP_DEBUG=true` hanya untuk pembangunan. Tukar kepada `false` sebelum digunakan oleh pelajar.

Jangan jalankan `php artisan migrate`. Semua migrasi lalai Laravel telah dibuang supaya jadual sedia ada tidak disentuh.

## Struktur

| Sistem asal | Laravel |
|---|---|
| `includes/functions.php` | `app/Services/LearningService.php` |
| `includes/lang.php` (`t()`) | `app/Support/helpers.php` |
| `middleware/auth.php` | `app/Http/Middleware/EnsureRole.php` + `auth` Laravel |
| `middleware/csrf.php` | Perlindungan CSRF terbina dalam Laravel (`@csrf`) |
| `config/db.php`, `config/secrets.php` | `.env` |
| `student/*.php` | `app/Http/Controllers/Student/*` |
| `admin/*.php` | `app/Http/Controllers/Admin/*` |
| `lecturer/*.php` | `app/Http/Controllers/Lecturer/*` |
| `includes/report_page.php` | `app/Http/Controllers/ReportController.php` |
| HTML dalam setiap fail | `resources/views/**/*.blade.php` |
| `assets/`, `uploads/` | `public/assets/`, `public/uploads/` |

## Peta URL

| Asal | Baharu |
|---|---|
| `/isep/index.php` | `/` |
| `login.php`, `logout.php`, `reset_password.php` | `/login`, `/logout`, `/reset-password` |
| `student/dashboard.php` | `/student/dashboard` |
| `student/language.php?slug=python` | `/student/language/python` |
| `student/enroll.php?slug=python` | `/student/enroll/python` |
| `student/chapter.php?id=5` | `/student/chapter/5` |
| `student/certificate.php?code=X` | `/student/certificate/X` |
| `student/certificates.php`, `leaderboard.php`, `profile.php`, `shop.php`, `report.php` | `/student/certificates`, `/student/leaderboard`, `/student/profile`, `/student/shop`, `/student/report` |
| `student/games.php`, `chess.php`, `dam.php` | `/student/games`, `/student/games/chess`, `/student/games/dam` |
| `student/game_actions.php`, `chatbot_api.php` | `/student/games/api`, `/student/chatbot` |
| `admin/index.php` | `/admin` |
| `admin/languages.php`, `users.php`, `feedback.php`, `analytics.php` | `/admin/languages`, `/admin/users`, `/admin/feedback`, `/admin/analytics` |
| `admin/backfill_badges.php` | `/admin/backfill-badges` |
| `admin/chapters.php?language_id=1` | `/manage/languages/1/chapters` |
| `admin/chapter_content.php?id=5` | `/manage/chapters/5/content` |
| `admin/exercise_csv_template.php` | `/manage/exercise-template.csv` |
| `lecturer/dashboard.php`, `reports.php`, `report.php`, `profile.php` | `/lecturer/dashboard`, `/lecturer/reports`, `/lecturer/report`, `/lecturer/profile` |

## Ujian

```
C:\php84\php.exe artisan test
```
