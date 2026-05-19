<?php

namespace common\models;

use frontend\components\T;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Query;

/**
 * @property int                      $id
 * @property int                      $user_id
 * @property int                      $set_id
 * @property string                   $review_type
 * @property float                    $overall_score
 * @property string|null              $title
 * @property string|null              $content
 * @property int                      $status
 * @property string                   $created_at
 * @property string|null              $updated_at
 * @property string|null              $published_at
 *
 * @property User                     $user
 * @property Set                      $set
 * @property SetReviewScore[]         $scores
 * @property-read string[][]|string[] $answersMap
 * @property-read float[]             $scoresMap
 * @property SetReviewAnswer[]        $answers
 */
class SetReview extends ActiveRecord
{
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_DETAILED = 'detailed';

    public const STATUS_HIDDEN = 0;
    public const STATUS_PUBLISHED = 1;
    public const STATUS_DRAFT = 2;

    public const SCORE_MIN = 1.0;
    public const SCORE_MAX = 10.0;
    public const SCORE_STEP = 0.25;

    /**
     * Six rating dimensions used in the detailed review wizard.
     * Each maps to one slider in the wizard and one axis on the radar chart.
     */
    public const DIMENSIONS = [
        'design'           => ['emoji' => '🎨'],
        'build_experience' => ['emoji' => '🧱'],
        'playability'      => ['emoji' => '🤖'],
        'quality'          => ['emoji' => '🧩'],
        'value'            => ['emoji' => '💸'],
        'recommendation'   => ['emoji' => '🏆'],
    ];

    /**
     * Multiple-choice questions asked in the detailed wizard, grouped per dimension.
     * `type`: 'radio' | 'text'. `options` for radio = array of value keys.
     */
    public const QUESTIONS_BY_DIMENSION = [
        'design'           => [
            ['key' => 'design_theme_fit', 'type' => 'radio', 'options' => ['poor', 'average', 'good', 'excellent']],
            ['key' => 'design_colors', 'type' => 'radio', 'options' => ['poor', 'average', 'attractive']],
        ],
        'build_experience' => [
            ['key' => 'build_instructions', 'type' => 'radio', 'options' => ['confusing', 'okay', 'clear']],
            ['key' => 'build_complexity', 'type' => 'radio', 'options' => ['too_simple', 'just_right', 'too_complex']],
            ['key' => 'build_techniques', 'type' => 'radio', 'options' => ['no', 'yes']],
        ],
        'playability'      => [
            ['key' => 'play_functions', 'type' => 'radio', 'options' => ['none', 'some', 'great']],
            ['key' => 'play_purpose', 'type' => 'radio', 'options' => ['play', 'display', 'both']],
            ['key' => 'play_minifigs', 'type' => 'radio', 'options' => ['na', 'poor', 'average', 'great']],
        ],
        'quality'          => [
            ['key' => 'quality_fit', 'type' => 'radio', 'options' => ['poor', 'average', 'good']],
            ['key' => 'quality_stickers', 'type' => 'radio', 'options' => ['na', 'poor', 'good']],
            ['key' => 'quality_unique', 'type' => 'radio', 'options' => ['no', 'yes']],
        ],
        'value'            => [
            ['key' => 'value_worth', 'type' => 'radio', 'options' => ['no', 'average', 'yes']],
            ['key' => 'value_pieces', 'type' => 'radio', 'options' => ['too_few', 'just_right', 'lots']],
            ['key' => 'value_feel', 'type' => 'radio', 'options' => ['budget', 'standard', 'premium']],
        ],
        'recommendation'   => [
            ['key' => 'would_buy_again', 'type' => 'radio', 'options' => ['no', 'maybe', 'yes']],
            ['key' => 'liked_most', 'type' => 'text'],
            ['key' => 'disliked_most', 'type' => 'text'],
        ],
    ];

    /**
     * Bonus preference questions (multi-select) asked at the end of the detailed wizard.
     * Stored per-review as multiple rows in set_review_answer — this lets a user have
     * different preferences for different sets ("this one is for play, that one for display")
     * and lets us derive an aggregated global taste profile later.
     */
    public const PREFERENCE_QUESTIONS = [
        ['key' => 'set_purpose', 'multi' => true, 'options' => ['display', 'play', 'collection', 'technical', 'minifigs']],
        ['key' => 'priority',    'multi' => true, 'options' => ['pieces', 'playability', 'looks', 'price', 'license'], 'max_select' => 2],
    ];

    /**
     * Question keys that accept multiple values per review (stored as N rows in set_review_answer).
     */
    public static function isMultiAnswerQuestion(string $questionKey): bool
    {
        foreach (self::PREFERENCE_QUESTIONS as $q) {
            if ($q['key'] === $questionKey) {
                return !empty($q['multi']);
            }
        }
        return false;
    }

