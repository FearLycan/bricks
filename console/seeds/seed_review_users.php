<?php
/**
 * One-off seed: 5 personality-driven reviewers + ~27 detailed reviews + scores + answers.
 *
 * Usage (host):
 *   docker exec bricks-frontend php /app/console/seeds/seed_review_users.php > /tmp/seed.sql
 *   docker exec -i bricks-mysql mysql -u yii2advanced -psecret yii2advanced < /tmp/seed.sql
 *
 * Idempotent: starts by deleting users with id BETWEEN 7 AND 11 (cascade clears their reviews).
 */

const HASH = '$2y$13$T0SWvqdwQNa63T8L2zk.Putdj4hSliBKlyE9JMGQYkec4SU5jG1H.'; // password = test1234

const ALLOWED_ANSWERS = [
    'design_theme_fit'   => ['poor', 'average', 'good', 'excellent'],
    'design_colors'      => ['poor', 'average', 'attractive'],
    'build_instructions' => ['confusing', 'okay', 'clear'],
    'build_complexity'   => ['too_simple', 'just_right', 'too_complex'],
    'build_techniques'   => ['no', 'yes'],
    'play_functions'     => ['none', 'some', 'great'],
    'play_purpose'       => ['play', 'display', 'both'],
    'play_minifigs'      => ['na', 'poor', 'average', 'great'],
    'quality_fit'        => ['poor', 'average', 'good'],
    'quality_stickers'   => ['na', 'poor', 'good'],
    'quality_unique'     => ['no', 'yes'],
    'value_worth'        => ['no', 'average', 'yes'],
    'value_pieces'       => ['too_few', 'just_right', 'lots'],
    'value_feel'         => ['budget', 'standard', 'premium'],
    'would_buy_again'    => ['no', 'maybe', 'yes'],
];

