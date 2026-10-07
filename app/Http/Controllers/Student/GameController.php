<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LearningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Permainan Chess & Dam Haji.
 * Mod AI: dimainkan sepenuhnya di pelayar, hanya "finish" dipanggil (untuk XP).
 * Mod PvP: keadaan papan disimpan di sini supaya kedua-dua pelayar boleh poll/sync.
 * Menggantikan student/games.php, chess.php, dam.php dan game_actions.php.
 */
class GameController extends Controller
{
    private const BOARD_GAMES = ['chess', 'dam', 'tictactoe', 'connect4', 'snakes'];

    private const GAME_XP = [
        'chess' => ['easy' => 15, 'medium' => 30, 'hard' => 50],
        'dam' => ['easy' => 15, 'medium' => 30, 'hard' => 50],
        'tictactoe' => ['easy' => 5, 'medium' => 10, 'hard' => 15],
        'connect4' => ['easy' => 10, 'medium' => 20, 'hard' => 30],
        'snakes' => ['easy' => 10, 'medium' => 15, 'hard' => 20],
    ];

    private const GAME_LABELS = ['chess' => 'Chess', 'dam' => 'Dam Haji', 'tictactoe' => 'Tic-Tac-Toe', 'connect4' => 'Connect Four', 'snakes' => 'Ular & Tangga'];

    private const QUIZRUSH_SECONDS = 60;

    private const QUIZRUSH_QUESTIONS = 40;

    private const QUIZRUSH_XP_PER_CORRECT = 2;

    private const QUIZRUSH_MAX_XP = 30;

    private const QUIZRUSH_RUNS_PER_DAY = 1;

    // PvP: pemain dianggap sudah keluar jika pelayarnya tidak poll selama tempoh ini (saat).
    // Cukup panjang untuk tab latar belakang yang di-throttle oleh pelayar (sehingga ~1 minit).
    private const PVP_PRESENCE_TTL = 90;

    private const CHESS_START_FEN = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

    public function __construct(private LearningService $learning)
    {
    }

    public function index()
    {
        return view('student.games');
    }

    public function chess(Request $request)
    {
        return view('student.chess', ['student_id' => (int) $request->user()->id, 'xp_today' => $this->learning->gameXpDifficultiesToday((int) $request->user()->id, 'chess')]);
    }

    public function dam(Request $request)
    {
        return view('student.dam', ['student_id' => (int) $request->user()->id, 'xp_today' => $this->learning->gameXpDifficultiesToday((int) $request->user()->id, 'dam')]);
    }

    public function boardGame(Request $request, string $game)
    {
        abort_unless(in_array($game, ['tictactoe', 'connect4', 'snakes'], true), 404);

        return view('student.games.'.$game, [
            'xp_today' => $this->learning->gameXpDifficultiesToday((int) $request->user()->id, $game),
        ]);
    }

    public function quizrush()
    {
        return view('student.games.quizrush');
    }

    private function initialBoardState(string $gameType): string
    {
        return match ($gameType) {
            'chess' => self::CHESS_START_FEN,
            'dam' => $this->damInitialBoard(),
            'tictactoe' => '.........',
            'connect4' => str_repeat('0', 42),
            'snakes' => json_encode(['pos' => [0, 0], 'roll' => 0, 'event' => '']),
            default => '',
        };
    }

    private function quizQuestionPool(int $limit): array
    {
        return rows(DB::table('quizzes')->select('id', 'question', 'question_type', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer')
            ->whereIn('question_type', ['multiple_choice', 'true_false'])->inRandomOrder()->limit($limit));
    }

    private function quizOptions(array $q): array
    {
        if ($q['question_type'] === 'true_false') {
            return ['True', 'False'];
        }
        $opts = array_values(array_filter([$q['option_a'], $q['option_b'], $q['option_c'], $q['option_d']], fn ($o) => $o !== null && $o !== ''));
        shuffle($opts);

        return $opts;
    }

    private function quizBestScore(int $studentId): int
    {
        return (int) DB::table('game_matches')->where('player1_id', $studentId)->where('game_type', 'quizrush')->where('status', 'finished')
            ->max(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(board_state, '$.score')) AS UNSIGNED)"));
    }

    private function quizRunsToday(int $studentId): int
    {
        return DB::table('game_matches')->where('player1_id', $studentId)->where('game_type', 'quizrush')->whereRaw('DATE(created_at) = CURDATE()')->count();
    }