    public static function tableName(): string
    {
        return '{{%set_review}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'      => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value'      => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'set_id', 'overall_score'], 'required'],
            [['user_id', 'set_id', 'status'], 'integer'],
            [['overall_score'], 'number', 'min' => self::SCORE_MIN, 'max' => self::SCORE_MAX],
            [['content'], 'string'],
            [['review_type'], 'in', 'range' => [self::TYPE_SIMPLE, self::TYPE_DETAILED]],
            [['status'], 'in', 'range' => [self::STATUS_HIDDEN, self::STATUS_PUBLISHED, self::STATUS_DRAFT]],
            [['title'], 'string', 'max' => 255],
            [['created_at', 'updated_at', 'published_at'], 'safe'],
            [['user_id', 'set_id'], 'unique', 'targetAttribute' => ['user_id', 'set_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => Set::class, 'targetAttribute' => ['set_id' => 'id']],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getSet(): ActiveQuery
    {
        return $this->hasOne(Set::class, ['id' => 'set_id']);
    }

    public function getScores(): ActiveQuery
    {
        return $this->hasMany(SetReviewScore::class, ['set_review_id' => 'id']);
    }

    public function getAnswers(): ActiveQuery
    {
        return $this->hasMany(SetReviewAnswer::class, ['set_review_id' => 'id']);
    }

    public function isPublished(): bool
    {
        return (int)$this->status === self::STATUS_PUBLISHED;
    }

    public function isDetailed(): bool
    {
        return $this->review_type === self::TYPE_DETAILED;
    }

    /**
     * @return array<string, float> dimension_key => score
     */
    public function getScoresMap(): array
    {
        $map = [];
        foreach ($this->scores as $row) {
            $map[$row->dimension_key] = (float)$row->score;
        }
        return $map;
    }

    /**
     * @return array<string, string|array<int,string>> question_key => value(s)
     */
    public function getAnswersMap(): array
    {
        $map = [];
        foreach ($this->answers as $answer) {
            if ($answer->answer_text !== null && $answer->answer_text !== '') {
                $map[$answer->question_key] = $answer->answer_text;
                continue;
            }
            if (!isset($map[$answer->question_key])) {
                $map[$answer->question_key] = $answer->answer_value;
            } else {
                $existing = $map[$answer->question_key];
                $map[$answer->question_key] = is_array($existing)
                    ? array_merge($existing, [$answer->answer_value])
                    : [$existing, $answer->answer_value];
            }
        }
        return $map;
    }

    public static function findByUserAndSet(int $userId, int $setId): ?self
    {
        return self::findOne(['user_id' => $userId, 'set_id' => $setId]);
    }

    /**
     * Normalises an incoming score to the configured 1–10 range with 0.25 step.
     */
    public static function normalizeScore(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }
        $score = (float)$value;
        if ($score < self::SCORE_MIN) {
            $score = self::SCORE_MIN;
        }
        if ($score > self::SCORE_MAX) {
            $score = self::SCORE_MAX;
        }
        return round($score / self::SCORE_STEP) * self::SCORE_STEP;
    }

    /**
     * Aggregated stats used by the set page UI.
     *
     * @return array{
     *   review_count: int,
     *   average: float|null,
     *   distribution: array<int,int>,
     *   dimensions: array<string, array{avg: float, count: int}>,
     *   answers: array<string, array{total: int, counts: array<string,int>}>,
     *   text_answers: array<string, array<int, array{text: string, author: string, date: string|null}>>
     * }
     */
    public static function getSetStats(int $setId): array
    {
        $rows = self::find()
            ->select(['overall_score'])
            ->where(['set_id' => $setId, 'status' => self::STATUS_PUBLISHED])
            ->asArray()
            ->all();

        $count = count($rows);
        $average = null;
        $distribution = array_fill(1, 10, 0);

        if ($count > 0) {
            $sum = 0.0;
            foreach ($rows as $row) {
                $score = (float)$row['overall_score'];
                $sum += $score;
                // Histogram bucket = score rounded to the nearest whole number (1..10).
                // Example: 8.75 → 9, 8.50 → 9 (PHP rounds half up), 8.25 → 8. This makes the
                // "9" bar match what users see written as the overall score.
                $bucket = (int)max(1, min(10, (int)round($score)));
                $distribution[$bucket]++;
            }
            $average = round($sum / $count, 2);
        }

        $dimensionAverages = [];
        if ($count > 0) {
            $dimRows = (new Query())
                ->select([
                    'srs.dimension_key',
                    'avg_score' => new Expression('AVG(srs.score)'),
                    'cnt'       => new Expression('COUNT(*)'),
                ])
                ->from(['srs' => SetReviewScore::tableName()])
                ->innerJoin(['sr' => self::tableName()], 'sr.id = srs.set_review_id')
                ->where(['sr.set_id' => $setId, 'sr.status' => self::STATUS_PUBLISHED])
                ->groupBy(['srs.dimension_key'])
                ->all();

            foreach ($dimRows as $row) {
                $dimensionAverages[(string)$row['dimension_key']] = [
                    'avg'   => round((float)$row['avg_score'], 2),
                    'count' => (int)$row['cnt'],
                ];
            }
        }

        return [
            'review_count' => $count,
            'average'      => $average,
            'distribution' => $distribution,
            'dimensions'   => $dimensionAverages,
            'answers'      => $count > 0 ? self::getAnswerAggregates($setId) : [],
            'text_answers' => $count > 0 ? self::getTextAnswers($setId) : [],
        ];
    }

    /**
     * Rozkład odpowiedzi radio na każde pytanie wizardu w obrębie zestawu.
     *
     * For multi-answer questions (set_purpose, priority) `total` reflects the number of
     * distinct reviewers who answered, not the sum of counts — so percentages stay
     * interpretable as "% of reviewers who picked X".
     *
     * @return array<string, array{total: int, counts: array<string,int>}>
     */
    public static function getAnswerAggregates(int $setId): array
    {
        $rows = (new Query())
            ->select([
                'sra.question_key',
                'sra.answer_value',
                'cnt' => new Expression('COUNT(*)'),
            ])
            ->from(['sra' => SetReviewAnswer::tableName()])
            ->innerJoin(['sr' => self::tableName()], 'sr.id = sra.set_review_id')
            ->where(['sr.set_id' => $setId, 'sr.status' => self::STATUS_PUBLISHED])
            ->andWhere(['not', ['sra.answer_value' => null]])
            ->groupBy(['sra.question_key', 'sra.answer_value'])
            ->all();

        $distinctRows = (new Query())
            ->select([
                'sra.question_key',
                'reviewers' => new Expression('COUNT(DISTINCT sra.set_review_id)'),
            ])
            ->from(['sra' => SetReviewAnswer::tableName()])
            ->innerJoin(['sr' => self::tableName()], 'sr.id = sra.set_review_id')
            ->where(['sr.set_id' => $setId, 'sr.status' => self::STATUS_PUBLISHED])
            ->andWhere(['not', ['sra.answer_value' => null]])
            ->groupBy(['sra.question_key'])
            ->all();

        $distinctMap = [];
        foreach ($distinctRows as $row) {
            $distinctMap[(string)$row['question_key']] = (int)$row['reviewers'];
        }

        $aggregates = [];
        foreach ($rows as $row) {
            $qKey = (string)$row['question_key'];
            $aValue = (string)$row['answer_value'];
            $count = (int)$row['cnt'];

            if (!isset($aggregates[$qKey])) {
                $aggregates[$qKey] = [
                    'total'  => self::isMultiAnswerQuestion($qKey)
                        ? ($distinctMap[$qKey] ?? 0)
                        : 0,
                    'counts' => [],
                ];
            }
            $aggregates[$qKey]['counts'][$aValue] = $count;
            if (!self::isMultiAnswerQuestion($qKey)) {
                $aggregates[$qKey]['total'] += $count;
            }
        }

        return $aggregates;
    }

    /**
     * Najnowsze tekstowe odpowiedzi (liked_most / disliked_most) z opublikowanych recenzji.
     *
     * @return array<string, array<int, array{text: string, author: string, date: string|null}>>
     */
    public static function getTextAnswers(int $setId, int $limit = 5): array
    {
        $rows = (new Query())
            ->select([
                'sra.question_key',
                'sra.answer_text',
                'username' => 'u.username',
                'published_at' => 'sr.published_at',
                'created_at' => 'sr.created_at',
            ])
            ->from(['sra' => SetReviewAnswer::tableName()])
            ->innerJoin(['sr' => self::tableName()], 'sr.id = sra.set_review_id')
            ->leftJoin(['u' => User::tableName()], 'u.id = sr.user_id')
            ->where(['sr.set_id' => $setId, 'sr.status' => self::STATUS_PUBLISHED])
            ->andWhere(['not', ['sra.answer_text' => null]])
            ->andWhere(['<>', 'sra.answer_text', ''])
            ->orderBy(['sr.published_at' => SORT_DESC, 'sr.id' => SORT_DESC])
            ->all();

        $result = [];
        foreach ($rows as $row) {
            $qKey = (string)$row['question_key'];
            if (!isset($result[$qKey])) {
                $result[$qKey] = [];
            }
            if (count($result[$qKey]) >= $limit) {
                continue;
            }
            $result[$qKey][] = [
                'text'   => (string)$row['answer_text'],
                'author' => (string)($row['username'] ?? ''),
                'date'   => $row['published_at'] ?? $row['created_at'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Aggregated review profile for a single user, used by the profile widgets.
     *
     * Returns:
     *  - detailed_count: how many published detailed reviews the user has authored
     *  - preferences:    per-preference-question aggregate, percent of reviews
     *  - dimensions:     per-dimension average + count
     *  - dimension_diffs: user-vs-community delta per dimension (positive = stricter)
     *
     * @return array{
     *   detailed_count: int,
     *   total_count: int,
     *   preferences: array<string, array{total: int, counts: array<string, array{count: int, pct: int}>}>,
     *   dimensions: array<string, array{avg: float, count: int}>,
     *   dimension_diffs: array<string, float>
     * }
     */
    public static function getUserReviewProfile(int $userId): array
    {
        $counts = (new Query())
            ->select([
                'detailed' => new Expression('SUM(CASE WHEN sr.review_type = :td THEN 1 ELSE 0 END)', [':td' => self::TYPE_DETAILED]),
                'total'    => new Expression('COUNT(*)'),
            ])
            ->from(['sr' => self::tableName()])
            ->where(['sr.user_id' => $userId, 'sr.status' => self::STATUS_PUBLISHED])
            ->one();

        $detailedCount = (int)($counts['detailed'] ?? 0);
        $totalCount = (int)($counts['total'] ?? 0);

        // Preference aggregates: how often the user picks each set_purpose / priority value.
        // Total = number of distinct reviews that contributed an answer to that question.
        $prefRows = (new Query())
            ->select([
                'sra.question_key',
                'sra.answer_value',
                'cnt' => new Expression('COUNT(*)'),
            ])
            ->from(['sra' => SetReviewAnswer::tableName()])
            ->innerJoin(['sr' => self::tableName()], 'sr.id = sra.set_review_id')
            ->where(['sr.user_id' => $userId, 'sr.status' => self::STATUS_PUBLISHED])
            ->andWhere(['sra.question_key' => array_column(self::PREFERENCE_QUESTIONS, 'key')])
            ->andWhere(['not', ['sra.answer_value' => null]])
            ->groupBy(['sra.question_key', 'sra.answer_value'])
            ->all();

        $prefTotals = (new Query())
            ->select([
                'sra.question_key',
                'reviewers' => new Expression('COUNT(DISTINCT sra.set_review_id)'),
            ])
            ->from(['sra' => SetReviewAnswer::tableName()])
            ->innerJoin(['sr' => self::tableName()], 'sr.id = sra.set_review_id')
            ->where(['sr.user_id' => $userId, 'sr.status' => self::STATUS_PUBLISHED])
            ->andWhere(['sra.question_key' => array_column(self::PREFERENCE_QUESTIONS, 'key')])
            ->andWhere(['not', ['sra.answer_value' => null]])
            ->groupBy(['sra.question_key'])
            ->all();

        $totals = [];
        foreach ($prefTotals as $row) {
            $totals[(string)$row['question_key']] = (int)$row['reviewers'];
        }

        $preferences = [];
        foreach ($prefRows as $row) {
            $qKey = (string)$row['question_key'];
            $value = (string)$row['answer_value'];
            $count = (int)$row['cnt'];
            $total = $totals[$qKey] ?? 0;
            $pct = $total > 0 ? (int)round(($count / $total) * 100) : 0;

            if (!isset($preferences[$qKey])) {
                $preferences[$qKey] = ['total' => $total, 'counts' => []];
            }
            $preferences[$qKey]['counts'][$value] = ['count' => $count, 'pct' => $pct];
        }
        foreach ($preferences as $qKey => &$entry) {
            uasort($entry['counts'], static fn($a, $b) => $b['count'] <=> $a['count']);
        }
        unset($entry);

        $dimRows = (new Query())
            ->select([
                'srs.dimension_key',
                'avg_score' => new Expression('AVG(srs.score)'),
                'cnt'       => new Expression('COUNT(*)'),
            ])
            ->from(['srs' => SetReviewScore::tableName()])
            ->innerJoin(['sr' => self::tableName()], 'sr.id = srs.set_review_id')
            ->where(['sr.user_id' => $userId, 'sr.status' => self::STATUS_PUBLISHED])
            ->groupBy(['srs.dimension_key'])
            ->all();

        $dimensions = [];
        foreach ($dimRows as $row) {
            $dimensions[(string)$row['dimension_key']] = [
                'avg'   => round((float)$row['avg_score'], 2),
                'count' => (int)$row['cnt'],
            ];
        }

        $diffs = [];
        if ($dimensions !== []) {
            $community = self::getCommunityDimensionAverages();
            foreach ($dimensions as $dimKey => $entry) {
                if (isset($community[$dimKey])) {
                    $diffs[$dimKey] = round($entry['avg'] - $community[$dimKey], 2);
                }
            }
        }

        return [
            'detailed_count'  => $detailedCount,
            'total_count'     => $totalCount,
            'preferences'     => $preferences,
            'dimensions'      => $dimensions,
            'dimension_diffs' => $diffs,
        ];
    }

    /**
     * Community-wide average score per dimension across all published detailed reviews.
     * Cached per request via a static — these are the same numbers for every user.
     *
     * @return array<string, float> dimension_key => avg_score
     */
    public static function getCommunityDimensionAverages(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $rows = (new Query())
            ->select([
                'srs.dimension_key',
                'avg_score' => new Expression('AVG(srs.score)'),
            ])
            ->from(['srs' => SetReviewScore::tableName()])
            ->innerJoin(['sr' => self::tableName()], 'sr.id = srs.set_review_id')
            ->where(['sr.status' => self::STATUS_PUBLISHED])
            ->groupBy(['srs.dimension_key'])
            ->all();

        $cached = [];
        foreach ($rows as $row) {
            $cached[(string)$row['dimension_key']] = round((float)$row['avg_score'], 2);
        }
        return $cached;
    }

    /**
     * Computes how well a set matches a user's taste profile (0–100).
     *
     * Logic: for each preference question (set_purpose, priority), compute the
     * distribution of answers from the user's own reviews and from this set's
     * reviews. Score = 100 minus average L1 distance of the two distributions,
     * weighted equally across questions.
     *
     * Returns null when there isn't enough data to be meaningful (default
     * thresholds: user has < 3 detailed reviews OR set has < 3 reviews).
     */
    public static function getMatchScoreForSet(int $userId, int $setId, int $minUserReviews = 3, int $minSetReviews = 3): ?int
    {
        $userCount = (int)self::find()
            ->where(['user_id' => $userId, 'status' => self::STATUS_PUBLISHED, 'review_type' => self::TYPE_DETAILED])
            ->count();

        if ($userCount < $minUserReviews) {
            return null;
        }

        $setCount = (int)self::find()
            ->where(['set_id' => $setId, 'status' => self::STATUS_PUBLISHED])
            ->count();
        if ($setCount < $minSetReviews) {
            return null;
        }

        $userProfile = self::getUserReviewProfile($userId);
        $setStats = self::getAnswerAggregates($setId);

        $prefKeys = array_column(self::PREFERENCE_QUESTIONS, 'key');
        $similarities = [];
        foreach ($prefKeys as $qKey) {
            $userDist = self::distributionForQuestion($userProfile['preferences'][$qKey] ?? null);
            $setDist = self::distributionForQuestion($setStats[$qKey] ?? null);
            if ($userDist === [] || $setDist === []) {
                continue;
            }
            $similarities[] = self::distributionSimilarity($userDist, $setDist);
        }

        if ($similarities === []) {
            return null;
        }

        $avg = array_sum($similarities) / count($similarities);
        return (int)round($avg * 100);
    }

    /**
     * Sets recommended to a user via lightweight collaborative filtering.
     *
     * Finds reviewers who scored sets the user also reviewed, ranks them by
     * average score-similarity, then returns highly-rated sets those peers
     * reviewed that the user hasn't yet.
     *
     * @return array<int, array{set_id: int, score: float, peer_count: int}>
     */
    public static function getSimilarUsersRecommendations(int $userId, int $limit = 6): array
    {
        $userOverlap = (new Query())
            ->select(['sr.set_id', 'sr.overall_score'])
            ->from(['sr' => self::tableName()])
            ->where(['sr.user_id' => $userId, 'sr.status' => self::STATUS_PUBLISHED])
            ->indexBy('set_id')
            ->column();

        if (count($userOverlap) < 2) {
            return [];
        }

        $userScoresBySet = [];
        $rows = (new Query())
            ->select(['set_id', 'overall_score'])
            ->from(self::tableName())
            ->where(['user_id' => $userId, 'status' => self::STATUS_PUBLISHED])
            ->all();
        foreach ($rows as $row) {
            $userScoresBySet[(int)$row['set_id']] = (float)$row['overall_score'];
        }

        // Find peers who reviewed at least 2 of the same sets.
        $peerRows = (new Query())
            ->select([
                'sr.user_id',
                'sr.set_id',
                'sr.overall_score',
            ])
            ->from(['sr' => self::tableName()])
            ->where(['sr.status' => self::STATUS_PUBLISHED])
            ->andWhere(['sr.set_id' => array_keys($userScoresBySet)])
            ->andWhere(['<>', 'sr.user_id', $userId])
            ->all();

        $peerAgg = [];
        foreach ($peerRows as $row) {
            $peerId = (int)$row['user_id'];
            $setIdRow = (int)$row['set_id'];
            $peerAgg[$peerId][$setIdRow] = (float)$row['overall_score'];
        }

        $peerSimilarity = [];
        foreach ($peerAgg as $peerId => $scoresBySet) {
            if (count($scoresBySet) < 2) {
                continue;
            }
            $diffSum = 0.0;
            $overlap = 0;
            foreach ($scoresBySet as $sId => $peerScore) {
                $diffSum += abs($peerScore - $userScoresBySet[$sId]);
                $overlap++;
            }
            $avgDiff = $diffSum / $overlap;
            // Similarity scale: 0 diff → 1.0, 9-point diff → 0.0
            $similarity = max(0.0, 1.0 - ($avgDiff / 9.0));
            if ($similarity > 0.5) {
                $peerSimilarity[$peerId] = ['sim' => $similarity, 'overlap' => $overlap];
            }
        }

        if ($peerSimilarity === []) {
            return [];
        }

        $peerIds = array_keys($peerSimilarity);

        // Sets these peers rated highly that the current user has not reviewed.
        $candidateRows = (new Query())
            ->select(['sr.user_id', 'sr.set_id', 'sr.overall_score'])
            ->from(['sr' => self::tableName()])
            ->where(['sr.user_id' => $peerIds, 'sr.status' => self::STATUS_PUBLISHED])
            ->andWhere(['>=', 'sr.overall_score', 7.0])
            ->andWhere(['not in', 'sr.set_id', array_keys($userScoresBySet)])
            ->all();

        $setScores = [];
        foreach ($candidateRows as $row) {
            $setIdRow = (int)$row['set_id'];
            $peerId = (int)$row['user_id'];
            $weight = $peerSimilarity[$peerId]['sim'];
            $score = ((float)$row['overall_score']) * $weight;

            if (!isset($setScores[$setIdRow])) {
                $setScores[$setIdRow] = ['score' => 0.0, 'peer_count' => 0];
            }
            $setScores[$setIdRow]['score'] += $score;
            $setScores[$setIdRow]['peer_count']++;
        }

        if ($setScores === []) {
            return [];
        }

        // Exclude sets the user owns — recommendations should be "what to buy next".
        $ownedIds = (new Query())
            ->select('set_id')
            ->from(OwnedSet::tableName())
            ->where(['user_id' => $userId])
            ->column();
        foreach ($ownedIds as $ownedId) {
            unset($setScores[(int)$ownedId]);
        }

        uasort($setScores, static fn($a, $b) => $b['score'] <=> $a['score']);

        $result = [];
        foreach (array_slice($setScores, 0, $limit, true) as $setIdRow => $entry) {
            $result[] = [
                'set_id'     => (int)$setIdRow,
                'score'      => round($entry['score'], 2),
                'peer_count' => (int)$entry['peer_count'],
            ];
        }
        return $result;
    }

    /**
     * Normalises a question-aggregate ({total, counts}) into a {value => fraction} map.
     *
     * @param array{total: int, counts: array<string, int|array{count: int}>}|null $aggregate
     * @return array<string, float>
     */
    private static function distributionForQuestion(?array $aggregate): array
    {
        if (!is_array($aggregate) || (int)($aggregate['total'] ?? 0) <= 0) {
            return [];
        }
        $total = (int)$aggregate['total'];
        $result = [];
        foreach ($aggregate['counts'] as $value => $entry) {
            $count = is_array($entry) ? (int)($entry['count'] ?? 0) : (int)$entry;
            if ($count <= 0) {
                continue;
            }
            $result[(string)$value] = $count / $total;
        }
        return $result;
    }

    /**
     * Similarity of two probability distributions over the same value set,
     * computed as 1 minus half the L1 distance (yields [0, 1]).
     *
     * @param array<string, float> $a
     * @param array<string, float> $b
     */
    private static function distributionSimilarity(array $a, array $b): float
    {
        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
        $l1 = 0.0;
        foreach ($keys as $key) {
            $l1 += abs(($a[$key] ?? 0.0) - ($b[$key] ?? 0.0));
        }
        return max(0.0, 1.0 - ($l1 / 2.0));
    }

    /**
     * Recomputes and persists Set.rating to reflect the average of published reviews.
     */
    public static function refreshSetRating(int $setId): void
    {
        $row = self::find()
            ->select([
                'avg_score' => new Expression('AVG(overall_score)'),
                'cnt'       => new Expression('COUNT(*)'),
            ])
            ->where(['set_id' => $setId, 'status' => self::STATUS_PUBLISHED])
            ->asArray()
            ->one();

        $avg = isset($row['cnt']) && (int)$row['cnt'] > 0
            ? round((float)$row['avg_score'], 2)
            : null;

        Set::updateAll(['rating' => $avg], ['id' => $setId]);
    }

    public function publish(): void
    {
        if ($this->published_at === null) {
            $this->published_at = date('Y-m-d H:i:s');
        }
        $this->status = self::STATUS_PUBLISHED;
    }

    /**
     * Returns a human-readable label for a dimension key.
     */
    public static function getDimensionLabel(string $key): string
    {
        return match ($key) {
            'design' => T::tr('Look & design'),
            'build_experience' => T::tr('Building experience'),
            'playability' => T::tr('Features & play'),
            'quality' => T::tr('Element quality'),
            'value' => T::tr('Price-to-value'),
            'recommendation' => T::tr('Overall impression'),
            default => $key,
        };
    }

    public static function getDimensionPrompt(string $key): string
    {
        return match ($key) {
            'design' => T::tr('How much do you like how the set looks once built?'),
            'build_experience' => T::tr('How enjoyable was the building experience?'),
            'playability' => T::tr('How would you rate the features and playability?'),
            'quality' => T::tr('How is the quality of the elements?'),
            'value' => T::tr('Is the set worth its price?'),
            'recommendation' => T::tr('How strongly would you recommend this set?'),
            default => '',
        };
    }

    public static function getQuestionLabel(string $key): string
    {
        return match ($key) {
            'design_theme_fit'   => T::tr('Does the set capture the theme / license well?'),
            'design_colors'      => T::tr('Are the colors and proportions visually appealing?'),
            'build_instructions' => T::tr('Were the instructions clear and logical?'),
            'build_complexity'   => T::tr('How was the build complexity?'),
            'build_techniques'   => T::tr('Did the build use any interesting techniques?'),
            'play_functions'     => T::tr('Are there moving parts or mechanisms that work well?'),
            'play_purpose'       => T::tr('Is this set better for play or for display?'),
            'play_minifigs'      => T::tr('Are the minifigures interesting and fit the set?'),
            'quality_fit'        => T::tr('Do the elements fit together well?'),
            'quality_stickers'   => T::tr('How are the stickers (if any)?'),
            'quality_unique'     => T::tr('Are there unique or rare elements in the set?'),
            'value_worth'        => T::tr('Do you think this set is worth its price?'),
            'value_pieces'       => T::tr('Is the piece count adequate for the price?'),
            'value_feel'         => T::tr('Does the set feel premium or budget?'),
            'would_buy_again'    => T::tr('Would you buy this set again?'),
            'liked_most'         => T::tr('What did you like most?'),
            'disliked_most'      => T::tr('What did you like least?'),
            'set_purpose'        => T::tr('I bought this set mainly for...'),
            'priority'           => T::tr('What matters most in THIS set? (up to 2)'),
            default              => $key,
        };
    }

    /**
     * Translates a status code into a UI label.
     */
    public static function getStatusLabel(int $status): string
    {
        return match ($status) {
            self::STATUS_PUBLISHED => T::tr('Published'),
            self::STATUS_DRAFT     => T::tr('Draft'),
            self::STATUS_HIDDEN    => T::tr('Hidden'),
            default                => '',
        };
    }

    /**
     * Bootstrap badge CSS class for the given status code.
     */
    public static function getStatusBadgeClass(int $status): string
    {
        return match ($status) {
            self::STATUS_PUBLISHED => 'text-bg-success',
            self::STATUS_DRAFT     => 'text-bg-secondary',
            self::STATUS_HIDDEN    => 'text-bg-warning text-dark',
            default                => 'text-bg-light',
        };
    }

    /**
     * Builds the 5-icon Bootstrap-icons star sequence for a 0–10 score.
     *
     * @return string[] e.g. ['bi-star-fill', 'bi-star-fill', 'bi-star-half', 'bi-star', 'bi-star']
     */
    public static function buildStarClasses(?float $score): array
    {
        if ($score === null) {
            return array_fill(0, 5, 'bi-star');
        }
        $value = max(0.0, min(10.0, $score)) / 2.0;
        $classes = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($value >= $i) {
                $classes[] = 'bi-star-fill';
            } elseif ($value >= $i - 0.5) {
                $classes[] = 'bi-star-half';
            } else {
                $classes[] = 'bi-star';
            }
        }
        return $classes;
    }

    /**
     * Picks the single dimension where the user is the most strict, and the one where they're
     * the most generous, given a {dimension_key => delta} map (positive delta = above community).
     *
     * @param array<string, float> $dimensionDiffs
     * @return array{strictest: ?array{dimension: string, delta: float}, mostGenerous: ?array{dimension: string, delta: float}}
     */
    public static function summarizeDimensionDiffs(array $dimensionDiffs): array
    {
        if ($dimensionDiffs === []) {
            return ['strictest' => null, 'mostGenerous' => null];
        }
        $sorted = $dimensionDiffs;
        uasort($sorted, static fn($a, $b) => abs($b) <=> abs($a));

        $strictest = null;
        $mostGenerous = null;
        foreach ($sorted as $dimKey => $delta) {
            if ($delta < 0 && $strictest === null) {
                $strictest = ['dimension' => $dimKey, 'delta' => (float)$delta];
            }
            if ($delta > 0 && $mostGenerous === null) {
                $mostGenerous = ['dimension' => $dimKey, 'delta' => (float)$delta];
            }
        }
        return ['strictest' => $strictest, 'mostGenerous' => $mostGenerous];
    }

    public static function getAnswerLabel(string $questionKey, string $answerValue): string
    {
        $map = [
            'design_theme_fit'   => ['poor' => T::tr('Poorly'), 'average' => T::tr('Average'), 'good' => T::tr('Well'), 'excellent' => T::tr('Excellently')],
            'design_colors'      => ['poor' => T::tr('No'), 'average' => T::tr('Average'), 'attractive' => T::tr('Yes, attractive')],
            'build_instructions' => ['confusing' => T::tr('Confusing'), 'okay' => T::tr('Okay'), 'clear' => T::tr('Clear & logical')],
            'build_complexity'   => ['too_simple' => T::tr('Too simple'), 'just_right' => T::tr('Just right'), 'too_complex' => T::tr('Too complex')],
            'build_techniques'   => ['no' => T::tr('No'), 'yes' => T::tr('Yes')],
            'play_functions'     => ['none' => T::tr('None'), 'some' => T::tr('Some'), 'great' => T::tr('Yes, work great')],
            'play_purpose'       => ['play' => T::tr('Play'), 'display' => T::tr('Display'), 'both' => T::tr('Both')],
            'play_minifigs'      => ['na' => T::tr('No minifigures'), 'poor' => T::tr('Boring'), 'average' => T::tr('Okay'), 'great' => T::tr('Great fit')],
            'quality_fit'        => ['poor' => T::tr('Poor'), 'average' => T::tr('Average'), 'good' => T::tr('Good')],
            'quality_stickers'   => ['na' => T::tr('No stickers'), 'poor' => T::tr('Hard to apply'), 'good' => T::tr('Good quality')],
            'quality_unique'     => ['no' => T::tr('No'), 'yes' => T::tr('Yes')],
            'value_worth'        => ['no' => T::tr('No'), 'average' => T::tr('Somewhat'), 'yes' => T::tr('Yes')],
            'value_pieces'       => ['too_few' => T::tr('Too few'), 'just_right' => T::tr('Just right'), 'lots' => T::tr('Plenty')],
            'value_feel'         => ['budget' => T::tr('Budget'), 'standard' => T::tr('Standard'), 'premium' => T::tr('Premium')],
            'would_buy_again'    => ['no' => T::tr('No'), 'maybe' => T::tr('Maybe'), 'yes' => T::tr('Yes')],
            'set_purpose'        => [
                'display'    => T::tr('Display'),
                'play'       => T::tr('Play'),
                'collection' => T::tr('Collection'),
                'technical'  => T::tr('Technical'),
                'minifigs'   => T::tr('Minifigures'),
            ],
            'priority'           => [
                'pieces'      => T::tr('Piece count'),
                'playability' => T::tr('Playability'),
                'looks'       => T::tr('Looks'),
                'price'       => T::tr('Price'),
                'license'     => T::tr('License / IP'),
            ],
        ];
        return $map[$questionKey][$answerValue] ?? $answerValue;
    }

    /**
     * Whether the given answer should be treated as the positive / "good" outcome
     * (used to colour the chips and aggregate bars).
     */
    public static function isPositiveAnswer(string $questionKey, string $answerValue): bool
    {
        $positive = [
            'design_theme_fit'   => ['good', 'excellent'],
            'design_colors'      => ['attractive'],
            'build_instructions' => ['clear'],
            'build_complexity'   => ['just_right'],
            'build_techniques'   => ['yes'],
            'play_functions'     => ['great'],
            'play_minifigs'      => ['great'],
            'quality_fit'        => ['good'],
            'quality_stickers'   => ['good'],
            'quality_unique'     => ['yes'],
            'value_worth'        => ['yes'],
            'value_pieces'       => ['just_right'],
            'value_feel'         => ['premium'],
            'would_buy_again'    => ['yes'],
        ];
        return isset($positive[$questionKey]) && in_array($answerValue, $positive[$questionKey], true);
    }
}