$users = [
    7 => [
        'username'    => 'TomekDisplay',
        'email'       => 'tomek@test.local',
        'personality' => 'Display collector — high design, low playability.',
        'dim_ranges'  => [
            'design'           => [8.5, 9.5],
            'build_experience' => [7.0, 9.0],
            'playability'      => [3.5, 6.0],
            'quality'          => [8.0, 9.0],
            'value'            => [6.0, 7.5],
            'recommendation'   => [7.5, 9.0],
        ],
        'preferences' => ['set_purpose' => ['display', 'collection'], 'priority' => ['looks', 'pieces']],
        'answer_bias' => [
            'design_theme_fit'   => 'excellent',
            'design_colors'      => 'attractive',
            'build_instructions' => 'clear',
            'build_complexity'   => 'just_right',
            'build_techniques'   => 'yes',
            'play_functions'     => 'some',
            'play_purpose'       => 'display',
            'play_minifigs'      => 'average',
            'quality_fit'        => 'good',
            'quality_stickers'   => 'good',
            'quality_unique'     => 'yes',
            'value_worth'        => 'average',
            'value_pieces'       => 'just_right',
            'value_feel'         => 'premium',
            'would_buy_again'    => 'yes',
        ],
        'text_bias' => [
            'liked_most'    => 'Looks gorgeous on the shelf — every detail rewards a second look.',
            'disliked_most' => 'Not much to actually do with it once it is built.',
        ],
        'title' => 'Looks stunning on display',
    ],
    8 => [
        'username'    => 'KasiaMama',
        'email'       => 'kasia@test.local',
        'personality' => 'Parent who plays with kids — playability and value first.',
        'dim_ranges'  => [
            'design'           => [6.5, 8.0],
            'build_experience' => [6.0, 8.0],
            'playability'      => [8.0, 9.5],
            'quality'          => [7.0, 8.0],
            'value'            => [7.5, 9.0],
            'recommendation'   => [7.5, 9.0],
        ],
        'preferences' => ['set_purpose' => ['play'], 'priority' => ['playability', 'price']],
        'answer_bias' => [
            'design_theme_fit'   => 'good',
            'design_colors'      => 'attractive',
            'build_instructions' => 'clear',
            'build_complexity'   => 'just_right',
            'build_techniques'   => 'no',
            'play_functions'     => 'great',
            'play_purpose'       => 'play',
            'play_minifigs'      => 'great',
            'quality_fit'        => 'good',
            'quality_stickers'   => 'good',
            'quality_unique'     => 'no',
            'value_worth'        => 'yes',
            'value_pieces'       => 'just_right',
            'value_feel'         => 'standard',
            'would_buy_again'    => 'yes',
        ],
        'text_bias' => [
            'liked_most'    => 'The kids play with it every day — that is the real test.',
            'disliked_most' => 'Stickers are a nightmare with little hands around.',
        ],
        'title' => 'A win with the kids',
    ],
    9 => [
        'username'    => 'AdamLicense',
        'email'       => 'adam@test.local',
        'personality' => 'License/minifig fan — strict on value, soft on licensed sets.',
        'dim_ranges'  => [
            'design'           => [7.5, 9.0],
            'build_experience' => [6.5, 8.0],
            'playability'      => [6.0, 8.0],
            'quality'          => [7.5, 9.0],
            'value'            => [5.0, 6.5],
            'recommendation'   => [7.0, 8.5],
        ],
        'preferences' => ['set_purpose' => ['minifigs', 'collection'], 'priority' => ['license', 'looks']],
        'answer_bias' => [
            'design_theme_fit'   => 'excellent',
            'design_colors'      => 'attractive',
            'build_instructions' => 'okay',
            'build_complexity'   => 'just_right',
            'build_techniques'   => 'yes',
            'play_functions'     => 'some',
            'play_purpose'       => 'display',
            'play_minifigs'      => 'great',
            'quality_fit'        => 'good',
            'quality_stickers'   => 'poor',
            'quality_unique'     => 'yes',
            'value_worth'        => 'no',
            'value_pieces'       => 'too_few',
            'value_feel'         => 'premium',
            'would_buy_again'    => 'maybe',
        ],
        'text_bias' => [
            'liked_most'    => 'The minifigures are excellent — true to the source material.',
            'disliked_most' => 'Way overpriced for what is in the box.',
        ],
        'title' => 'Great licensing, painful price',
    ],
    10 => [
        'username'    => 'MagdaDesign',
        'email'       => 'magda@test.local',
        'personality' => 'Architect/designer eye — high standards on design and quality.',
        'dim_ranges'  => [
            'design'           => [9.0, 10.0],
            'build_experience' => [8.0, 9.0],
            'playability'      => [5.0, 7.0],
            'quality'          => [8.5, 9.75],
            'value'            => [7.0, 8.5],
            'recommendation'   => [8.0, 9.5],
        ],
        'preferences' => ['set_purpose' => ['display'], 'priority' => ['pieces', 'looks']],
        'answer_bias' => [
            'design_theme_fit'   => 'excellent',
            'design_colors'      => 'attractive',
            'build_instructions' => 'clear',
            'build_complexity'   => 'just_right',
            'build_techniques'   => 'yes',
            'play_functions'     => 'some',
            'play_purpose'       => 'display',
            'play_minifigs'      => 'average',
            'quality_fit'        => 'good',
            'quality_stickers'   => 'good',
            'quality_unique'     => 'yes',
            'value_worth'        => 'yes',
            'value_pieces'       => 'just_right',
            'value_feel'         => 'premium',
            'would_buy_again'    => 'yes',
        ],
        'text_bias' => [
            'liked_most'    => 'Proportions and color choices are spot on — feels designed, not assembled.',
            'disliked_most' => 'A few build techniques felt forced — cleaner solutions exist.',
        ],
        'title' => 'Designer-grade execution',
    ],
    11 => [
        'username'    => 'PiotrMixed',
        'email'       => 'piotr@test.local',
        'personality' => 'Everyman — middling scores, price-sensitive.',
        'dim_ranges'  => [
            'design'           => [7.0, 8.0],
            'build_experience' => [7.0, 8.0],
            'playability'      => [7.0, 8.5],
            'quality'          => [7.0, 8.0],
            'value'            => [7.5, 8.5],
            'recommendation'   => [7.0, 8.0],
        ],
        'preferences' => ['set_purpose' => ['play', 'display'], 'priority' => ['price']],
        'answer_bias' => [
            'design_theme_fit'   => 'good',
            'design_colors'      => 'attractive',
            'build_instructions' => 'clear',
            'build_complexity'   => 'just_right',
            'build_techniques'   => 'no',
            'play_functions'     => 'some',
            'play_purpose'       => 'both',
            'play_minifigs'      => 'average',
            'quality_fit'        => 'good',
            'quality_stickers'   => 'good',
            'quality_unique'     => 'no',
            'value_worth'        => 'yes',
            'value_pieces'       => 'just_right',
            'value_feel'         => 'standard',
            'would_buy_again'    => 'yes',
        ],
        'text_bias' => [
            'liked_most'    => 'Solid all-rounder for the price.',
            'disliked_most' => 'Nothing really stands out either way.',
        ],
        'title' => 'Solid, no surprises',
    ],
];

