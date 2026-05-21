<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Query;
use yii\helpers\Json;

/**
 * @property int         $id
 * @property string      $hash
 * @property int|null    $user_id
 * @property string|null $answers
 * @property string|null $filters
 * @property string|null $labels
 * @property string      $created_at
 * @property string|null $updated_at
 */
class SearchWizardHash extends ActiveRecord
{
    private const BUDGET_MAP = [
        'budget_15'   => ['min' => null, 'max' => 1500],
        'budget_40'   => ['min' => 1500, 'max' => 4000],
        'budget_80'   => ['min' => 4000, 'max' => 8000],
        'budget_150'  => ['min' => 8000, 'max' => 15000],
        'budget_plus' => ['min' => 15000, 'max' => null],
    ];

    private const SIZE_MAP = [
        'small'  => ['min' => null, 'max' => 200],
        'medium' => ['min' => 200, 'max' => 600],
        'large'  => ['min' => 600, 'max' => null],
        'any'    => ['min' => null, 'max' => null],
    ];

    private const PROFILE_AGE_RANGES = [
        'toddler'     => ['min' => 1, 'max' => 4],
        'child'       => ['min' => 5, 'max' => 7],
        'older_child' => ['min' => 8, 'max' => 11],
        'teen'        => ['min' => 12, 'max' => 17],
        'adult'       => ['min' => 18, 'max' => null],
        'collector'   => ['min' => 18, 'max' => null],
    ];

    /**
     * set.age is a minimum recommended age ("X+"), not a target band. For
     * non-adult profiles we only exclude the junior line below this floor
     * instead of applying the profile's nominal minimum age.
     */
    private const SOFT_AGE_FLOOR = 6;

    private const YEAR_MAP = [
        'newest' => ['from' => 2024, 'to' => null],
        'recent' => ['from' => 2022, 'to' => null],
        'any'    => ['from' => null, 'to' => null],
    ];

