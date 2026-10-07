<?php

namespace App\Services;

use DateTime;
use Illuminate\Support\Facades\DB;

/**
 * iSEP - Logik teras pembelajaran (unlock bab, progress, XP, streak, kedai,
 * lencana, sijil). Port terus daripada includes/functions.php sistem asal.
 */
class LearningService
{
    /** Kategori kosmetik yang boleh "dipakai" (equipable). */
    public const EQUIPABLE_TYPES = ['border', 'title', 'theme', 'celebration', 'flame', 'name_effect'];

    public const EQUIP_COLUMN_MAP = [
        'border' => 'equipped_border_id',
        'title' => 'equipped_title_id',
        'theme' => 'equipped_theme_id',
        'celebration' => 'equipped_celebration_id',
        'flame' => 'equipped_flame_id',
        'name_effect' => 'equipped_name_effect_id',
    ];

    /**
     * Dapatkan (atau cipta) rekod progress pelajar bagi satu bab.
     * Bab 1 (chapter_number = 1) automatik "unlocked".
     */
    public function getOrCreateProgress(int $studentId, int $chapterId): array
    {
        $row = DB::table('student_progress')
            ->where('student_id', $studentId)->where('chapter_id', $chapterId)->first();

        if ($row) {
            return (array) $row;
        }

        $chapter = DB::table('chapters')->select('language_id', 'chapter_number')->where('id', $chapterId)->first();

        DB::table('student_progress')->insert([
            'student_id' => $studentId,
            'language_id' => $chapter->language_id,
            'chapter_id' => $chapterId,
            'unlock_status' => $chapter->chapter_number == 1 ? 'unlocked' : 'locked',
        ]);

        return $this->getOrCreateProgress($studentId, $chapterId);
    }

    /**
     * Kemaskini status unlocked/locked semua bab dalam satu bahasa berdasarkan
     * sama ada bab sebelumnya sudah "completed".
     */
    public function refreshUnlockStatus(int $studentId, int $languageId): void
    {
        $chapters = DB::table('chapters')->select('id', 'chapter_number')
            ->where('language_id', $languageId)->orderBy('chapter_number')->get();

        $previousCompleted = true; // bab pertama sentiasa unlocked

        foreach ($chapters as $chapter) {
            $progress = $this->getOrCreateProgress($studentId, $chapter->id);
            $newStatus = $previousCompleted ? 'unlocked' : 'locked';

            if ($progress['unlock_status'] !== $newStatus) {
                DB::table('student_progress')->where('id', $progress['id'])->update(['unlock_status' => $newStatus]);
            }

            $previousCompleted = ($progress['chapter_status'] === 'completed');
        }
    }

    /**
     * Kira semula peratus penyelesaian & status bab berdasarkan 5 kriteria:
     * notes, video, try_it, exercise, quiz (>= minimum_quiz_score).
     */
    public function recalculateChapterCompletion(int $studentId, int $chapterId): void
    {
        $progress = $this->getOrCreateProgress($studentId, $chapterId);
        $chapter = DB::table('chapters')->select('language_id', 'minimum_quiz_score')->where('id', $chapterId)->first();

        $criteria = [
            $progress['notes_status'] === 'completed',
            $progress['video_status'] === 'completed',
            $progress['try_it_status'] === 'completed',
            $progress['exercise_status'] === 'completed',
            $progress['quiz_status'] === 'passed' && $progress['quiz_best_score'] >= $chapter->minimum_quiz_score,
        ];

        $completedCount = count(array_filter($criteria));
        $percentage = (int) round(($completedCount / count($criteria)) * 100);
        $allDone = ($completedCount === count($criteria));

        $chapterStatus = 'not_started';
        if ($allDone) {
            $chapterStatus = 'completed';
        } elseif ($percentage > 0) {
            $chapterStatus = 'in_progress';
        }

        $wasCompleted = ($progress['chapter_status'] === 'completed');

        $update = [
            'completion_percentage' => $percentage,
            'chapter_status' => $chapterStatus,
            'last_accessed' => DB::raw('NOW()'),
        ];
        if ($allDone && ! $wasCompleted) {
            $update['completion_date'] = DB::raw('NOW()');
        }
        DB::table('student_progress')->where('id', $progress['id'])->update($update);

        // Bab baru sahaja selesai buat kali pertama -> beri XP & semak sijil
        if ($allDone && ! $wasCompleted) {
            $this->addXp($studentId, 50, 'Chapter Completed');
            $this->checkAndIssueCertificate($studentId, $chapter->language_id);
        }

        $this->refreshUnlockStatus($studentId, $chapter->language_id);
    }

