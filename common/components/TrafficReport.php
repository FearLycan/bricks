<?php

declare(strict_types=1);

namespace common\components;

use DateInterval;
use DateTimeImmutable;
use yii\db\Query;

/**
 * Read side of the traffic stats for the admin: one period (last N days including today)
 * compared with the N days before it. Visits = entries (views that came from outside).
 * Ported from store-base (without outbound clicks; BrickAtlas has no click table yet).
 */
final class TrafficReport
{
    public readonly string $from;
    public readonly string $to;
    public readonly string $prevFrom;
    public readonly string $prevTo;

    public function __construct(public readonly int $days)
    {
        $today = new DateTimeImmutable('today');
        $this->to = $today->format('Y-m-d');
        $this->from = $today->sub(new DateInterval('P' . ($days - 1) . 'D'))->format('Y-m-d');
        $this->prevTo = $today->sub(new DateInterval('P' . $days . 'D'))->format('Y-m-d');
        $this->prevFrom = $today->sub(new DateInterval('P' . ($days * 2 - 1) . 'D'))->format('Y-m-d');
    }

    /** Everything the admin Traffic page shows, as one JSON-ready array. */
    public function toArray(): array
    {
        $bots = $this->bots();

        return [
            'range' => ['days' => $this->days, 'from' => $this->from, 'to' => $this->to, 'prevFrom' => $this->prevFrom, 'prevTo' => $this->prevTo],
            'kpis' => $this->kpis(),
            'daily' => $this->daily(),
            'sources' => $this->sources(),
            'pageTypes' => $this->pageTypes(),
            'devices' => $this->devices(),
            'landing' => $this->landingPages(),
            'referrers' => $this->referrers(),
            'languages' => $this->languages(),
            'searches' => $this->searches(),
            'zeroSearches' => $this->searches(true),

            'bots' => $bots['bots'],
            'google' => $bots['google'],
            'notFound' => $this->notFound(),
            'generatedAt' => date('c'),
        ];
    }

    /** @return array<string, array{now:float, prev:float}> */
    public function kpis(): array
    {
        $now = $this->totals($this->from, $this->to);
        $prev = $this->totals($this->prevFrom, $this->prevTo);
        $out = [];
        foreach ($now as $k => $v) {
            $out[$k] = ['now' => $v, 'prev' => $prev[$k]];
        }

        return $out;
    }

    /** @return list<array{day:string, visits:int, views:int}> */
    public function daily(): array
    {
        $rows = [];
        $d = new DateTimeImmutable($this->from);
        $end = new DateTimeImmutable($this->to);
        while ($d <= $end) {
            $rows[$d->format('Y-m-d')] = ['day' => $d->format('Y-m-d'), 'visits' => 0, 'views' => 0];
            $d = $d->add(new DateInterval('P1D'));
        }
        $traffic = (new Query())
            ->select(['day', 'visits' => 'SUM(entries)', 'views' => 'SUM(views)'])
            ->from('{{%traffic_daily}}')
            ->where(['between', 'day', $this->from, $this->to])
            ->groupBy('day')
            ->all();
        foreach ($traffic as $r) {
            $key = substr((string) $r['day'], 0, 10);
            if (isset($rows[$key])) {
                $rows[$key]['visits'] = (int) $r['visits'];
                $rows[$key]['views'] = (int) $r['views'];
            }
        }
        return array_values($rows);
    }

    /** @return list<array{source:string, label:string, visits:int, views:int, prev:int}> */
    public function sources(): array
    {
        $now = $this->sumBy('{{%traffic_daily}}', 'source', ['visits' => 'SUM(entries)', 'views' => 'SUM(views)'], $this->from, $this->to);
        $prev = $this->sumBy('{{%traffic_daily}}', 'source', ['visits' => 'SUM(entries)'], $this->prevFrom, $this->prevTo);
        $out = [];
        foreach (TrafficClassifier::SOURCES as $key => $label) {
            if ($key === 'internal') {
                continue;
            }
            $visits = (int) ($now[$key]['visits'] ?? 0);
            $before = (int) ($prev[$key]['visits'] ?? 0);
            if ($visits === 0 && $before === 0) {
                continue;
            }
            $out[] = ['source' => $key, 'label' => $label, 'visits' => $visits, 'views' => (int) ($now[$key]['views'] ?? 0), 'prev' => $before];
        }
        usort($out, static fn ($a, $b) => $b['visits'] <=> $a['visits']);

        return $out;
    }