    private const INTERESTS_MAP = [
        // Toddler (0-5)
        'toddler_vehicles'       => ['theme_ids' => [], 'group_ids' => [1], 'tag_slugs' => ['car', 'truck', 'aircraft']],
        'toddler_animals'        => ['theme_ids' => [], 'group_ids' => [1], 'tag_slugs' => ['brick-built-animals', 'dogs', 'cats', 'birds']],
        'toddler_characters'     => ['theme_ids' => [1, 10, 8], 'group_ids' => [], 'tag_slugs' => ['cartoon', 'disney']],
        'toddler_building'       => ['theme_ids' => [1, 214], 'group_ids' => [], 'tag_slugs' => []],

        // Child 6-9
        'child_superheroes'      => ['theme_ids' => [6, 45], 'group_ids' => [], 'tag_slugs' => []],
        'child_adventure'        => ['theme_ids' => [40, 117, 333, 859], 'group_ids' => [], 'tag_slugs' => ['magic']],
        'child_city'             => ['theme_ids' => [22], 'group_ids' => [], 'tag_slugs' => ['car', 'truck']],
        'child_movies'           => ['theme_ids' => [36, 156, 28], 'group_ids' => [], 'tag_slugs' => ['disney', 'cartoon']],
        'child_friends'          => ['theme_ids' => [38, 8, 10], 'group_ids' => [], 'tag_slugs' => []],
        'child_creative'         => ['theme_ids' => [214, 32, 818], 'group_ids' => [], 'tag_slugs' => []],

        // Older child 10-12
        'older_superheroes'      => ['theme_ids' => [6, 45], 'group_ids' => [], 'tag_slugs' => []],
        'older_adventure'        => ['theme_ids' => [40, 117, 333], 'group_ids' => [], 'tag_slugs' => []],
        'older_games'            => ['theme_ids' => [28, 125, 52, 49], 'group_ids' => [], 'tag_slugs' => ['video-game']],
        'older_city'             => ['theme_ids' => [22], 'group_ids' => [], 'tag_slugs' => ['car', 'truck']],
        'older_tech'             => ['theme_ids' => [56], 'group_ids' => [], 'tag_slugs' => []],
        'older_movies'           => ['theme_ids' => [42, 47, 6, 156], 'group_ids' => [], 'tag_slugs' => ['disney', 'cartoon']],

        // Teen 13-17
        'teen_games'             => ['theme_ids' => [28, 125, 146, 49, 52], 'group_ids' => [], 'tag_slugs' => ['video-game']],
        'teen_scifi'             => ['theme_ids' => [47, 526], 'group_ids' => [], 'tag_slugs' => ['space']],
        'teen_superheroes'       => ['theme_ids' => [6, 45], 'group_ids' => [], 'tag_slugs' => []],
        'teen_tech'              => ['theme_ids' => [56, 725, 334], 'group_ids' => [], 'tag_slugs' => []],
        'teen_speed'             => ['theme_ids' => [54], 'group_ids' => [], 'tag_slugs' => ['car']],
        'teen_anime'             => ['theme_ids' => [137, 40], 'group_ids' => [], 'tag_slugs' => []],
        'teen_adventure'         => ['theme_ids' => [40, 117, 333], 'group_ids' => [], 'tag_slugs' => []],

        // Adult 18+
        'adult_movies'           => ['theme_ids' => [42, 47, 6, 540, 321, 156], 'group_ids' => [], 'tag_slugs' => ['disney']],
        'adult_tech'             => ['theme_ids' => [56, 725], 'group_ids' => [], 'tag_slugs' => []],
        'adult_collections'      => ['theme_ids' => [11, 26, 688], 'group_ids' => [], 'tag_slugs' => ['display-stand', 'large-scale-object']],
        'adult_botany'           => ['theme_ids' => [20], 'group_ids' => [], 'tag_slugs' => ['brick-built-animals', 'brick-built-tree']],
        'adult_architecture'     => ['theme_ids' => [25, 11], 'group_ids' => [], 'tag_slugs' => ['house', 'microscale']],
        'adult_art'              => ['theme_ids' => [57, 32, 164], 'group_ids' => [], 'tag_slugs' => []],
        'adult_games'            => ['theme_ids' => [28, 125, 146], 'group_ids' => [], 'tag_slugs' => ['video-game']],
        'adult_speed'            => ['theme_ids' => [54, 56, 11], 'group_ids' => [], 'tag_slugs' => ['car']],

        // Collector
        'collector_limited'      => ['theme_ids' => [26, 164], 'group_ids' => [], 'tag_slugs' => ['limited-edition', 'anniversary-set']],
        'collector_movies'       => ['theme_ids' => [42, 47, 6, 540], 'group_ids' => [], 'tag_slugs' => ['disney']],
        'collector_botany'       => ['theme_ids' => [20, 57], 'group_ids' => [], 'tag_slugs' => ['brick-built-tree', 'brick-built-animals']],
        'collector_architecture' => ['theme_ids' => [25], 'group_ids' => [], 'tag_slugs' => ['house', 'microscale']],
        'collector_diorama'      => ['theme_ids' => [47, 42, 6, 11], 'group_ids' => [], 'tag_slugs' => ['display-stand', 'large-scale-object']],
        'collector_tech'         => ['theme_ids' => [56], 'group_ids' => [], 'tag_slugs' => []],
        'collector_speed'        => ['theme_ids' => [54, 56, 11], 'group_ids' => [], 'tag_slugs' => ['car']],
        'collector_art'          => ['theme_ids' => [57, 164], 'group_ids' => [], 'tag_slugs' => []],
    ];

