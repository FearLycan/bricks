# Plan strony głównej BrickAtlas

Wzorzec: **https://www.lego.com/pl-pl**
Docelowa lokalizacja: `frontend/modules/homepage/`
Status startowy: moduł istnieje, ale `HomeController::actionIndex` renderuje generyczną listę setów (duplikat `/lego`). Aplikacja używa `site/index` jako `defaultRoute`.

---

## 1. Cel

Zbudować dynamiczną stronę główną, która:
- jest *witryną* (a nie tylko paginowaną listą) — prowadzi użytkownika do tematów, promocji, nowości i kolekcji,
- aktualizuje się sama wraz z napływem danych (nowe sety, oferty, oceny), bez ręcznej kuracji,
- różnicuje treść dla gości i zalogowanych (osobne bloki personalizacji),
- zastępuje `site/index` jako domyślny route w `frontend/config/main.php`,
- dziedziczy header/footer/CSS z `frontend/views/layouts/main.php` (zgodnie z regułą *match-existing-layout*).

---

## 2. Co odwzorowujemy z lego.com/pl-pl

Strona LEGO.com to ciąg pełnoszerokościowych sekcji z dużymi grafikami, slajderami produktowymi i kafelkami tematów. Kluczowe elementy, które mapujemy 1:1 (lub adaptujemy do naszego modelu — porównywarka cen, nie sklep):

| LEGO.com | Sekcja u nas | Źródło danych |
|---|---|---|
| Top promo bar | (już mamy w `main.php`) | statyczny |
| Hero karuzela 3–5 slajdów | `_hero-carousel.php` | top sety wg `rating` + recent `launch_date`, z dużą okładką z `SetImage` |
| Shop by theme — kafelki | `_theme-tiles.php` | `Theme` (top‑level, status=ACTIVE) z `themeGroup`, hero‑grafika tematu |
| Nowości | `_new-arrivals.php` | `Set` ORDER BY `launch_date DESC`, limit 12 |
| Wkrótce / Coming Soon | `_coming-soon.php` | `Set` WHERE `launch_date > NOW()` |
| Wyprzedaż / Promocje | `_on-sale.php` | sety, dla których `getPromotionalPriceCents()` zwraca wartość (czyli istnieje tańsza oferta od `price`) |
| Bestsellery / Top oceniane | `_top-rated.php` | `Set` ORDER BY `rating DESC` z progiem (np. min. 3 recenzje) |
| LEGO® dla dorosłych | `_for-adults.php` | `Set` WHERE `age >= 18` |
| Spotlight tematu (duży baner) | `_theme-spotlight.php` | rotacyjnie 1 wybrany temat (np. losowy spośród top‑5 wg ilości aktywnych setów) |
| Minifigurki / featured | `_featured-minifigs.php` | `SetMinifig` JOIN `Set` — wybrane minifigurki ostatnich tygodni |
| Personalizacja (zalogowany) | `_personal-wishlist.php`, `_personal-collection.php`, `_recommendations.php` | `Wishlist`, `OwnedSet` + rekomendacje wg tematów z kolekcji |
| Newsletter / call‑to‑register (gość) | `_guest-cta.php` | statyczny dla `Yii::$app->user->isGuest` |
| Footer | (już mamy) | statyczny |

> Sekcje typu „Inspiracje / blog” z LEGO.com pomijamy w MVP — nie mamy contentu redakcyjnego. Wracamy do tematu, gdy pojawi się moduł artykułów (patrz [feature roadmap]).

---

## 3. Kolejność sekcji na stronie

Od góry, dla **gościa**:

1. Hero karuzela
2. Shop by theme — kafelki
3. Nowości (slajder poziomy)
4. Wyprzedaż / Promocje (slajder)
5. Spotlight tematu (duży baner)
6. Top oceniane (slajder)
7. Wkrótce (slajder)
8. LEGO dla dorosłych (slajder)
9. Featured minifigs (kafelki)
10. Guest CTA (rejestracja / newsletter)

Dla **zalogowanego** dodatkowo:

- **Nad** Shop by theme: pasek „Twoja wishlist” (jeżeli niepusta) — 4 ostatnie pozycje + link do `/user/wishlist`,
- **Po** Top oceniane: „Polecane dla Ciebie” — sety z tematów obecnych w kolekcji/wishliście, których jeszcze nie ma w `OwnedSet`,
- **Zamiast** Guest CTA: „Twoja kolekcja w liczbach” (ile setów, ilość klocków, top temat).

---

## 4. Architektura kodu

### 4.1. Warstwa serwisowa (nowa)