/**
 * Set IDs verified against the current DB.
 *   909  = guardian-dragon-71847 (target: needs >=3 reviews for the match score)
 *   28   = lord-of-the-rings-minas-tirith-11377
 *   23   = shopping-street-11371
 *   358  = iron-man-mark-3-collectors-edition-76344
 *   8    = fire-truck-with-hose-and-firefighter-10473
 *   281  = lloyds-titan-mech-71860
 *   24   = autumn-cottage-garden-11372
 *   3339 = lion-knights-castle-10305
 */
$assignments = [
    909  => [7, 8, 9, 10, 11],
    28   => [7, 9, 10],
    23   => [7, 8, 10],
    358  => [7, 9, 10],
    8    => [8, 11],
    281  => [8, 9, 11],
    24   => [7, 8, 10, 11],
    3339 => [7, 9, 10, 11],
];

function pickScore(int $userId, int $setId, string $dim, array $range): float
{
    [$min, $max] = $range;
    $seed = crc32("$userId-$setId-$dim") % 1000;
    $frac = $seed / 999.0;
    $val = $min + ($max - $min) * $frac;
    return round($val * 4) / 4;
}

function chance(int $userId, int $setId, string $key, int $pct): bool
{
    return (crc32("$userId-$setId-$key") % 100) < $pct;
}

function esc(?string $v): string
{
    if ($v === null) {
        return 'NULL';
    }
    return "'" . str_replace("'", "''", $v) . "'";
}

$reviewId = 1000;
$sql = [];
$sql[] = "-- Generated by console/seeds/seed_review_users.php at " . date('c');
$sql[] = "-- Idempotent: deletes user IDs 7..11 first (CASCADE removes their reviews/scores/answers).";
$sql[] = "";
$sql[] = "START TRANSACTION;";
$sql[] = "";
$sql[] = "DELETE FROM `user` WHERE id BETWEEN 7 AND 11;";
$sql[] = "";

// --- USERS ---
$sql[] = "INSERT INTO `user` (id, username, auth_key, password_hash, email, role, status, created_at, updated_at) VALUES";
$userRows = [];
foreach ($users as $uid => $u) {
    $authKey = substr('seed_' . str_pad((string)$uid, 3, '0', STR_PAD_LEFT) . '_authkey_xxxxxxxxxxxxxxxxxx', 0, 32);
    $userRows[] = sprintf(
        "(%d, %s, %s, %s, %s, 'user', 10, NOW(), NOW())",
        $uid,
        esc($u['username']),
        esc($authKey),
        esc(HASH),
        esc($u['email'])
    );
}
$sql[] = implode(",\n", $userRows) . ";";
$sql[] = "";

