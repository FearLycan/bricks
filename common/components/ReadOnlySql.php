<?php

namespace common\components;

use Yii;
use yii\db\Connection;
use yii\db\Exception as DbException;

/**
 * Read-only SQL for admin diagnostics (ManagementController::actionDbQuery).
 *
 * Layers, each enough on its own for the common cases:
 * 1. validate(): one SELECT / WITH / EXPLAIN / SHOW / DESCRIBE statement,
 *    no comments, no `;`, no INTO OUTFILE / file / lock / sleep functions,
 *    and every table it
 *    names must be in ALLOWED_TABLES (no user data: user, wishlist, owned
 *    sets, settings, wizard answers).
 * 2. run(): inside START TRANSACTION READ ONLY (the server refuses writes),
 *    with a per-statement time limit and a row cap, always rolled back.
 */
final class ReadOnlySql
{
    public const MAX_ROWS = 1000;
    public const TIMEOUT_SECONDS = 5;

    /** Catalog, offers, reviews and error log. Anything else is refused. */
    public const ALLOWED_TABLES = [
        'set', 'set_image', 'set_instruction', 'set_minifig', 'set_offer', 'set_offer_import',
        'set_offer_review', 'set_offer_review_image', 'set_price', 'set_review', 'set_review_answer',
        'set_review_score', 'set_tag', 'store', 'tag', 'theme', 'theme_group', 'log',
    ];

    private const ALLOWED_START = '/^(select|with|explain|show|describe|desc)\b/i';

    private const FORBIDDEN = [
        '/\binto\s+(outfile|dumpfile|@)/i',
        '/\b(load_file|sleep|benchmark|get_lock|release_lock|is_free_lock|master_pos_wait)\s*\(/i',
        '/\bfor\s+update\b/i',
        '/\block\s+in\s+share\s+mode\b/i',
    ];

    /**
     * @param list<string>|null $existingTables tables in the database; names that
     *        are not tables (e.g. EXTRACT(YEAR FROM col)) are then ignored.
     *        Schema-qualified names (mysql.user, information_schema.x) are always refused.
     * @return string|null error message, or null when the query may run
     */
    public static function validate(string $sql, ?array $existingTables = null): ?string
    {
        $sql = trim($sql);
        if ($sql === '') {
            return 'Empty query.';
        }
        if (mb_strlen($sql) > 5000) {
            return 'Query too long (max 5000 characters).';
        }
        if (preg_match('/;|--|#|\/\*/', $sql) === 1) {
            return 'One statement only, without comments or ";".';
        }
        if (preg_match(self::ALLOWED_START, $sql) !== 1) {
            return 'Only SELECT, WITH, EXPLAIN, SHOW and DESCRIBE are allowed.';
        }
        if (preg_match('/^show\b/i', $sql) === 1
            && preg_match('/^show\s+(tables|table\s+status|(full\s+)?(columns|fields)|index|indexes|keys|create\s+table)\b/i', $sql) !== 1) {
            return 'SHOW is limited to TABLES, TABLE STATUS, COLUMNS, INDEX and CREATE TABLE.';
        }

        // Keywords inside string literals must not trip the checks below.
        $bare = preg_replace("/'(?:[^'\\\\]|\\\\.)*'|\"(?:[^\"\\\\]|\\\\.)*\"/s", "''", $sql);
        foreach (self::FORBIDDEN as $pattern) {
            if (preg_match($pattern, (string)$bare) === 1) {
                return 'Statement contains a forbidden keyword or function.';
            }
        }

        $existing = $existingTables === null ? null : array_map('strtolower', $existingTables);
        foreach (self::referencedTables((string)$bare) as $table) {
            if ($existing !== null && !str_contains($table, '.') && !in_array($table, $existing, true)) {
                continue;
            }
            if (!in_array($table, self::ALLOWED_TABLES, true)) {
                return "Table not allowed: {$table}.";
            }
        }

        return null;
    }

    /**
     * Table names after FROM / JOIN (incl. comma lists) and in SHOW/DESCRIBE.
     *
     * @return list<string>
     */
    public static function referencedTables(string $sql): array
    {
        $tables = [];
        $name = '`?(?:[a-z0-9_]+`?\.`?)?([a-z0-9_]+)`?';
        if (preg_match_all('/\b(?:from|join)\s+(' . $name . '(?:\s*,\s*' . $name . ')*)/i', $sql, $m)) {
            foreach ($m[1] as $list) {
                foreach (preg_split('/\s*,\s*/', $list) as $part) {
                    // "t alias" -> t; schema-qualified names keep the schema so they are refused.
                    $part = trim(preg_split('/\s+/', trim($part))[0], '`');
                    $part = str_replace('`', '', $part);
                    if ($part !== '' && $part[0] !== '(') {
                        $tables[] = strtolower($part);
                    }
                }
            }
        }
        if (preg_match('/^\s*(?:describe|desc)\s+`?([a-z0-9_.]+)`?/i', $sql, $d)) {
            $tables[] = strtolower($d[1]);
        }
        if (preg_match('/^\s*show\s+(?:full\s+)?(?:columns|fields|index|indexes|keys|create\s+table)\s+(?:from|in)?\s*`?([a-z0-9_.]+)`?/i', $sql, $s)) {
            $tables[] = strtolower($s[1]);
        }

        return array_values(array_unique($tables));
    }

    /**
     * @return array{columns: list<string>, rows: list<array<string, mixed>>, truncated: bool, ms: int}
     * @throws DbException
     */
    public static function run(string $sql, ?Connection $db = null): array
    {
        $db ??= Yii::$app->db;
        $started = microtime(true);

        self::trySet($db, 'SET SESSION MAX_EXECUTION_TIME=' . (self::TIMEOUT_SECONDS * 1000)); // MySQL
        self::trySet($db, 'SET SESSION max_statement_time=' . self::TIMEOUT_SECONDS);          // MariaDB
        $db->createCommand('START TRANSACTION READ ONLY')->execute();

        try {
            $reader = $db->createCommand(trim($sql))->query();
            $rows = [];
            $truncated = false;
            foreach ($reader as $row) {
                if (count($rows) >= self::MAX_ROWS) {
                    $truncated = true;
                    break;
                }
                $rows[] = $row;
            }
            $reader->close();
        } finally {
            $db->createCommand('ROLLBACK')->execute();
            self::trySet($db, 'SET SESSION MAX_EXECUTION_TIME=0');
            self::trySet($db, 'SET SESSION max_statement_time=0');
        }

        return [
            'columns'   => $rows === [] ? [] : array_keys($rows[0]),
            'rows'      => $rows,
            'truncated' => $truncated,
            'ms'        => (int)round((microtime(true) - $started) * 1000),
        ];
    }

    private static function trySet(Connection $db, string $statement): void
    {
        try {
            $db->createCommand($statement)->execute();
        } catch (DbException) {
            // Variable not known by this server flavour.
        }
    }
}