    private const DISPLAY_CONFIG = [
        'giftProfiles'       => [
            ['value' => 'toddler', 'label' => 'Small child', 'emoji' => '👶', 'note' => 'DUPLO & toys'],
            ['value' => 'child', 'label' => 'Child', 'emoji' => '🧒'],
            ['value' => 'older_child', 'label' => 'Older child', 'emoji' => '🧒'],
            ['value' => 'teen', 'label' => 'Teenager', 'emoji' => '🧑'],
            ['value' => 'adult', 'label' => 'Adult', 'emoji' => '👨'],
            ['value' => 'collector', 'label' => 'Collector / AFOL', 'emoji' => '🏆', 'description' => 'Advanced LEGO fan'],
        ],
        'selfProfiles'       => [
            ['value' => 'child', 'label' => 'Child', 'emoji' => '🧒'],
            ['value' => 'teen', 'label' => 'Teenager', 'emoji' => '🧑'],
            ['value' => 'adult', 'label' => 'Adult', 'emoji' => '👨'],
            ['value' => 'collector', 'label' => 'Collector / AFOL', 'emoji' => '🏆', 'description' => 'Advanced LEGO fan'],
        ],
        'interestsByProfile' => [
            'toddler'     => [
                ['key' => 'toddler_vehicles', 'label' => 'Vehicles & Machines', 'emoji' => '🚗'],
                ['key' => 'toddler_animals', 'label' => 'Animals & Nature', 'emoji' => '🐘'],
                ['key' => 'toddler_characters', 'label' => 'Cartoons & Characters', 'emoji' => '🎭'],
                ['key' => 'toddler_building', 'label' => 'Free Building', 'emoji' => '🧱'],
            ],
            'child'       => [
                ['key' => 'child_superheroes', 'label' => 'Superheroes', 'emoji' => '🦸'],
                ['key' => 'child_adventure', 'label' => 'Adventure & Magic', 'emoji' => '⚔️'],
                ['key' => 'child_city', 'label' => 'City & Vehicles', 'emoji' => '🏙️'],
                ['key' => 'child_movies', 'label' => 'Movies & Cartoons', 'emoji' => '🎬'],
                ['key' => 'child_friends', 'label' => 'Friendship & Home', 'emoji' => '🏠'],
                ['key' => 'child_creative', 'label' => 'Creative Building', 'emoji' => '🎨'],
            ],
            'older_child' => [
                ['key' => 'older_superheroes', 'label' => 'Superheroes', 'emoji' => '🦸'],
                ['key' => 'older_adventure', 'label' => 'Ninjago & Action', 'emoji' => '⚔️'],
                ['key' => 'older_games', 'label' => 'Video Games', 'emoji' => '🎮'],
                ['key' => 'older_city', 'label' => 'City & Vehicles', 'emoji' => '🏙️'],
                ['key' => 'older_tech', 'label' => 'Technic', 'emoji' => '⚙️'],
                ['key' => 'older_movies', 'label' => 'Movies & Series', 'emoji' => '🎬'],
            ],
            'teen'        => [
                ['key' => 'teen_games', 'label' => 'Video Games', 'emoji' => '🎮'],
                ['key' => 'teen_scifi', 'label' => 'Sci-Fi & Space', 'emoji' => '🚀'],
                ['key' => 'teen_superheroes', 'label' => 'Superheroes', 'emoji' => '🦸'],
                ['key' => 'teen_tech', 'label' => 'Technic & Robotics', 'emoji' => '⚙️'],
                ['key' => 'teen_speed', 'label' => 'Fast Cars', 'emoji' => '🏎️'],
                ['key' => 'teen_anime', 'label' => 'Anime & Manga', 'emoji' => '⛩️'],
                ['key' => 'teen_adventure', 'label' => 'Adventure & Action', 'emoji' => '⚔️'],
            ],
            'adult'       => [
                ['key' => 'adult_movies', 'label' => 'Movies & TV Series', 'emoji' => '🎬'],
                ['key' => 'adult_tech', 'label' => 'Premium Technic', 'emoji' => '⚙️'],
                ['key' => 'adult_collections', 'label' => 'Collections & Display', 'emoji' => '🏆'],
                ['key' => 'adult_botany', 'label' => 'Botanicals & Nature', 'emoji' => '🌿'],
                ['key' => 'adult_architecture', 'label' => 'Architecture', 'emoji' => '🏛️'],
                ['key' => 'adult_art', 'label' => 'Art & Creativity', 'emoji' => '🎨'],
                ['key' => 'adult_games', 'label' => 'Gaming', 'emoji' => '🎮'],
                ['key' => 'adult_speed', 'label' => 'Collector Cars', 'emoji' => '🏎️'],
            ],
            'collector'   => [
                ['key' => 'collector_limited', 'label' => 'Limited Editions', 'emoji' => '⭐'],
                ['key' => 'collector_movies', 'label' => 'Cinema & TV', 'emoji' => '🎬'],
                ['key' => 'collector_botany', 'label' => 'Botanicals & Plants', 'emoji' => '🌿'],
                ['key' => 'collector_architecture', 'label' => 'World Architecture', 'emoji' => '🏛️'],
                ['key' => 'collector_diorama', 'label' => 'Dioramas & Displays', 'emoji' => '🎭'],
                ['key' => 'collector_tech', 'label' => 'Premium Technic', 'emoji' => '⚙️'],
                ['key' => 'collector_speed', 'label' => 'Collector Cars', 'emoji' => '🏎️'],
                ['key' => 'collector_art', 'label' => 'LEGO Art', 'emoji' => '🎨'],
            ],
        ],
        'budgetOptions'      => [
            ['value' => 'budget_15', 'label' => 'Under $15', 'emoji' => '💵', 'description' => 'Small sets and polybags'],
            ['value' => 'budget_40', 'label' => '$15 – $40', 'emoji' => '💵', 'description' => 'Solid medium-sized sets'],
            ['value' => 'budget_80', 'label' => '$40 – $80', 'emoji' => '💵', 'description' => 'Large and detailed sets'],
            ['value' => 'budget_150', 'label' => '$80 – $150', 'emoji' => '💸', 'description' => 'Premium sets'],
            ['value' => 'budget_plus', 'label' => 'Over $150', 'emoji' => '💎', 'description' => 'Flagship and limited editions'],
        ],
        'sizeOptions'        => [
            ['value' => 'small', 'label' => 'Small', 'emoji' => '🔹', 'description' => 'Up to 200 pieces – quick to build (~1 h)'],
            ['value' => 'medium', 'label' => 'Medium', 'emoji' => '🔷', 'description' => '200–600 pieces – a few hours of building'],
            ['value' => 'large', 'label' => 'Large', 'emoji' => '🔶', 'description' => '600+ pieces – hours of building challenge'],
            ['value' => 'any', 'label' => 'Any size', 'emoji' => '↔️', 'description' => 'Show sets of all sizes'],
        ],
        'yearOptions'        => [
            ['value' => 'newest', 'label' => 'Latest releases only', 'emoji' => '✨', 'description' => 'Released in 2024–2026'],
            ['value' => 'recent', 'label' => 'Last 3 years', 'emoji' => '📅', 'description' => 'Released from 2022'],
            ['value' => 'any', 'label' => 'Any year', 'emoji' => '⏳', 'description' => 'Older editions are fine too'],
        ],
    ];

