# BrickAtlas (brickatlas.shop) — kontekst dla Claude

Porównywarka cen LEGO na Yii2 advanced (`frontend`, `backend`, `common`, `console`), PHP 8.x, MySQL.
Hosting: MyDevil (FreeBSD). Deploy ręczny (`git pull` na serwerze).

**Zasada nr 1: zanim dopiszesz helper, sprawdź tabelę poniżej.** Większość rzeczy już istnieje.

## Konwencje

- Teksty widoczne dla użytkownika zawsze przez `frontend\components\T::tr('English source', [...])`.
  Źródło po angielsku, tłumaczenia w `frontend/messages/<lang>/app.php`.
  Po dodaniu stringów: `./yii message frontend/config/i18n.php`, potem uzupełnij `pl` (i resztę).
- Języki: `en` (bez prefiksu), `pl`, `de`, `fr`, `es`, `it`, `ja`, `zh` — `codemix/localeurls`,
  lista w `SeoHelper::SUPPORTED_LANGUAGES`, `frontend/config/main.php` i `SitemapController` (trzymaj w sync).
- Endpointy JSON bez języka dopisz do `ignoreLanguageUrlPatterns` w `frontend/config/main.php`. Modale HTML — nie.
- Ceny w bazie w **centach** (int). Formatowanie: `Set::formatAmountFromCents($cents, $currency)`.
- Enumy w `common/enums/*` implementują `BaseEnumInterface` (`label()`, `options()` przez `EnumOptionsTrait`).
- Statusy rekordów: `StatusEnum::ACTIVE` / `INACTIVE`. Zapytania o sety filtrują aktywny temat (`SetSearch::joinActiveTheme`).

## SEO — co już jest (nie duplikuj)

| Potrzeba | Gdzie |
|---|---|
| title / meta description dla każdej strony katalogu | `frontend/components/SeoHelper::build*Title/Description/Intro` |
| canonical | widok ustawia `$this->params['canonicalUrl']`, layout emituje |
| robots | `$this->params['robots']` (domyślnie `index,follow`) |
| hreflang + x-default | automatycznie w `views/layouts/main.php` → `SeoHelper::registerHreflangLinks` |
| rel prev/next | `SeoHelper::registerPaginationLinks` |
| OG / Twitter | layout z `params['socialTitle'|'socialDescription'|'socialImage'|'ogType']` |
| JSON-LD | `common/schema/JsonLdRenderer::render([...nodes])` (`@graph`) |
| — Organization, BreadcrumbList | automatycznie w layoucie (z `params['breadcrumbs']`) |
| — ItemList listingu | `ItemListSchemaFactory::fromDataProvider($dp)` |
| — Product na stronie zestawu | `SetPageSchemaBuilder::build($set, $url)` (AggregateOffer + recenzje własne) |
| sitemap (z alternates językowymi) | `./yii sitemap/generate` → `console/controllers/SitemapController` — nową stronę statyczną dopisz do listy w ~l.105 |
| feedy Atom / RSS / JSON | `FeedController` + `FeedService`, `AtomFeedRenderer`, `Rss2FeedRenderer` |
| teksty skracane / normalizacja | `SeoHelper::truncate`, `SeoHelper::normalizeText` |
| bezwzględny URL | `SeoHelper::buildAbsoluteUrl` |

## Landing page'e katalogu (wzorzec dla nowych)

Trasy w `frontend/config/main.php`, akcje w `frontend/modules/lego/controllers/LegoController.php`:
`/lego`, `/lego/on-sale`, `/lego/magazines`, `/lego/exclusive`, `/lego/retiring-soon`, `/lego/new`,
`/lego/<audience>` (konfiguracja w `SeoHelper::audiencePages()` — dodanie wpisu + regex trasy = nowa strona),
`/lego/tag/<slug>`, `/lego/theme/<slug>[/<sub>]`, `/lego/minifig/<number>`, `/lego/<slug>` (zestaw — **ostatnia, łapie wszystko**).

Nowa strona-hub = akcja w `LegoController` + metoda w `SetSearch` + widok wg wzoru `retiring-soon.php` /
`magazines.php` (hero `views/_partials/_page-hero.php`, `_list.php`, canonical, ItemList, wpis do sitemapy).

