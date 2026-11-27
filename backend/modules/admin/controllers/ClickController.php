<?php

namespace backend\modules\admin\controllers;

use backend\components\Controller;
use common\models\OfferClick;
use yii\db\Expression;
use yii\db\Query;

/** Store link clicks recorded by frontend\controllers\ClickController (humans only unless ?bots=1). */
class ClickController extends Controller
{
    public const RANGES = [1, 7, 30, 90, 365];

    public function actionIndex(int $days = 30, int $bots = 0): string
    {
        $days = in_array($days, self::RANGES, true) ? $days : 30;
        $from = $days === 1 ? strtotime('today') : strtotime('today -' . ($days - 1) . ' days');
        $prevFrom = $from - ($days * 86400);
        $isBot = $bots ? 1 : 0;

        $base = static fn(int $start, int $end): Query => (new Query())->from(['c' => OfferClick::tableName()])
            ->where(['c.is_bot' => $isBot])->andWhere(['>=', 'c.created_at', $start])->andWhere(['<', 'c.created_at', $end]);
        $now = time() + 1;

        $totals = $base($from, $now)->select([
            'clicks' => 'COUNT(*)',
            'offer'  => new Expression("SUM(c.kind = 'offer')"),
            'lego'   => new Expression("SUM(c.kind = 'lego')"),
            'sets'   => new Expression('COUNT(DISTINCT c.set_id)'),
        ])->one();
        $previous = (int)$base($prevFrom, $from)->count();
        $botCount = (int)(new Query())->from(OfferClick::tableName())->where(['is_bot' => 1 - $isBot])->andWhere(['>=', 'created_at', $from])->count();

        $daily = $base($from, $now)->select(['day' => new Expression("FROM_UNIXTIME(c.created_at, '%Y-%m-%d')"), 'n' => 'COUNT(*)'])
            ->groupBy('day')->orderBy('day')->indexBy('day')->column();

        $stores = $base($from, $now)->select([
            'name'  => new Expression("COALESCE(s.name, IF(c.kind = 'lego', 'LEGO.com (official price)', '(deleted store)'))"),
            'n'     => 'COUNT(*)',
        ])->leftJoin(['s' => '{{%store}}'], 's.id = c.store_id')->groupBy('name')->orderBy(['n' => SORT_DESC])->limit(20)->all();

        $sets = $base($from, $now)->select([
            'c.set_id', 'st.number', 'st.name', 'st.slug',
            'n'     => 'COUNT(*)',
            'offer' => new Expression("SUM(c.kind = 'offer')"),
            'lego'  => new Expression("SUM(c.kind = 'lego')"),
        ])->leftJoin(['st' => '{{%set}}'], 'st.id = c.set_id')
            ->groupBy(['c.set_id', 'st.number', 'st.name', 'st.slug'])->orderBy(['n' => SORT_DESC])->limit(30)->all();

        $langs = $base($from, $now)->select(['lang' => new Expression("COALESCE(c.lang, '?')"), 'n' => 'COUNT(*)'])
            ->groupBy('lang')->orderBy(['n' => SORT_DESC])->all();
        $devices = $base($from, $now)->select(['device' => new Expression("COALESCE(c.device, '?')"), 'n' => 'COUNT(*)'])
            ->groupBy('device')->orderBy(['n' => SORT_DESC])->all();

        $recent = OfferClick::find()->with(['set', 'store'])->where(['is_bot' => $isBot])
            ->orderBy(['id' => SORT_DESC])->limit(50)->all();

        return $this->render('index', compact('days', 'bots', 'totals', 'previous', 'botCount', 'daily', 'from', 'stores', 'sets', 'langs', 'devices', 'recent') + ['ranges' => self::RANGES]);
    }
}