    /**
     * Tambah XP kepada pelajar. Booster aktif menggandakan XP positif sahaja.
     */
    public function addXp(int $studentId, int $amount, string $reason = ''): void
    {
        if ($amount > 0) {
            $boost = DB::table('users')->select('xp_booster_multiplier', 'xp_booster_until')->where('id', $studentId)->first();

            if ($boost->xp_booster_until && strtotime($boost->xp_booster_until) > time()) {
                $amount = (int) round($amount * (float) $boost->xp_booster_multiplier);
                $reason .= ' (⚡x'.$boost->xp_booster_multiplier.')';
            }
        }

        DB::table('users')->where('id', $studentId)->increment('xp_points', $amount);
        DB::table('xp_log')->insert(['student_id' => $studentId, 'amount' => $amount, 'reason' => $reason]);

        if ($amount > 0) {
            $this->grantLevelRewards($studentId);
        }
    }

    public const XP_PER_LEVEL = 500;

    public function levelFromXp(int $totalXpEarned): int
    {
        return intdiv(max(0, $totalXpEarned), self::XP_PER_LEVEL) + 1;
    }

    public function xpForLevel(int $level): int
    {
        return max(0, $level - 1) * self::XP_PER_LEVEL;
    }

    public function gameXpDifficultiesToday(int $studentId, string $gameType): array
    {
        return DB::table('game_matches')->where('player1_id', $studentId)->where('game_type', $gameType)->where('mode', 'ai')
            ->where('xp_awarded', 1)->whereRaw('DATE(created_at) = CURDATE()')->distinct()->pluck('ai_difficulty')->all();
    }

    public function getLevelRewards(): array
    {
        return rows(DB::table('shop_items')->whereNotNull('unlock_level')->orderBy('unlock_level')->orderBy('sort_order')->orderBy('id'));
    }

    public function grantLevelRewards(int $studentId): array
    {
        $level = $this->levelFromXp($this->getTotalXpEarned($studentId));
        $new = rows(DB::table('shop_items as si')->select('si.id', 'si.name', 'si.unlock_level')
            ->whereNotNull('si.unlock_level')->where('si.unlock_level', '<=', $level)
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('student_purchases as sp')
                ->whereColumn('sp.item_id', 'si.id')->where('sp.student_id', $studentId))
            ->orderBy('si.unlock_level'));

        foreach ($new as $item) {
            DB::table('student_purchases')->insert(['student_id' => $studentId, 'item_id' => $item['id']]);
        }

        if ($new && session()->isStarted() && (int) auth()->id() === $studentId) {
            session()->put('new_level_rewards', array_merge(session('new_level_rewards', []), $new));
        }