## Dane i zapytania

| Potrzeba | Gdzie |
|---|---|
| lista / filtry katalogu | `frontend/models/searches/SetSearch::search($params)` |
| promocje | `SetSearch::searchPromo()` — **tylko oferty w USD** vs `set.price` |
| wycofywane | `SetSearch::searchRetiringSoon($days)`, `Set::RETIRING_SOON_DAYS = 180`, `Set::isRetiringSoon()` |
| ukrywanie posiadanych | `SetSearch::applyHideOwnedFilter` (ustawienie usera) |
| bloki strony głównej (nowości, promo, top, wkrótce, 18+, minifigi, personalizacja) | `frontend/modules/homepage/services/HomepageContentService` |
| podobne zestawy | `frontend/modules/lego/services/RelatedSetsService::getRelatedSets` |
| wydarzenie sezonowe (Halloween, Święta 15.11–1.01, Wielkanoc, Walentynki) | `common/components/SeasonalEventResolver::getActiveEvent()` |
| menu kategorii | `frontend/components/MenuService` |
| FAQ / słownik / zainteresowania (treści statyczne) | `LegoFaq`, `LegoGlossary`, `LegoInterests` |

`Set` — najważniejsze metody: `getBestAlternativeOffer($cur)`, `getLowestAlternativeOfferPriceCents`,
`getPromotionalPriceCents`, `getPromotionalSavingsPercent`, `getFormattedPrice`, `getDisplayMainImageUrl`,
`isReleased`, `isUpcoming`, `getDaysUntilLaunch/Exit`, `isLimitedRun`, `get*Text($default)`,
listy do selectów `getAvailableYearsList/ThemesList/SubthemesList`.
`SetOffer::syncBySetAndStore`, `SetPrice::syncLegoComPrices`, `Theme/Tag/Store::getOrCreate`.
`Wishlist` / `OwnedSet`: `getCurrentUserSetIds()` (cache), `invalidateCurrentUserCache()`.
`SetReview` (recenzje użytkowników, skala 1–10, `STATUS_PUBLISHED`) + analityka profilu/rekomendacje.

## Czego NIE ma (świadomie do zbudowania, nie szukaj)

- **Historii cen** — brak tabeli; `set_offer.price` jest nadpisywane przy sync.
- **Alertów o spadku ceny** — brak tabeli/kolejki/maili.
- Promocji w walutach innych niż USD.

## Konsola / crony

`brickset/sync-sets`, `brickset/sync-recently-updated-sets`, `brickset/sync-instructions`, `brickset/sync-themes`,
`rebrickable/sync-minifigs`, `ali-express-review/sync|fetch`, `set-offer-discovery/queue-matches`,
`set-offer-import/process-pending`, `sitemap/generate`, `set/rebuild-slugs`, `tag/recount-sets`, `theme/recount-sets`.
Integracja AliExpress: `common/components/aliexpress/*` (klient API, resolver linków, matcher, importer).

## Frontend

- Layout: `frontend/views/layouts/main.php` (+ `_categories-dropdown`, `_language-switcher`, `_user-dropdown-menu`).
- Partiale wielokrotnego użytku: `views/_partials/_page-hero.php`, `modules/lego/views/lego/_list.php`, `_item.php`,
  `_banner-retiring.php`, `_banner-upcoming.php`, `modules/homepage/views/home/_section-slider.php`, `_set-card.php`.
- Kafelki strony głównej i /interests: zdjęcie reprezentatywnego zestawu (`HomepageContentService::pickCover`, cache 6 h); własna grafika = plik 800×600 w `frontend/web/images/browse/<key>.*` (lista kluczy w README tam), dla tematów pole `theme.image` w adminie.
- Assety: `frontend/assets/AppAsset.php` (Bootstrap 5, select2, venobox, Chart.js 4 z CDN — gotowe do wykresów).
- CSS per strona rejestrowany w widoku (`registerCssFile('@web/css/x.css', ['depends' => [AppAsset::class]])`).
- `common/components/Html::img`, `common/widgets/Alert`, `common/widgets/InlineScript`.
