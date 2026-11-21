<?php

namespace common\tests\unit\components;

use common\components\IndexPolicy;

class IndexPolicyTest extends \Codeception\Test\Unit
{
    public function testTagThreshold(): void
    {
        $this->assertSame(IndexPolicy::NOINDEX, IndexPolicy::forTag(9));
        $this->assertSame(IndexPolicy::INDEX, IndexPolicy::forTag(10));
    }

    public function testMinifigThreshold(): void
    {
        $this->assertSame(IndexPolicy::NOINDEX, IndexPolicy::forMinifig(2));
        $this->assertSame(IndexPolicy::INDEX, IndexPolicy::forMinifig(3));
    }

    public function testThemeThresholds(): void
    {
        $this->assertSame(IndexPolicy::INDEX, IndexPolicy::forTheme(1, false));
        $this->assertSame(IndexPolicy::NOINDEX, IndexPolicy::forTheme(4, true));
        $this->assertSame(IndexPolicy::INDEX, IndexPolicy::forTheme(5, true));
    }
}