`frontend/modules/homepage/services/HomepageContentService.php` — jedyne miejsce z logiką biznesową strony głównej. Każda sekcja = jedna metoda zwracająca DTO (tablicę modeli/wartości):

```
HomepageContentService::getHeroSlides(): array
HomepageContentService::getThemeTiles(int $limit = 12): array
HomepageContentService::getNewArrivals(int $limit = 12): array
HomepageContentService::getOnSale(int $limit = 12): array
HomepageContentService::getTopRated(int $limit = 12): array
HomepageContentService::getComingSoon(int $limit = 12): array
HomepageContentService::getForAdults(int $limit = 12): array
HomepageContentService::getThemeSpotlight(): ?Theme
HomepageContentService::getFeaturedMinifigs(int $limit = 8): array
HomepageContentService::getPersonalWishlistPreview(User $user, int $limit = 4): array
HomepageContentService::getPersonalRecommendations(User $user, int $limit = 12): array
HomepageContentService::getPersonalCollectionStats(User $user): array
```

Każde zapytanie:
- używa **aliasów tabel** w `SELECT/WHERE/ORDER BY` (reguła `feedback_mysql_aliases`),
- jest osłonięte cachem Yii (`Yii::$app->cache->getOrSet`) z kluczem zawierającym wariant (gość vs. user_id) i krótkim TTL (np. 5 min dla list, 30 s dla personalizacji),
- zwraca preładowane relacje (`with('mainImageRelation', 'theme', 'setOffers')`), żeby widok nie generował N+1.

Rekomendacje (`getPersonalRecommendations`) — najprostsza heurystyka v1:
- weź `theme_id` z `Wishlist` + `OwnedSet` użytkownika,
- znajdź sety w tych tematach, których użytkownik nie posiada ani nie ma na liście życzeń,
- sortuj po `rating DESC, launch_date DESC`,
- fallback: top oceniane globalnie, jeśli brak danych behavioralnych.

### 4.2. Controller

`frontend/modules/homepage/controllers/HomeController.php` zostaje, ale `actionIndex` chudnie do:
1. wywołania `HomepageContentService` dla wszystkich sekcji,
2. doklejenia bloków personalizacji jeśli `!Yii::$app->user->isGuest`,
3. `return $this->render('index', $viewData)`.

Bez zapytań, bez `SetSearch`, bez `ListView`. Wszystkie dane wstrzykiwane jako gotowe listy.

### 4.3. Widoki (partials)

```
frontend/modules/homepage/views/home/
    index.php                       # spina sekcje
    _hero-carousel.php
    _theme-tiles.php
    _new-arrivals.php
    _on-sale.php
    _top-rated.php
    _coming-soon.php
    _for-adults.php
    _theme-spotlight.php
    _featured-minifigs.php
    _section-slider.php             # reusable slider (Swiper/Bootstrap carousel) — przyjmuje tytuł, listę setów, link „Zobacz wszystkie"
    _set-card.php                   # ujednolicona karta seta (zastępuje obecny _item.php)
    _minifig-card.php
    _personal-wishlist.php
    _personal-recommendations.php
    _personal-collection-stats.php
    _guest-cta.php
```

Zasady widoków (reguła `feedback_no_logic_in_views`):
- żadnych zapytań, żadnych `find()`,
- tylko prezentacja danych przekazanych z kontrolera,
- każda sekcja sama wycisza się, jeśli dostała pustą listę (`if (empty($items)) return;`).

### 4.4. Styl

Nowy plik `frontend/web/css/homepage.css` (rejestrowany przez `AppAsset` warunkowo lub dołączany w `index.php` przez `$this->registerCssFile()`). Bazujemy na klasach `bricks-*` z `main.php` żeby zachować spójność, dodajemy:
- `bricks-hero` (gradient, padding, CTA),
- `bricks-section` (sekcja pełnoszerokościowa z kontenerem wewnątrz),
- `bricks-set-slider` (poziomy slider — Swiper lub natywny `scroll-snap`),
- `bricks-theme-tile` (kafelek tematu z obrazkiem i overlay).

Slider: preferowany **Swiper.js** (sprawdzony, łatwe sterowanie strzałkami, lazy load); fallback — CSS `scroll-snap` bez JS, gdyby Swiper okazał się zbyt ciężki.

### 4.5. Cache i wydajność

| Sekcja | Klucz cache | TTL |
|---|---|---|
| hero, theme-tiles, theme-spotlight | `homepage.{section}` | 30 min |
| new-arrivals, coming-soon, for-adults, top-rated | `homepage.{section}` | 10 min |
| on-sale | `homepage.onSale` | 5 min (ceny zmieniają się częściej) |
| featured-minifigs | `homepage.minifigs` | 30 min |
| personal-* | `homepage.personal.{userId}.{section}` | 60 s |

