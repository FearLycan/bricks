<?php

namespace common\tests\unit\components;

use common\components\ReadOnlySql;

class ReadOnlySqlTest extends \Codeception\Test\Unit
{
    private const TABLES = ['set', 'tag', 'theme', 'user', 'wishlist', 'owned_set'];

    /** @dataProvider queries */
    public function testValidate(string $sql, bool $allowed): void
    {
        $this->assertSame($allowed, ReadOnlySql::validate($sql, self::TABLES) === null, $sql);
    }

    public static function queries(): array
    {
        return [
            ['SELECT COUNT(*) FROM `set`', true],
            ['select s.number from set s join theme t on t.id = s.theme_id', true],
            ['SELECT EXTRACT(MONTH FROM launch_date) FROM `set`', true],
            ["SELECT name FROM tag WHERE name = 'delete from user'", true],
            ['WITH x AS (SELECT id FROM tag) SELECT COUNT(*) FROM x', true],
            ['EXPLAIN SELECT * FROM `set`', true],
            ['SHOW COLUMNS FROM `set`', true],
            ['SELECT * FROM user', false],
            ['SELECT * FROM `set`, wishlist', false],
            ['SELECT * FROM `set` s JOIN owned_set o ON o.set_id = s.id', false],
            ['SELECT * FROM (SELECT id FROM user) x', false],
            ['SELECT * FROM tag UNION SELECT id, username FROM user', false],
            ['SELECT * FROM mysql.user', false],
            ['SELECT * FROM information_schema.tables', false],
            ['SELECT 1; DROP TABLE tag', false],
            ['DELETE FROM tag', false],
            ['SELECT * FROM tag INTO OUTFILE "/tmp/x"', false],
            ['SELECT SLEEP(10)', false],
            ['SELECT * FROM tag -- comment', false],
            ['SELECT * FROM tag FOR UPDATE', false],
            ['SHOW PROCESSLIST', false],
            ['DESCRIBE user', false],
        ];
    }
}