    private function respond(array $data, int $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    private function damInitialBoard(): string
    {
        $board = array_fill(0, 64, 0);
        for ($row = 0; $row < 8; $row++) {
            for ($col = 0; $col < 8; $col++) {
                if (($row + $col) % 2 === 1) {
                    $idx = $row * 8 + $col;
                    if ($row <= 2) {
                        $board[$idx] = 3;      // player2 (hitam) di atas
                    } elseif ($row >= 5) {
                        $board[$idx] = 1;      // player1 (putih) di bawah
                    }
                }
            }
        }

        return json_encode($board);
    }

    private function getMatch(int $id): ?array
    {
        return row(DB::table('game_matches')->where('id', $id));
    }

    private function touchPresence(int $matchId, int $studentId): void
    {
        Cache::put("game-presence:$matchId:$studentId", true, self::PVP_PRESENCE_TTL);
    }

    private function isPresent(int $matchId, ?int $studentId): bool
    {
        return $studentId !== null && Cache::has("game-presence:$matchId:$studentId");
    }

    /**
     * Jika lawan PvP sudah lama tidak aktif (tab ditutup / sambungan putus), pemain yang masih ada menang.
     */
    private function resolveAbandonedPvp(array $m, int $studentId): array
    {
        if ($m['mode'] !== 'pvp' || $m['status'] !== 'ongoing') {
            return $m;
        }
        $myRole = $this->roleOf($m, $studentId);
        $opponentId = $myRole === 'player1' ? (int) $m['player2_id'] : (int) $m['player1_id'];
        if ($myRole === null || $this->isPresent((int) $m['id'], $opponentId)) {
            return $m;
        }
        DB::table('game_matches')->where('id', $m['id'])->where('status', 'ongoing')->update(['status' => 'finished', 'winner' => $myRole]);

        return $this->getMatch((int) $m['id']);
    }

    private function roleOf(array $m, int $studentId): ?string
    {
        return $studentId == $m['player1_id'] ? 'player1' : ($studentId == $m['player2_id'] ? 'player2' : null);
    }

    private function matchView(array $m, int $viewerId): array
    {
        $p1 = DB::table('users')->where('id', (int) $m['player1_id'])->value('name');
        $p2 = $m['player2_id'] ? DB::table('users')->where('id', (int) $m['player2_id'])->value('name') : null;

        return [
            'match_id' => (int) $m['id'],
            'game_type' => $m['game_type'],
            'mode' => $m['mode'],
            'ai_difficulty' => $m['ai_difficulty'],
            'status' => $m['status'],
            'board_state' => $m['board_state'],
            'turn' => $m['turn'],
            'winner' => $m['winner'],
            'move_count' => (int) $m['move_count'],
            'player1_name' => $p1 ?? 'Pelajar',
            'player2_name' => $p2,
            'my_role' => $this->roleOf($m, $viewerId),
        ];
    }

    /**
     * API tunggal (GET: lobby, my_waiting, state | POST: create, join, cancel, move, resign, finish).
     */
    public function api(Request $request): JsonResponse
    {
        $studentId = (int) $request->user()->id;
        $action = $request->query('action') ?? $request->input('action', '');

        // ---------------- GET ----------------
        if ($action === 'lobby') {
            $gameType = $request->query('game_type', '');
            if (! in_array($gameType, self::BOARD_GAMES, true)) {
                return $this->respond(['error' => 'Jenis permainan tidak sah.'], 400);
            }
            $rows = [];
            foreach (rows(DB::table('game_matches as gm')->join('users as u', 'u.id', '=', 'gm.player1_id')
                ->select('gm.id', 'gm.player1_id', 'gm.created_at', 'u.name as creator_name')
                ->where('gm.game_type', $gameType)->where('gm.mode', 'pvp')->where('gm.status', 'waiting')
                ->where('gm.player1_id', '!=', $studentId)->orderByDesc('gm.created_at')->limit(50)) as $r) {
                if ($this->isPresent((int) $r['id'], (int) $r['player1_id'])) {
                    unset($r['player1_id']);
                    $rows[] = $r;
                } else {
                    DB::table('game_matches')->where('id', $r['id'])->where('status', 'waiting')->update(['status' => 'abandoned']);
                }
            }
            $rows = array_slice($rows, 0, 20);

            return $this->respond(['open_matches' => $rows]);
        }

        if ($action === 'my_waiting') {
            $row = row(DB::table('game_matches')->select('id', 'status')
                ->where('game_type', $request->query('game_type', ''))->where('mode', 'pvp')->where('player1_id', $studentId)
                ->whereIn('status', ['waiting', 'ongoing'])->orderByDesc('created_at')->limit(1));

            return $this->respond(['match' => $row]);
        }

        if ($action === 'question') {
            $q = $this->quizQuestionPool(1)[0] ?? null;
            if (! $q) {
                return $this->respond(['error' => 'Tiada soalan.'], 404);
            }

            return $this->respond(['question' => ['text' => $q['question'], 'options' => $this->quizOptions($q), 'answer' => $q['correct_answer']]]);
        }

        if ($action === 'quiz_stats') {
            return $this->respond([
                'best' => $this->quizBestScore($studentId),
                'runs_left' => max(0, self::QUIZRUSH_RUNS_PER_DAY - $this->quizRunsToday($studentId)),
            ]);
        }

        if ($action === 'state') {
            $m = $this->getMatch((int) $request->query('match_id', 0));
            if (! $m) {
                return $this->respond(['error' => 'Perlawanan tidak dijumpai.'], 404);
            }
            if ($studentId != $m['player1_id'] && $studentId != $m['player2_id']) {
                return $this->respond(['error' => 'Akses ditolak.'], 403);
            }
            $this->touchPresence((int) $m['id'], $studentId);
            $m = $this->resolveAbandonedPvp($m, $studentId);

            return $this->respond(['match' => $this->matchView($m, $studentId)]);
        }

        // ---------------- POST (dilindungi CSRF oleh Laravel) ----------------
        if ($request->isMethod('post')) {
            $matchId = (int) $request->input('match_id', 0);

            if ($action === 'create') {
                $gameType = $request->input('game_type', '');
                $mode = $request->input('mode', '');
                $aiDifficulty = $request->input('ai_difficulty');

                if (! in_array($gameType, self::BOARD_GAMES, true)) {
                    return $this->respond(['error' => 'Jenis permainan tidak sah.'], 400);
                }
                if (! in_array($mode, ['ai', 'pvp'], true)) {
                    return $this->respond(['error' => 'Mod permainan tidak sah.'], 400);
                }
                if ($mode === 'ai' && ! in_array($aiDifficulty, ['easy', 'medium', 'hard'], true)) {
                    return $this->respond(['error' => 'Tahap kesukaran tidak sah.'], 400);
                }
                if ($mode === 'pvp') {
                    $aiDifficulty = null;
                }

                $newId = DB::table('game_matches')->insertGetId([
                    'game_type' => $gameType,
                    'mode' => $mode,
                    'ai_difficulty' => $aiDifficulty,
                    'player1_id' => $studentId,
                    'status' => $mode === 'ai' ? 'ongoing' : 'waiting',
                    'board_state' => $this->initialBoardState($gameType),
                    'turn' => 'player1',
                    'last_move_at' => DB::raw('NOW()'),
                ]);
                $this->touchPresence($newId, $studentId);

                return $this->respond(['match' => $this->matchView($this->getMatch($newId), $studentId)]);
            }

            if ($action === 'quiz_start') {
                if ($this->quizRunsToday($studentId) >= self::QUIZRUSH_RUNS_PER_DAY) {
                    return $this->respond(['error' => t('Anda sudah bermain Kuiz Koding Pantas hari ini. Datang semula esok!', 'You have already played the Quick Code Quiz today. Come back tomorrow!')], 429);
                }
                $questions = $this->quizQuestionPool(self::QUIZRUSH_QUESTIONS);
                if (! $questions) {
                    return $this->respond(['error' => 'Tiada soalan kuiz.'], 404);
                }
                $newId = DB::table('game_matches')->insertGetId([
                    'game_type' => 'quizrush', 'mode' => 'ai', 'ai_difficulty' => null, 'player1_id' => $studentId, 'status' => 'ongoing',
                    'board_state' => json_encode(['q' => array_map(fn ($q) => (int) $q['id'], $questions), 'score' => 0]),
                    'turn' => 'player1', 'last_move_at' => DB::raw('NOW()'),
                ]);

                return $this->respond(['match_id' => $newId, 'seconds' => self::QUIZRUSH_SECONDS, 'questions' => array_map(fn ($q) => [
                    'id' => (int) $q['id'], 'text' => $q['question'], 'options' => $this->quizOptions($q),
                ], $questions)]);
            }

            if ($action === 'quiz_finish') {
                $answers = json_decode((string) $request->input('answers', '{}'), true);
                $answers = is_array($answers) ? $answers : [];
                $m = $this->getMatch($matchId);
                if (! $m || $m['game_type'] !== 'quizrush' || (int) $m['player1_id'] !== $studentId) {
                    return $this->respond(['error' => 'Perlawanan tidak dijumpai.'], 404);
                }
                if ($m['status'] !== 'ongoing') {
                    return $this->respond(['error' => 'Kuiz sudah tamat.'], 409);
                }

                $state = json_decode($m['board_state'], true);
                $ids = array_map('intval', $state['q'] ?? []);
                $elapsed = (int) DB::table('game_matches')->where('id', $matchId)->value(DB::raw('TIMESTAMPDIFF(SECOND, created_at, NOW())'));
                $byId = collect(rows(DB::table('quizzes')->select('id', 'question', 'correct_answer')->whereIn('id', $ids ?: [0])))->keyBy('id');

                $review = [];
                $score = 0;
                foreach ($answers as $qid => $given) {
                    $qid = (int) $qid;
                    if (! in_array($qid, $ids, true) || ! $byId->has($qid)) {
                        continue;
                    }
                    $ok = trim((string) $given) === trim($byId[$qid]['correct_answer']);
                    $score += $ok ? 1 : 0;
                    $review[] = ['question' => $byId[$qid]['question'], 'given' => (string) $given, 'correct' => $byId[$qid]['correct_answer'], 'ok' => $ok];
                }

                $state['score'] = $score;
                $state['answered'] = count($review);
                DB::table('game_matches')->where('id', $matchId)->update([
                    'status' => 'finished', 'winner' => 'player1', 'board_state' => json_encode($state), 'move_count' => count($review),
                ]);

                $xpMessage = null;
                $xp = min($score * self::QUIZRUSH_XP_PER_CORRECT, self::QUIZRUSH_MAX_XP);
                if ($elapsed > self::QUIZRUSH_SECONDS + 15) {
                    $xpMessage = t('(Masa tamat terlalu lama - tiada XP.)', '(Time ran out too long ago - no XP.)');
                } elseif ($xp > 0) {
                    $this->learning->addXp($studentId, $xp, "Kuiz Koding Pantas - $score betul");
                    DB::table('game_matches')->where('id', $matchId)->update(['xp_awarded' => 1]);
                    $xpMessage = "+$xp XP!";
                }

                return $this->respond(['score' => $score, 'answered' => count($review), 'best' => $this->quizBestScore($studentId), 'xp_message' => $xpMessage, 'review' => $review]);
            }

            if ($action === 'join') {
                $m = $this->getMatch($matchId);
                if (! $m) {
                    return $this->respond(['error' => 'Perlawanan tidak dijumpai.'], 404);
                }
                if ($m['mode'] !== 'pvp' || $m['status'] !== 'waiting' || $m['player2_id'] !== null) {
                    return $this->respond(['error' => 'Perlawanan ini tidak lagi tersedia.'], 409);
                }
                if ($m['player1_id'] == $studentId) {
                    return $this->respond(['error' => 'Anda tidak boleh menyertai perlawanan sendiri.'], 400);
                }

                $ok = DB::table('game_matches')->where('id', $matchId)->where('status', 'waiting')->whereNull('player2_id')
                    ->update(['player2_id' => $studentId, 'status' => 'ongoing', 'last_move_at' => DB::raw('NOW()')]) > 0;

                if (! $ok) {
                    return $this->respond(['error' => 'Perlawanan baru sahaja disertai pemain lain.'], 409);
                }
                $this->touchPresence($matchId, $studentId);

                return $this->respond(['match' => $this->matchView($this->getMatch($matchId), $studentId)]);
            }

            if ($action === 'cancel') {
                $m = $this->getMatch($matchId);
                if (! $m) {
                    return $this->respond(['error' => 'Perlawanan tidak dijumpai.'], 404);
                }
                if ($m['player1_id'] != $studentId || $m['status'] !== 'waiting') {
                    return $this->respond(['error' => 'Tidak boleh batalkan perlawanan ini.'], 400);
                }
                DB::table('game_matches')->where('id', $matchId)->update(['status' => 'abandoned']);

                return $this->respond(['success' => true]);
            }

            if ($action === 'move') {
                $boardState = (string) $request->input('board_state', '');
                $nextTurn = $request->input('next_turn', '');

                if (! in_array($nextTurn, ['player1', 'player2'], true) || $boardState === '') {
                    return $this->respond(['error' => 'Data langkah tidak sah.'], 400);
                }

                $m = $this->getMatch($matchId);
                if (! $m) {
                    return $this->respond(['error' => 'Perlawanan tidak dijumpai.'], 404);
                }
                if ($m['status'] !== 'ongoing') {
                    return $this->respond(['error' => 'Perlawanan sudah tamat.'], 409);
                }
                $myRole = $this->roleOf($m, $studentId);
                if ($myRole === null) {
                    return $this->respond(['error' => 'Akses ditolak.'], 403);
                }
                if ($myRole !== $m['turn']) {
                    return $this->respond(['error' => 'Bukan giliran anda.'], 409);
                }

                DB::table('game_matches')->where('id', $matchId)->update([
                    'board_state' => $boardState,
                    'turn' => $nextTurn,
                    'move_count' => DB::raw('move_count + 1'),
                    'last_move_at' => DB::raw('NOW()'),
                ]);
                $this->touchPresence($matchId, $studentId);

                return $this->respond(['match' => $this->matchView($this->getMatch($matchId), $studentId)]);
            }

            if ($action === 'resign') {
                $m = $this->getMatch($matchId);
                if (! $m) {
                    return $this->respond(['error' => 'Perlawanan tidak dijumpai.'], 404);
                }
                $myRole = $this->roleOf($m, $studentId);
                if ($myRole === null) {
                    return $this->respond(['error' => 'Akses ditolak.'], 403);
                }
                if ($m['status'] !== 'ongoing') {
                    return $this->respond(['error' => 'Perlawanan sudah tamat.'], 409);
                }

                DB::table('game_matches')->where('id', $matchId)->update([
                    'status' => 'finished',
                    'winner' => $myRole === 'player1' ? 'player2' : 'player1',
                ]);

                return $this->respond(['success' => true]);
            }

            if ($action === 'finish') {
                $winner = $request->input('winner', ''); // 'player1' | 'player2' | 'draw'
                if (! in_array($winner, ['player1', 'player2', 'draw'], true)) {
                    return $this->respond(['error' => 'Keputusan tidak sah.'], 400);
                }

                $m = $this->getMatch($matchId);
                if (! $m) {
                    return $this->respond(['error' => 'Perlawanan tidak dijumpai.'], 404);
                }
                if ($this->roleOf($m, $studentId) === null) {
                    return $this->respond(['error' => 'Akses ditolak.'], 403);
                }
                if ($m['status'] === 'finished') {
                    return $this->respond(['match' => $this->matchView($m, $studentId)]); // idempotent
                }
                if ($m['game_type'] === 'quizrush') {
                    return $this->respond(['error' => 'Tindakan tidak sah.'], 400);
                }

                DB::table('game_matches')->where('id', $matchId)->update(['status' => 'finished', 'winner' => $winner]);

                $xpMessage = null;
                $celebrationColors = null;
                // XP hanya untuk kemenangan melawan KOMPUTER. Tiada XP untuk PvP.
                if ($m['mode'] === 'ai' && $winner === 'player1') {
                    // Permainan vs komputer berjalan di pelayar, jadi pelayan tidak nampak langkahnya.
                    // Halang "menang" palsu: perlawanan mesti berlangsung cukup lama, dan XP dihadkan setiap hari.
                    $alreadyToday = in_array($m['ai_difficulty'] ?: 'easy', $this->learning->gameXpDifficultiesToday($studentId, $m['game_type']), true);
                    $eligible = ! $alreadyToday;

                    if (! $m['xp_awarded'] && $eligible) {
                        $difficulty = $m['ai_difficulty'] ?: 'easy';
                        $xp = self::GAME_XP[$m['game_type']][$difficulty] ?? 10;
                        $gameLabel = self::GAME_LABELS[$m['game_type']] ?? $m['game_type'];
                        $diffLabel = ['easy' => 'Mudah', 'medium' => 'Sederhana', 'hard' => 'Sukar'][$difficulty];
                        $this->learning->addXp($studentId, $xp, "$gameLabel vs Komputer ($diffLabel) - Menang");

                        DB::table('game_matches')->where('id', $matchId)->update(['xp_awarded' => 1]);
                        $xpMessage = "+$xp XP!";
                    } elseif (! $m['xp_awarded'] && $alreadyToday) {
                        $xpMessage = t('(XP untuk tahap ini sudah diperoleh hari ini - cuba tahap lain atau datang semula esok.)', '(XP for this level was already earned today - try another level or come back tomorrow.)');
                    }

                    $freshUser = row(DB::table('users')->where('id', $studentId));
                    $celebrationColors = $this->learning->getEquippedCosmetics($freshUser)['celebration_colors'];
                }

                return $this->respond([
                    'match' => $this->matchView($this->getMatch($matchId), $studentId),
                    'xp_message' => $xpMessage,
                    'celebration_colors' => $celebrationColors,
                ]);
            }
        }

        return $this->respond(['error' => 'Tindakan tidak dikenali.'], 400);
    }
}
