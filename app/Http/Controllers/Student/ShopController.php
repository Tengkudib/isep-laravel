<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LearningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kedai XP: beli & pakai kosmetik (student/shop.php).
 */
class ShopController extends Controller
{
    public function show(Request $request)
    {
        $studentId = (int) $request->user()->id;
        app(LearningService::class)->grantLevelRewards($studentId);
        $user = row(DB::table('users')->where('id', $studentId));

        $byType = [];
        foreach (rows(DB::table('shop_items')->orderBy('item_type')->orderBy('sort_order')) as $item) {
            $byType[$item['item_type']][] = $item;
        }

        $categories = [
            'booster' => ['label' => t('Penambah', 'Boosters'), 'icon' => 'fa-bolt'],
            'title' => ['label' => t('Gelaran', 'Titles'), 'icon' => 'fa-tag'],
            'theme' => ['label' => t('Tema', 'Theme'), 'icon' => 'fa-palette'],
            'celebration' => ['label' => t('Sambutan', 'Celebrations'), 'icon' => 'fa-wand-magic-sparkles'],
            'freeze' => ['label' => t('Beku Streak', 'Streak Freeze'), 'icon' => 'fa-icicles'],
            'flame' => ['label' => t('Api Streak', 'Streak Flames'), 'icon' => 'fa-fire'],
            'border' => ['label' => t('Bingkai Profil', 'Profile Frames'), 'icon' => 'fa-circle-user'],
            'name_effect' => ['label' => t('Kesan Nama', 'Name Effects'), 'icon' => 'fa-font'],
        ];

        $activeTab = $request->query('tab', 'booster');
        if (! isset($categories[$activeTab])) {
            $activeTab = 'booster';
        }

        return view('student.shop', [
            'user' => $user,
            'by_type' => $byType,
            'owned_ids' => DB::table('student_purchases')->where('student_id', $studentId)->pluck('item_id')->all(),
            'categories' => $categories,
            'active_tab' => $activeTab,
            'equipped_column_map' => LearningService::EQUIP_COLUMN_MAP,
            'booster_active' => $user['xp_booster_until'] && strtotime($user['xp_booster_until']) > time(),
            'message' => session('message'),
            'message_type' => session('message_type'),
        ]);
    }

    public function action(Request $request, LearningService $learning)
    {
        $studentId = (int) $request->user()->id;
        $result = null;

        if ($request->has('buy_item')) {
            $result = $learning->purchaseShopItem($studentId, (int) $request->input('item_id'));
        } elseif ($request->has('equip_item')) {
            $itemId = $request->input('item_id') === null || $request->input('item_id') === '' ? null : (int) $request->input('item_id');
            $result = $learning->equipCosmetic($studentId, (string) $request->input('item_type'), $itemId);
        }

        if (! $result) {
            return back();
        }

        return back()->with(['message' => $result['message'], 'message_type' => $result['success'] ? 'success' : 'danger']);
    }
}