        return $new;
    }

    /**
     * Jumlah XP yang PERNAH diperoleh (xp_log positif sahaja) - untuk Level & leaderboard.
     */
    public function getTotalXpEarned(int $studentId): int
    {
        return (int) DB::table('xp_log')->where('student_id', $studentId)->where('amount', '>', 0)->sum('amount');
    }

    /**
     * Kemaskini streak pembelajaran harian. Jika terlepas TEPAT 1 hari dan ada
     * Streak Freeze, streak diselamatkan secara automatik.
     */
    public function updateLearningStreak(int $studentId): void
    {
        $user = DB::table('users')->select('learning_streak', 'last_streak_date', 'streak_freezes')->where('id', $studentId)->first();

        $today = new DateTime('today');
        $last = $user->last_streak_date ? new DateTime($user->last_streak_date) : null;
        $freezeUsed = false;

        if ($last === null) {
            $newStreak = 1;
        } else {
            $diff = (int) $today->diff($last)->format('%a');
            if ($diff === 0) {
                return; // sudah kira untuk hari ini
            } elseif ($diff === 1) {
                $newStreak = $user->learning_streak + 1;
            } elseif ($diff === 2 && $user->streak_freezes > 0) {
                $newStreak = $user->learning_streak + 1;
                $freezeUsed = true;
            } else {
                $newStreak = 1; // streak terputus
            }
        }

        $todayStr = $today->format('Y-m-d');

        if ($freezeUsed) {
            DB::table('users')->where('id', $studentId)->update([
                'learning_streak' => $newStreak,
                'last_streak_date' => $todayStr,
                'streak_freezes' => DB::raw('streak_freezes - 1'),
            ]);
            DB::table('streak_freeze_log')->insert([
                'student_id' => $studentId,
                'used_for_date' => (clone $today)->modify('-1 day')->format('Y-m-d'),
            ]);
        } else {
            DB::table('users')->where('id', $studentId)->update([
                'learning_streak' => $newStreak,
                'last_streak_date' => $todayStr,
            ]);
        }

        $this->checkStreakBadges($studentId);
    }

    /**
     * Beli item kedai guna XP. Pulangkan ['success' => bool, 'message' => string].
     */
    public function purchaseShopItem(int $studentId, int $itemId): array
    {
        $item = DB::table('shop_items')->where('id', $itemId)->first();
        if (! $item) {
            return ['success' => false, 'message' => t('Item tidak dijumpai.', 'Item not found.')];
        }

        if ($item->unlock_level !== null) {
            return ['success' => false, 'message' => sprintf(
                t('%s ialah ganjaran Level %d dan tidak boleh dibeli. Ia diberi secara automatik apabila anda mencapai level itu.', '%s is a Level %d reward and cannot be bought. It is given automatically when you reach that level.'),
                $item->name, $item->unlock_level
            )];
        }

        $buyer = DB::table('users')->select('xp_points', 'xp_booster_until')->where('id', $studentId)->first();
        $xp = $buyer->xp_points;

        if ($item->item_type === 'booster' && $buyer->xp_booster_until && strtotime($buyer->xp_booster_until) > time()) {
            return ['success' => false, 'message' => sprintf(
                t('Booster anda masih aktif sehingga %s. Tunggu sehingga tamat sebelum membeli booster baharu.', 'Your booster is still active until %s. Wait until it ends before buying a new one.'),
                date('d M, h:i A', strtotime($buyer->xp_booster_until))
            )];
        }

        if ($xp < $item->cost_xp) {
            return ['success' => false, 'message' => t('XP tidak mencukupi untuk item ini.', 'Not enough XP for this item.')];
        }

        if (in_array($item->item_type, self::EQUIPABLE_TYPES)) {
            $owned = DB::table('student_purchases')->where('student_id', $studentId)->where('item_id', $itemId)->exists();
            if ($owned) {
                return ['success' => false, 'message' => t('Anda sudah memiliki item ini.', 'You already own this item.')];
            }
            DB::table('student_purchases')->insert(['student_id' => $studentId, 'item_id' => $itemId]);
        } elseif ($item->item_type === 'freeze') {
            // boleh beli berulang kali, tambah kuantiti
            DB::table('users')->where('id', $studentId)->increment('streak_freezes');
        } elseif ($item->item_type === 'booster') {
            // extra_data format: "multiplier|durasi_saat"
            [$multiplier, $duration] = explode('|', $item->extra_data);
            DB::update(
                'UPDATE users SET xp_booster_multiplier = ?, xp_booster_until = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE id = ?',
                [(float) $multiplier, (int) $duration, $studentId]
            );
        }

        $this->addXp($studentId, -$item->cost_xp, 'Beli: '.$item->name);

        return ['success' => true, 'message' => $item->name.t(' berjaya dibeli!', ' purchased successfully!')];
    }

    /**
     * Tetapkan/buang item kosmetik aktif (mesti sudah dimiliki).
     */
    public function equipCosmetic(int $studentId, string $itemType, ?int $itemId): array
    {
        if (! isset(self::EQUIP_COLUMN_MAP[$itemType])) {
            return ['success' => false, 'message' => t('Jenis item tidak sah.', 'Invalid item type.')];
        }
        $column = self::EQUIP_COLUMN_MAP[$itemType];

        if ($itemId === null) {
            DB::table('users')->where('id', $studentId)->update([$column => null]);

            return ['success' => true, 'message' => t('Item dibuang.', 'Item removed.')];
        }

        $owned = DB::table('student_purchases')->where('student_id', $studentId)->where('item_id', $itemId)->exists();
        if (! $owned) {
            return ['success' => false, 'message' => t('Anda belum memiliki item ini.', 'You do not own this item yet.')];
        }

        DB::table('users')->where('id', $studentId)->update([$column => $itemId]);

        return ['success' => true, 'message' => t('Item dipakai!', 'Item equipped!')];
    }

    /**
     * Senarai leaderboard ikut tempoh: 'weekly', 'monthly', atau 'overall'.
     * Setiap baris ialah array (id, name, period_xp, equipped_border_id, border_style, title_text, name_effect_class).
     */
    public function getLeaderboard(string $period = 'overall', int $limit = 50): array
    {
        if ($period === 'overall') {
            $rows = DB::select(
                "SELECT u.id, u.name,
                        COALESCE((SELECT SUM(amount) FROM xp_log WHERE student_id = u.id AND amount > 0), 0) as period_xp,
                        u.equipped_border_id, si.border_style,
                        st.extra_data as title_text, sn.extra_data as name_effect_class
                 FROM users u
                 LEFT JOIN shop_items si ON si.id = u.equipped_border_id
                 LEFT JOIN shop_items st ON st.id = u.equipped_title_id
                 LEFT JOIN shop_items sn ON sn.id = u.equipped_name_effect_id
                 WHERE u.role = 'student'
                 ORDER BY period_xp DESC
                 LIMIT ?",
                [$limit]
            );
        } else {
            $dateFrom = $period === 'weekly'
                ? (new DateTime('monday this week'))->format('Y-m-d')
                : (new DateTime('first day of this month'))->format('Y-m-d');

            $rows = DB::select(
                "SELECT u.id, u.name, COALESCE(SUM(xl.amount), 0) as period_xp, u.equipped_border_id, si.border_style,
                        st.extra_data as title_text, sn.extra_data as name_effect_class
                 FROM users u
                 LEFT JOIN xp_log xl ON xl.student_id = u.id AND xl.created_at >= ? AND xl.amount > 0
                 LEFT JOIN shop_items si ON si.id = u.equipped_border_id
                 LEFT JOIN shop_items st ON st.id = u.equipped_title_id
                 LEFT JOIN shop_items sn ON sn.id = u.equipped_name_effect_id
                 WHERE u.role = 'student'
                 GROUP BY u.id
                 ORDER BY period_xp DESC
                 LIMIT ?",
                [$dateFrom, $limit]
            );
        }

        return array_map(fn ($r) => (array) $r, $rows);
    }

    public function isEnrolled(int $studentId, int $languageId): bool
    {
        return DB::table('enrollments')->where('student_id', $studentId)->where('language_id', $languageId)->exists();
    }

    /**
     * Jika SEMUA bab aktif dalam satu bahasa sudah "completed", keluarkan sijil (sekali sahaja).
     */
    public function checkAndIssueCertificate(int $studentId, int $languageId): ?string
    {
        $totalChapters = DB::table('chapters')->where('language_id', $languageId)->where('status', 'active')->count();
        if ($totalChapters === 0) {
            return null;
        }

        $completedChapters = DB::table('student_progress as sp')
            ->join('chapters as c', 'c.id', '=', 'sp.chapter_id')
            ->where('sp.student_id', $studentId)->where('c.language_id', $languageId)
            ->where('sp.chapter_status', 'completed')->count();

        if ($completedChapters < $totalChapters) {
            return null;
        }

        $existing = DB::table('certificates')->where('student_id', $studentId)->where('language_id', $languageId)->value('certificate_code');
        if ($existing) {
            return $existing;
        }

        $code = 'ISEP-'.strtoupper(bin2hex(random_bytes(4))).'-'.$studentId.$languageId;
        DB::table('certificates')->insert([
            'student_id' => $studentId,
            'language_id' => $languageId,
            'certificate_code' => $code,
        ]);

        $this->addXp($studentId, 150, 'Course Completed - Certificate Earned');
        $this->checkLanguageBadge($studentId, $languageId);

        return $code;
    }

    /**
     * Anugerahkan lencana kepada pelajar (jika belum ada).
     */
    public function awardBadge(int $studentId, int $badgeId): bool
    {
        $exists = DB::table('student_badges')->where('student_id', $studentId)->where('badge_id', $badgeId)->exists();
        if ($exists) {
            return false;
        }
        DB::table('student_badges')->insert(['student_id' => $studentId, 'badge_id' => $badgeId]);

        return true;
    }

    /**
     * Lencana "language_complete" (padan nama bahasa) + lencana generik first_code.
     */
    public function checkLanguageBadge(int $studentId, int $languageId): void
    {
        $langName = DB::table('languages')->where('id', $languageId)->value('name') ?? '';
        if (! $langName) {
            return;
        }

        $badges = DB::table('badges')->where('criteria_type', 'language_complete')
            ->whereRaw("name LIKE CONCAT('%', ?, '%')", [$langName])->pluck('id');
        foreach ($badges as $id) {
            $this->awardBadge($studentId, $id);
        }

        foreach (DB::table('badges')->where('criteria_type', 'first_code')->pluck('id') as $id) {
            $this->awardBadge($studentId, $id);
        }
    }

    public function checkStreakBadges(int $studentId): void
    {
        $streak = DB::table('users')->where('id', $studentId)->value('learning_streak') ?? 0;
        $badges = DB::table('badges')->where('criteria_type', 'streak')->where('criteria_value', '<=', $streak)->pluck('id');
        foreach ($badges as $id) {
            $this->awardBadge($studentId, $id);
        }
    }

    public function checkFirstCodeBadge(int $studentId): void
    {
        $count = DB::table('exercise_submissions')->where('student_id', $studentId)->count();
        if ($count < 1) {
            return;
        }
        foreach (DB::table('badges')->where('criteria_type', 'first_code')->pluck('id') as $id) {
            $this->awardBadge($studentId, $id);
        }
    }

    public function checkQuizMasterBadge(int $studentId): void
    {
        $count = DB::table('quiz_results')->where('student_id', $studentId)->where('percentage', '>=', 90)->count();
        $badges = DB::table('badges')->where('criteria_type', 'quiz_master')->where('criteria_value', '<=', $count)->pluck('id');
        foreach ($badges as $id) {
            $this->awardBadge($studentId, $id);
        }
    }

    /**
     * Semua kosmetik yang dipakai oleh pelajar.
     * Pulangkan: border_style, title_text, theme_colors, celebration_colors, flame_emoji, name_effect_class.
     */
    public function getEquippedCosmetics($user): array
    {
        $user = (array) ($user instanceof \Illuminate\Database\Eloquent\Model ? $user->getAttributes() : $user);

        $cosmetics = [
            'border_style' => null, 'title_text' => null, 'theme_colors' => null,
            'celebration_colors' => null, 'flame_emoji' => null, 'name_effect_class' => null,
        ];

        $ids = array_filter([
            $user['equipped_border_id'] ?? null,
            $user['equipped_title_id'] ?? null,
            $user['equipped_theme_id'] ?? null,
            $user['equipped_celebration_id'] ?? null,
            $user['equipped_flame_id'] ?? null,
            $user['equipped_name_effect_id'] ?? null,
        ]);

        if (empty($ids)) {
            return $cosmetics;
        }

        $rows = DB::table('shop_items')->select('id', 'item_type', 'border_style', 'extra_data')->whereIn('id', array_values($ids))->get();

        foreach ($rows as $row) {
            switch ($row->item_type) {
                case 'border': $cosmetics['border_style'] = $row->border_style;
                    break;
                case 'title': $cosmetics['title_text'] = $row->extra_data;
                    break;
                case 'theme': $cosmetics['theme_colors'] = $row->extra_data;
                    break;
                case 'celebration': $cosmetics['celebration_colors'] = $row->extra_data;
                    break;
                case 'flame': $cosmetics['flame_emoji'] = $row->extra_data;
                    break;
                case 'name_effect': $cosmetics['name_effect_class'] = $row->extra_data;
                    break;
            }
        }

        return $cosmetics;
    }

    /**
     * Peratus keseluruhan progress pelajar untuk satu bahasa (purata semua bab).
     */
    public function getLanguageOverallProgress(int $studentId, int $languageId): int
    {
        $avg = DB::table('chapters as c')
            ->leftJoin('student_progress as sp', function ($j) use ($studentId) {
                $j->on('sp.chapter_id', '=', 'c.id')->where('sp.student_id', '=', $studentId);
            })
            ->where('c.language_id', $languageId)
            ->avg('sp.completion_percentage');

        return (int) round($avg ?? 0);
    }
}