    public static function tableName(): string
    {
        return '{{%search_wizard_hash}}';
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
            [['user_id'], 'integer'],
            [['answers', 'filters', 'labels'], 'string'],
            [['hash'], 'string', 'max' => 10],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public static function findByHash(string $hash): ?self
    {
        return static::findOne(['hash' => $hash]);
    }

    public static function createFromAnswers(array $answers, ?int $userId): self
    {
        $filters = static::resolveFilters($answers);
        $labels = static::resolveLabels($answers);

        $record = new static();
        $record->hash = static::generateUniqueHash();
        $record->user_id = $userId;
        $record->answers = Json::encode($answers);
        $record->filters = Json::encode($filters);
        $record->labels = Json::encode($labels);
        $record->save(false);

        return $record;
    }

    public function getAnswersData(): array
    {
        return $this->answers ? Json::decode($this->answers) : [];
    }

    public function getLabelsData(): array
    {
        return static::resolveLabels($this->getAnswersData());
    }

    public function getPublicData(): array
    {
        return [
            'hash'    => $this->hash,
            'labels'  => $this->getLabelsData(),
            'answers' => $this->getAnswersData(),
        ];
    }

    public static function getJsConfig(): array
    {
        $config = self::DISPLAY_CONFIG;

        foreach (['giftProfiles', 'selfProfiles'] as $key) {
            foreach ($config[$key] as &$profile) {
                if (!isset($profile['description'])) {
                    $profile['description'] = self::buildAgeDescription($profile['value'], $profile['note'] ?? null);
                }
                unset($profile['note']);
            }
        }

        return $config;
    }

    private static function buildAgeDescription(string $profile, ?string $note): string
    {
        $range = self::PROFILE_AGE_RANGES[$profile] ?? null;
        if ($range === null) {
            return '';
        }

        $min = $range['min'];
        $max = $range['max'];

        if ($min === null) {
            $desc = 'Up to ' . $max . ' years';
        } else if ($max === null) {
            $desc = $min . '+ years old';
        } else {
            $desc = $min . '–' . $max . ' years old';
        }

        return $note !== null ? $desc . ' – ' . $note : $desc;
    }

    public function applyToQuery(ActiveQuery $query): void
    {
        $answers = $this->getAnswersData();
        $filters = static::resolveFilters($answers);

        $orConditions = ['or'];

        if (!empty($filters['theme_ids'])) {
            $ids = array_map('intval', $filters['theme_ids']);
            $orConditions[] = ['{{%set}}.theme_id' => $ids];
            $orConditions[] = ['{{%set}}.subtheme_id' => $ids];
        } else if (!empty($filters['group_ids'])) {
            $groupIds = array_map('intval', $filters['group_ids']);
            $themeIds = Theme::find()->select('id')->where(['group_id' => $groupIds])->column();
            if (!empty($themeIds)) {
                $orConditions[] = ['{{%set}}.theme_id' => $themeIds];
                $orConditions[] = ['{{%set}}.subtheme_id' => $themeIds];
            }
        }

        if (!empty($filters['tag_slugs'])) {
            $tagSubquery = (new Query())
                ->select('st.set_id')
                ->from('{{%set_tag}} st')
                ->innerJoin('{{%tag}} tg', 'tg.id = st.tag_id')
                ->where(['tg.slug' => $filters['tag_slugs'], 'tg.status' => 1]);
            $orConditions[] = ['{{%set}}.id' => $tagSubquery];
        }

        if (count($orConditions) > 1) {
            $query->andWhere($orConditions);
        }

        if (!empty($filters['price_min'])) {
            $query->andWhere(['>=', '{{%set}}.price', (int)$filters['price_min']]);
        }
        if (!empty($filters['price_max'])) {
            $query->andWhere([
                'or',
                ['{{%set}}.price' => null],
                ['<=', '{{%set}}.price', (int)$filters['price_max']],
            ]);
        }

        if (!empty($filters['pieces_min'])) {
            $query->andWhere(['>=', '{{%set}}.pieces', (int)$filters['pieces_min']]);
        }
        if (!empty($filters['pieces_max'])) {
            $query->andWhere([
                'or',
                ['{{%set}}.pieces' => null],
                ['<=', '{{%set}}.pieces', (int)$filters['pieces_max']],
            ]);
        }

        $profile = $answers['profile'] ?? null;

        if (in_array($profile, ['adult', 'collector'], true)) {
            if (!empty($filters['age_min'])) {
                $query->andWhere(['>=', '{{%set}}.age', (int)$filters['age_min']]);
            }
        } else {
            // set.age is a minimum recommended age ("X+"), not a target band:
            // a teenager can build an 8+ set. So we apply only a soft lower
            // bound to exclude the junior line, plus the profile's upper
            // bound. An age of 0 means "unknown" and is treated like NULL.
            if (!empty($filters['age_min'])) {
                $floor = min(self::SOFT_AGE_FLOOR, (int)$filters['age_min']);
                $query->andWhere([
                    'or',
                    ['{{%set}}.age' => null],
                    ['{{%set}}.age' => 0],
                    ['>=', '{{%set}}.age', $floor],
                ]);
            }
            if (!empty($filters['age_max'])) {
                $query->andWhere([
                    'or',
                    ['{{%set}}.age' => null],
                    ['{{%set}}.age' => 0],
                    ['<=', '{{%set}}.age', (int)$filters['age_max']],
                ]);
            }
        }

        if (!empty($filters['year_from'])) {
            $query->andWhere(['>=', '{{%set}}.year', (int)$filters['year_from']]);
        }
        if (!empty($filters['year_to'])) {
            $query->andWhere(['<=', '{{%set}}.year', (int)$filters['year_to']]);
        }
    }

    private static function resolveFilters(array $answers): array
    {
        $filters = [
            'theme_ids'  => [],
            'group_ids'  => [],
            'tag_slugs'  => [],
            'age_min'    => null,
            'age_max'    => null,
            'price_min'  => null,
            'price_max'  => null,
            'pieces_min' => null,
            'pieces_max' => null,
            'year_from'  => null,
            'year_to'    => null,
        ];

        $budget = $answers['budget'] ?? null;
        if ($budget && isset(self::BUDGET_MAP[$budget])) {
            $filters['price_min'] = self::BUDGET_MAP[$budget]['min'];
            $filters['price_max'] = self::BUDGET_MAP[$budget]['max'];
        }

        $size = $answers['size'] ?? null;
        if ($size && isset(self::SIZE_MAP[$size])) {
            $filters['pieces_min'] = self::SIZE_MAP[$size]['min'];
            $filters['pieces_max'] = self::SIZE_MAP[$size]['max'];
        }

        $year = $answers['year'] ?? null;
        if ($year && isset(self::YEAR_MAP[$year])) {
            $filters['year_from'] = self::YEAR_MAP[$year]['from'];
            $filters['year_to'] = self::YEAR_MAP[$year]['to'];
        }

        $interests = $answers['interests'] ?? [];
        $themeIds = [];
        $groupIds = [];
        $tagSlugs = [];
        foreach ($interests as $key) {
            if (isset(self::INTERESTS_MAP[$key])) {
                $themeIds = array_merge($themeIds, self::INTERESTS_MAP[$key]['theme_ids']);
                $groupIds = array_merge($groupIds, self::INTERESTS_MAP[$key]['group_ids']);
                $tagSlugs = array_merge($tagSlugs, self::INTERESTS_MAP[$key]['tag_slugs'] ?? []);
            }
        }
        $filters['theme_ids'] = array_values(array_unique($themeIds));
        $filters['group_ids'] = array_values(array_unique($groupIds));
        $filters['tag_slugs'] = array_values(array_unique($tagSlugs));

        $profile = $answers['profile'] ?? null;

        if ($profile === 'toddler' && empty($filters['theme_ids']) && empty($filters['group_ids'])) {
            $filters['group_ids'] = [1];
        }

        if ($profile && isset(self::PROFILE_AGE_RANGES[$profile])) {
            $filters['age_min'] = self::PROFILE_AGE_RANGES[$profile]['min'];
            $filters['age_max'] = self::PROFILE_AGE_RANGES[$profile]['max'];
        }

        return $filters;
    }

    private static function resolveLabels(array $answers): array
    {
        $labels = [];
        $recipient = $answers['recipient'] ?? null;
        $profile = $answers['profile'] ?? null;

        $profileLabel = $profile
            ? (self::findOptionLabel(self::DISPLAY_CONFIG['giftProfiles'], $profile) ?? $profile)
            : null;
        $profileDesc  = $profile ? self::profileDescription($profile) : null;
        $profileFull  = $profileLabel . ($profileDesc ? ' (' . $profileDesc . ')' : '');
        $labels['for'] = $recipient === 'gift'
            ? 'For: ' . ($profileFull ?? 'someone')
            : 'For myself' . ($profileFull ? ' · ' . $profileFull : '');

        $budget = $answers['budget'] ?? null;
        if ($budget && $budgetLabel = self::findOptionLabel(self::DISPLAY_CONFIG['budgetOptions'], $budget)) {
            $labels['budget'] = 'Budget: ' . $budgetLabel;
        }

        $interests = $answers['interests'] ?? [];
        if (!empty($interests)) {
            $interestMap = [];
            foreach (self::DISPLAY_CONFIG['interestsByProfile'][$profile] ?? [] as $item) {
                $interestMap[$item['key']] = $item['label'];
            }
            $names = array_map(fn($k) => $interestMap[$k] ?? $k, $interests);
            $labels['interests'] = implode(', ', $names);
        }

        $size = $answers['size'] ?? null;
        if ($size && $size !== 'any' && $sizeLabel = self::findOptionLabel(self::DISPLAY_CONFIG['sizeOptions'], $size)) {
            $labels['size'] = 'Size: ' . $sizeLabel;
        }

        $year = $answers['year'] ?? null;
        if ($year && $year !== 'any' && $yearLabel = self::findOptionLabel(self::DISPLAY_CONFIG['yearOptions'], $year)) {
            $labels['year'] = 'Year: ' . $yearLabel;
        }

        return $labels;
    }

    private static function findOptionLabel(array $options, string $value): ?string
    {
        foreach ($options as $opt) {
            if ($opt['value'] === $value) {
                return $opt['label'];
            }
        }
        return null;
    }

    private static function profileDescription(string $profile): string
    {
        foreach (self::DISPLAY_CONFIG['giftProfiles'] as $p) {
            if ($p['value'] !== $profile) {
                continue;
            }
            return isset($p['description'])
                ? $p['description']
                : self::buildAgeDescription($profile, null);
        }
        return '';
    }

    private static function generateUniqueHash(): string
    {
        do {
            $hash = substr(bin2hex(random_bytes(5)), 0, 8);
        } while (static::findOne(['hash' => $hash]) !== null);

        return $hash;
    }
}