    /** Where visits start and how many pages each type gets. @return list<array{label:string, visits:int, views:int}> */
    public function pageTypes(): array
    {
        $rows = $this->sumBy('{{%traffic_daily}}', 'page_type', ['visits' => 'SUM(entries)', 'views' => 'SUM(views)'], $this->from, $this->to);
        $out = [];
        foreach ($rows as $type => $r) {
            if ($type === 'notfound') {
                continue;
            }
            $out[] = ['label' => TrafficClassifier::PAGE_TYPES[$type] ?? $type, 'visits' => (int) $r['visits'], 'views' => (int) $r['views']];
        }
        usort($out, static fn ($a, $b) => $b['views'] <=> $a['views']);

        return $out;
    }

    /** @return array<string, int> device => visits */
    public function devices(): array
    {
        $rows = $this->sumBy('{{%traffic_daily}}', 'device', ['visits' => 'SUM(entries)'], $this->from, $this->to);
        $out = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0];
        foreach ($rows as $d => $r) {
            $out[$d] = (int) $r['visits'];
        }

        return $out;
    }

    /** @return list<array{path:string, type:string, visits:int}> */
    public function landingPages(int $limit = 15): array
    {
        return array_map(static fn ($r) => [
            'path' => (string) $r['path'],
            'type' => TrafficClassifier::PAGE_TYPES[$r['page_type']] ?? (string) $r['page_type'],
            'visits' => (int) $r['n'],
        ], (new Query())
            ->select(['path' => 'MAX(path)', 'page_type' => 'MAX(page_type)', 'n' => 'SUM(entries)'])
            ->from('{{%traffic_path_daily}}')
            ->where(['between', 'day', $this->from, $this->to])
            ->andWhere(['<>', 'page_type', 'notfound'])
            ->groupBy('path_hash')
            ->orderBy(['n' => SORT_DESC])
            ->limit($limit)
            ->all());
    }

    /** @return list<array{path:string, hits:int}> */
    public function notFound(int $limit = 15): array
    {
        return array_map(static fn ($r) => ['path' => (string) $r['path'], 'hits' => (int) $r['n']], (new Query())
            ->select(['path' => 'MAX(path)', 'n' => 'SUM(views)'])
            ->from('{{%traffic_path_daily}}')
            ->where(['between', 'day', $this->from, $this->to])
            ->andWhere(['page_type' => 'notfound'])
            ->groupBy('path_hash')
            ->orderBy(['n' => SORT_DESC])
            ->limit($limit)
            ->all());
    }

    /** @return list<array{host:string, source:string, visits:int}> */
    public function referrers(int $limit = 15): array
    {
        return array_map(static fn ($r) => [
            'host' => (string) $r['host'],
            'source' => TrafficClassifier::SOURCES[$r['source']] ?? (string) $r['source'],
            'visits' => (int) $r['n'],
        ], (new Query())
            ->select(['host', 'source' => 'MAX(source)', 'n' => 'SUM(entries)'])
            ->from('{{%traffic_referrer_daily}}')
            ->where(['between', 'day', $this->from, $this->to])
            ->groupBy('host')
            ->orderBy(['n' => SORT_DESC])
            ->limit($limit)
            ->all());
    }

    /** @return list<array{query:string, searches:int, results:int}> */
    public function searches(bool $zeroOnly = false, int $limit = 15): array
    {
        $q = (new Query())
            ->select(['query' => 'MAX(query)', 'n' => 'SUM(searches)', 'results' => 'MAX(results)'])
            ->from('{{%search_daily}}')
            ->where(['between', 'day', $this->from, $this->to])
            ->groupBy('query_hash')
            ->orderBy(['n' => SORT_DESC])
            ->limit($limit);
        if ($zeroOnly) {
            $q->having(['MAX(results)' => 0]);
        }

        return array_map(static fn ($r) => ['query' => (string) $r['query'], 'searches' => (int) $r['n'], 'results' => (int) $r['results']], $q->all());
    }

    /** Crawler hits per bot family, with Google's split by page type. @return array{bots: array<string,int>, google: array<string,int>} */
    public function bots(): array
    {
        $bots = [];
        foreach ($this->sumBy('{{%traffic_bot_daily}}', 'bot', ['n' => 'SUM(hits)'], $this->from, $this->to) as $bot => $r) {
            $bots[$bot] = (int) $r['n'];
        }
        arsort($bots);
        $google = [];
        $rows = (new Query())
            ->select(['page_type', 'n' => 'SUM(hits)'])
            ->from('{{%traffic_bot_daily}}')
            ->where(['between', 'day', $this->from, $this->to])
            ->andWhere(['bot' => 'google'])
            ->groupBy('page_type')
            ->orderBy(['n' => SORT_DESC])
            ->all();
        foreach ($rows as $r) {
            $google[TrafficClassifier::PAGE_TYPES[$r['page_type']] ?? $r['page_type']] = (int) $r['n'];
        }

        return ['bots' => $bots, 'google' => $google];
    }

    /** @return array{visits:float, views:float, searches:float, set_views:float} */
    private function totals(string $from, string $to): array
    {
        $t = (new Query())
            ->select([
                'visits' => 'COALESCE(SUM(entries),0)',
                'views' => 'COALESCE(SUM(views),0)',
                'set_views' => "COALESCE(SUM(CASE WHEN page_type = 'set' THEN views ELSE 0 END),0)",
            ])
            ->from('{{%traffic_daily}}')
            ->where(['between', 'day', $from, $to])
            ->one() ?: [];
        $searches = (int) (new Query())->from('{{%search_daily}}')
            ->where(['between', 'day', $from, $to])->sum('searches');

        return [
            'visits' => (float) ($t['visits'] ?? 0),
            'views' => (float) ($t['views'] ?? 0),
            'searches' => (float) $searches,
            'set_views' => (float) ($t['set_views'] ?? 0),
        ];
    }

    /** Visits and views per page language. @return list<array{lang:string, visits:int, views:int, prev:int}> */
    public function languages(): array
    {
        $now = $this->sumBy('{{%traffic_daily}}', 'lang', ['visits' => 'SUM(entries)', 'views' => 'SUM(views)'], $this->from, $this->to);
        $prev = $this->sumBy('{{%traffic_daily}}', 'lang', ['visits' => 'SUM(entries)'], $this->prevFrom, $this->prevTo);
        $out = [];
        foreach (array_unique(array_merge(array_keys($now), array_keys($prev))) as $lang) {
            $out[] = ['lang' => (string) $lang, 'visits' => (int) ($now[$lang]['visits'] ?? 0), 'views' => (int) ($now[$lang]['views'] ?? 0), 'prev' => (int) ($prev[$lang]['visits'] ?? 0)];
        }
        usort($out, static fn ($a, $b) => $b['visits'] <=> $a['visits']);

        return $out;
    }

    /**
     * @param array<string, string> $select alias => aggregate
     * @return array<string, array<string, mixed>> keyed by the group column
     */
    private function sumBy(string $table, string $col, array $select, string $from, string $to): array
    {
        return (new Query())
            ->select([$col] + $select)
            ->from($table)
            ->where(['between', 'day', $from, $to])
            ->groupBy($col)
            ->indexBy($col)
            ->all();
    }
}
