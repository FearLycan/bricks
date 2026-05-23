<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class HomeCest
{
    public function checkOpen(FunctionalTester $I)
    {
        $I->amOnRoute(\Yii::$app->homeUrl);
        $I->see('My Application');
        $I->seeLink('About');
        $I->click('About');
        $I->see('BrickAtlas is a price tracker and discovery tool for LEGO® fans.');
    }
}