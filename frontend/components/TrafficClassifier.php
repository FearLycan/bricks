<?php

declare(strict_types=1);

namespace frontend\components;

/**
 * Pure helpers that turn request headers into the coarse buckets the traffic stats keep.
 * Nothing here identifies a person: no IP, no full user agent, no cookies. Ported from store-base.
 */
final class TrafficClassifier
{
    /** Labels for the admin, in display order. */
    public const SOURCES = [
        'google'   => 'Google',
        'bing'     => 'Bing',
        'search'   => 'Other search',
        'pinterest' => 'Pinterest',
        'social'   => 'Social',
        'ai'       => 'AI assistants',
        'referral' => 'Other sites',
        'direct'   => 'Direct',
        'internal' => 'Internal',
    ];

    public const PAGE_TYPES = [
        'home'     => 'Home',
        'set'      => 'Set page',
        'catalog'  => 'Catalog / search',
        'theme'    => 'Theme',
        'tag'      => 'Tag',
        'minifig'  => 'Minifigure',
        'guide'    => 'Gift guides',
        'hub'      => 'Landing pages',
        'info'     => 'Info pages',
        'notfound' => 'Not found (404)',
    ];

    /** Routes that count as a page view, by page type. Anything else is not tracked. */
    private const ROUTES = [
        'homepage/home/index'        => 'home',
        'site/index'                 => 'home',
        'lego/lego/view'             => 'set',
        'lego/lego/index'            => 'catalog',
        'lego/theme/index'           => 'theme',
        'lego/theme/view'            => 'theme',
        'lego/lego/tag'              => 'tag',
        'lego/lego/minifig'          => 'minifig',
        'lego/lego/gifts'            => 'guide',
        'lego/lego/gift-guide'       => 'guide',
        'lego/lego/advent-calendars' => 'guide',
        'lego/lego/retiring-soon'    => 'hub',
        'lego/lego/new'              => 'hub',
        'lego/lego/exclusive'        => 'hub',
        'lego/lego/promo'            => 'hub',
        'lego/lego/magazines'        => 'hub',
        'lego/lego/audience'         => 'hub',
        'site/about'                 => 'info',
        'site/contact'               => 'info',
        'site/faq'                   => 'info',
        'site/glossary'              => 'info',
        'site/interests'             => 'info',
        'site/privacy'               => 'info',
    ];

    private const SEARCH_HOSTS = '/(^|\.)(duckduckgo|yahoo|yandex|ecosia|brave|qwant|baidu|naver|seznam|startpage|aol)\./';
    private const SOCIAL_HOSTS = '/(^|\.)(facebook|fb|instagram|threads|reddit|tiktok|youtube|linkedin|twitter|x|t|vk|tumblr|quora|messenger|whatsapp|telegram)\.(com|co|me|net|org)$|^(t\.co|lm\.facebook\.com|l\.instagram\.com)$/';
    private const AI_HOSTS = '/(^|\.)(chatgpt\.com|openai\.com|perplexity\.ai|gemini\.google\.com|copilot\.microsoft\.com|claude\.ai|you\.com|phind\.com|meta\.ai|deepseek\.com|grok\.com)$/';

    /** Bot families worth telling apart; first match wins. */
    private const BOTS = [
        'google'     => '/googlebot|google-inspectiontool|googleother|adsbot-google|storebot-google|mediapartners-google|google-extended/i',
        'bing'       => '/bingbot|bingpreview|msnbot|adidxbot/i',
        'pinterest'  => '/pinterest/i',
        'apple'      => '/applebot/i',
        'yandex'     => '/yandex/i',
        'openai'     => '/gptbot|oai-searchbot|chatgpt-user/i',
        'claude'     => '/claudebot|claude-user|claude-searchbot|anthropic/i',
        'perplexity' => '/perplexity/i',
        'meta'       => '/facebookexternalhit|meta-externalagent|facebookbot/i',
        'seo'        => '/ahrefs|semrush|mj12|dotbot|dataforseo|serpstat|seznambot|blexbot|barkrowler/i',
    ];

    public static function pageType(string $route): ?string
    {
        return self::ROUTES[$route] ?? null;
    }

    /** Null for a human browser, else the bot family ('other' when unrecognised). */
    public static function bot(string $userAgent): ?string
    {
        if (trim($userAgent) === '') {
            return 'other';
        }
        foreach (self::BOTS as $name => $pattern) {
            if (preg_match($pattern, $userAgent) === 1) {
                return $name;
            }
        }

        return preg_match('/bot|crawl|spider|slurp|fetch|scrape|curl|wget|python|java\/|go-http|headless|phantom|httpclient|axios|node-fetch/i', $userAgent) === 1 ? 'other' : null;
    }

    public static function device(string $userAgent): string
    {
        if (preg_match('/ipad|tablet|kindle|silk|playbook|(android(?!.*mobile))/i', $userAgent) === 1) {
            return 'tablet';
        }

        return preg_match('/mobi|iphone|ipod|android|windows phone|opera mini/i', $userAgent) === 1 ? 'mobile' : 'desktop';
    }

    /**
     * @return array{0:string,1:string} [source, referrer host ('' when none or internal)]
     */
    public static function source(string $referrer, string $ownHost, string $utmSource = ''): array
    {
        $host = strtolower((string) parse_url($referrer, PHP_URL_HOST));
        $host = (string) preg_replace('/^www\./', '', $host);
        $own = (string) preg_replace('/^www\./', '', strtolower($ownHost));

        if ($host !== '' && $host === $own) {
            return ['internal', ''];
        }

        $byUtm = self::fromUtm(strtolower(trim($utmSource)));
        if ($byUtm !== null) {
            return [$byUtm, $host];
        }

        if ($host === '') {
            return ['direct', ''];
        }

        $source = match (true) {
            preg_match('/(^|\.)google\.[a-z.]+$/', $host) === 1 && !str_starts_with($host, 'gemini.') => 'google',
            preg_match('/(^|\.)bing\.com$/', $host) === 1                    => 'bing',
            preg_match(self::SEARCH_HOSTS, $host) === 1                      => 'search',
            preg_match('/(^|\.)pinterest\.[a-z.]+$|^pin\.it$/', $host) === 1 => 'pinterest',
            preg_match(self::AI_HOSTS, $host) === 1                          => 'ai',
            preg_match(self::SOCIAL_HOSTS, $host) === 1                      => 'social',
            default                                                          => 'referral',
        };

        return [$source, $host];
    }

    private static function fromUtm(string $utm): ?string
    {
        if ($utm === '') {
            return null;
        }

        return match (true) {
            str_contains($utm, 'pinterest')                          => 'pinterest',
            $utm === 'google'                                        => 'google',
            $utm === 'bing'                                          => 'bing',
            in_array($utm, ['chatgpt.com', 'chatgpt', 'openai', 'perplexity', 'claude'], true) => 'ai',
            in_array($utm, ['facebook', 'instagram', 'reddit', 'tiktok', 'x', 'twitter', 'youtube'], true) => 'social',
            default                                                  => null,
        };
    }
}