Inwalidacja: nasłuch na eventach `Set::EVENT_AFTER_INSERT/UPDATE`, `SetOffer::EVENT_AFTER_*` → `Yii::$app->cache->delete('homepage.*')` (lub przez `TagDependency`, jeśli backend cache wspiera).

---

## 5. Routing — zmiana defaultRoute

W `frontend/config/main.php`:

```php
'defaultRoute' => 'homepage/home/index',
```

Dodatkowo dodać regułę URL, żeby `/` ładnie się renderował i `homepage` nie pojawiał się w breadcrumbach jako oddzielny segment:

```php
'rules' => [
    '' => 'homepage/home/index',
    ...
],
```

`frontend/views/site/index.php` — zostaje na razie nietknięty (martwy plik), do skasowania w osobnym czyszczącym commicie po potwierdzeniu, że nic do niego nie linkuje.

---

## 6. SEO

Zgodnie z regułą `feedback_seo_files`:

- `frontend/components/SeoHelper.php` — dodać dedykowane tytuły i `metaDescription` dla homepage (klucz np. `SeoHelper::homepage()`),
- `frontend/controllers/SitemapController.php` — sprawdzić, czy `/` jest w sitemap (powinno być, ale potwierdzić po zmianie routingu),
- w `index.php` ustawić:
  - `$this->title` — krótki, marka + USP,
  - `$this->params['metaDescription']`,
  - `$this->params['canonicalUrl']` — wersja językowa,
  - `$this->params['socialImage']` — dedykowana OG image dla homepage,
  - JSON‑LD: `WebSite` ze `SearchAction` (sitelinks searchbox).

---

## 7. Tłumaczenia

Reguła `feedback_translations` + `feedback_translation_files`:

- każdy nowy string przez `T::tr('...')`,
- po napisaniu sekcji — dopisać klucze do `frontend/messages/en/app.php` i `frontend/messages/pl/app.php` (oraz pozostałych języków obsługiwanych w `urlManager.languages`: de, fr, es, it, ja, zh — minimum stub = klucz==wartość angielska, do uzupełnienia później),
- przykładowe klucze: `'New arrivals'`, `'Coming soon'`, `'On sale'`, `'Top rated'`, `'For adults'`, `'Shop by theme'`, `'See all'`, `'Your wishlist'`, `'Recommended for you'`, `'Your collection'`.

---

## 8. Plan iteracyjny

Mimo decyzji o pełnej replice — wdrażamy w fazach, żeby każdy krok dawało się ocenić w przeglądarce:

**Faza 1 — szkielet i fundamenty**
- `HomepageContentService` z metodami zwracającymi puste tablice (interfejs gotowy, treść TBD),
- nowy `index.php` z układem sekcji (placeholdery),
- zmiana `defaultRoute`,
- `homepage.css` z bazowym layoutem,
- usunięcie/przepisanie obecnego `actionIndex`.

**Faza 2 — sekcje katalogowe (gość)**
- Nowości, On sale, Top rated, Coming soon, For adults — wszystkie korzystają z `_section-slider.php`,
- ujednolicona `_set-card.php` (zastępuje obecny `_item.php`, można też podmienić użycia w innych miejscach w osobnym commicie),
- cache + N+1 fix (`with(...)`).

**Faza 3 — hero, theme tiles, spotlight, minifigs**
- Implementacja Swipera dla hero,
- kafelki tematów (z grafikami — jeśli brakuje ich w bazie, dodać fallback placeholder),
- spotlight rotujący raz dziennie (key cache uwzględnia `date('Y-m-d')`).

**Faza 4 — personalizacja zalogowanego**
- Pasek wishlist,
- rekomendacje,
- statystyki kolekcji,
- guest CTA jako odpowiednik dla niezalogowanych.

**Faza 5 — SEO, tłumaczenia, polish**
- Tytuły, metaDescription, OG, JSON‑LD WebSite,
- pełne tłumaczenia we wszystkich plikach `messages/`,
- screenshot test w przeglądarce (mobile + desktop), weryfikacja regresji w `/lego`, `/lego/on-sale`, `/lego/new`.

**Faza 6 — inwalidacja cache + monitoring**
- Hooki na eventach modeli (`Set`, `SetOffer`, `Wishlist`, `OwnedSet`),
- log/metryka czasu renderowania homepage (sprawdzić w `runtime/logs/`).

---

## 9. Lista plików do utworzenia / zmiany