// --- REVIEWS, SCORES, ANSWERS ---
$reviewInserts = [];
$scoreInserts = [];
$answerInserts = [];
$setsToRefresh = [];

foreach ($assignments as $setId => $userIds) {
    foreach ($userIds as $userId) {
        $u = $users[$userId];
        $setsToRefresh[$setId] = true;

        $dimScores = [];
        foreach ($u['dim_ranges'] as $dim => $range) {
            $dimScores[$dim] = pickScore($userId, $setId, $dim, $range);
        }
        $overall = round(array_sum($dimScores) / count($dimScores) * 4) / 4;

        $publishedOffsetDays = 30 - (crc32("$userId-$setId") % 28);

        $reviewInserts[] = sprintf(
            "(%d, %d, %d, 'detailed', %.2f, %s, %s, 1, DATE_SUB(NOW(), INTERVAL %d DAY), DATE_SUB(NOW(), INTERVAL %d DAY))",
            $reviewId,
            $userId,
            $setId,
            $overall,
            esc($u['title']),
            esc('Reviewer profile: ' . $u['personality']),
            $publishedOffsetDays + 1,
            $publishedOffsetDays
        );

        foreach ($dimScores as $dim => $score) {
            $scoreInserts[] = sprintf("(%d, %s, %.2f)", $reviewId, esc($dim), $score);
        }

        // Radio answers: 80% bias, 20% deterministic alternate.
        foreach ($u['answer_bias'] as $qKey => $biasedAnswer) {
            $useBias = chance($userId, $setId, $qKey, 80);
            if ($useBias) {
                $value = $biasedAnswer;
            } else {
                $allowed = ALLOWED_ANSWERS[$qKey] ?? null;
                if ($allowed === null || count($allowed) <= 1) {
                    $value = $biasedAnswer;
                } else {
                    $seed = crc32("$userId-$setId-$qKey-alt");
                    $value = $allowed[$seed % count($allowed)];
                }
            }
            $answerInserts[] = sprintf("(%d, %s, %s, NULL)", $reviewId, esc($qKey), esc($value));
        }

        if (chance($userId, $setId, 'liked_most_present', 70)) {
            $answerInserts[] = sprintf("(%d, 'liked_most', NULL, %s)", $reviewId, esc($u['text_bias']['liked_most']));
        }
        if (chance($userId, $setId, 'disliked_most_present', 60)) {
            $answerInserts[] = sprintf("(%d, 'disliked_most', NULL, %s)", $reviewId, esc($u['text_bias']['disliked_most']));
        }

        foreach ($u['preferences'] as $qKey => $values) {
            foreach ($values as $value) {
                $answerInserts[] = sprintf("(%d, %s, %s, NULL)", $reviewId, esc($qKey), esc($value));
            }
        }

        $reviewId++;
    }
}

$sql[] = "INSERT INTO set_review (id, user_id, set_id, review_type, overall_score, title, content, status, created_at, published_at) VALUES";
$sql[] = implode(",\n", $reviewInserts) . ";";
$sql[] = "";
$sql[] = "INSERT INTO set_review_score (set_review_id, dimension_key, score) VALUES";
$sql[] = implode(",\n", $scoreInserts) . ";";
$sql[] = "";
$sql[] = "INSERT INTO set_review_answer (set_review_id, question_key, answer_value, answer_text) VALUES";
$sql[] = implode(",\n", $answerInserts) . ";";
$sql[] = "";

$ids = implode(',', array_map('intval', array_keys($setsToRefresh)));
$sql[] = "UPDATE `set` s SET rating = (";
$sql[] = "    SELECT ROUND(AVG(overall_score), 2) FROM set_review sr WHERE sr.set_id = s.id AND sr.status = 1";
$sql[] = ") WHERE s.id IN ($ids);";
$sql[] = "";

$sql[] = "COMMIT;";

echo implode("\n", $sql) . "\n";