### Nowe pliki

- `frontend/modules/homepage/services/HomepageContentService.php`
- `frontend/modules/homepage/views/home/_hero-carousel.php`
- `frontend/modules/homepage/views/home/_theme-tiles.php`
- `frontend/modules/homepage/views/home/_new-arrivals.php`
- `frontend/modules/homepage/views/home/_on-sale.php`
- `frontend/modules/homepage/views/home/_top-rated.php`
- `frontend/modules/homepage/views/home/_coming-soon.php`
- `frontend/modules/homepage/views/home/_for-adults.php`
- `frontend/modules/homepage/views/home/_theme-spotlight.php`
- `frontend/modules/homepage/views/home/_featured-minifigs.php`
- `frontend/modules/homepage/views/home/_section-slider.php`
- `frontend/modules/homepage/views/home/_set-card.php`
- `frontend/modules/homepage/views/home/_minifig-card.php`
- `frontend/modules/homepage/views/home/_personal-wishlist.php`
- `frontend/modules/homepage/views/home/_personal-recommendations.php`
- `frontend/modules/homepage/views/home/_personal-collection-stats.php`
- `frontend/modules/homepage/views/home/_guest-cta.php`
- `frontend/web/css/homepage.css`

### Zmienione pliki

- `frontend/modules/homepage/controllers/HomeController.php` — `actionIndex` wstrzykuje dane z serwisu, bez `SetSearch`,
- `frontend/modules/homepage/views/home/index.php` — composition sekcji,
- `frontend/modules/homepage/views/home/_item.php` — **do usunięcia** (zastępowany przez `_set-card.php`),
- `frontend/config/main.php` — `defaultRoute`, reguła `'' => 'homepage/home/index'`,
- `frontend/components/SeoHelper.php` — wpis dla homepage,
- `frontend/controllers/SitemapController.php` — weryfikacja entry dla `/`,
- `frontend/messages/{en,pl,de,fr,es,it,ja,zh}/app.php` — nowe klucze.

### Do skasowania w późniejszym czyszczącym commicie

- `frontend/views/site/index.php` (jeśli nic nie linkuje),
- po weryfikacji: stary `_item.php` w `homepage/views/home/` (po zastąpieniu `_set-card.php`).

---

## 10. Otwarte decyzje — do podjęcia w trakcie implementacji

1. **Theme grafiki dla kafelków „Shop by theme”** — czy w `Theme` mamy pole na hero‑grafikę? Jeśli nie — albo dodać kolumnę (migracja), albo użyć obrazka najpopularniejszego seta w temacie jako fallback.
2. **Newsletter dla gościa** — czy w MVP zbieramy maile, czy CTA prowadzi tylko do `/auth/signup`? Druga opcja jest prostsza i nie wymaga modelu `NewsletterSubscriber`.
3. **Hero — czy potrzebuje pola admin‑editable** mimo decyzji „pełna dynamika”? Jeśli zarząd kiedyś będzie chciał wypchnąć konkretny set, wracamy do tabeli `homepage_feature` (patrz: opcja „mix” odrzucona w pytaniu, ale warto trzymać tę furtkę otwartą — zaznaczyć w kodzie miejsce do rozszerzenia).
4. **Swiper vs własny CSS scroll‑snap** — decyzja zapadnie po pierwszym prototypie. Jeśli scroll‑snap wystarczy i działa na mobile — zero dodatkowych assetów.
5. **Inwalidacja cache** — czy backend cache wspiera `TagDependency`? Jeśli używamy file cache, sięgnąć po prefiksy kluczy i `delete()` po wzorcu (niestety bez wsparcia natywnego trzeba list-iterate).

---

## 11. Checklist gotowości (definition of done)

- [ ] `/` w przeglądarce pokazuje pełną stronę homepage (nie listę z paginacją),
- [ ] każda sekcja chowa się, gdy nie ma danych (puste DB nie psuje strony),
- [ ] response time `<300 ms` przy pełnym cache (DB hit `<800 ms`),
- [ ] działa w obu trybach: gość / zalogowany,
- [ ] przeklik mobile + desktop bez horyzontalnego scrolla i CLS,
- [ ] poprawne SEO (tytuł, description, canonical, OG, JSON‑LD WebSite),
- [ ] tłumaczenia w `en`/`pl` (pozostałe języki — stuby),
- [ ] regresja sprawdzona w `/lego`, `/lego/on-sale`, `/lego/new`, `/lego/<slug>` (bo używają tych samych modeli/relacji),
- [ ] `frontend/views/site/index.php` zaznaczone jako kandydat do usunięcia.
