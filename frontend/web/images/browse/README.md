# Tile artwork (optional)

Drop a file named `<key>.webp` / `.jpg` / `.jpeg` / `.png` here and the matching
tile on the homepage and on /interests uses it instead of the automatic set
photo. No deploy needed: the file is picked up on the next page render
(homepage cache: `php yii cache/flush-all` to see it at once).

**Format: 800×600 px (4:3), WebP, under 120 KB.** The picture fills the whole
image area of the card; the title is printed below it on white, so text in the
image is not needed. Keep the subject in the centre (the card may crop a few
pixels on the edges). Avoid LEGO logos, minifigures and licensed characters
(trademarks).

| key | homepage | /interests |
|---|---|---|
| on-sale | On sale | On sale |
| new-arrivals | All new arrivals | |
| all-sets | Browse all sets | |
| adults | For adults (18+) | Adults welcome |
| toddlers | For toddlers (1+) | |
| kids | For kids (5–8) | For kids (5–8) |
| tweens | For tweens (9–12) | For tweens (9–12) |
| teens | For teens (13+) | |
| small-builds | Small builds | Small builds |
| big-builds | Big builds | Big builds |
| exclusive | | LEGO Exclusive |
| retiring-soon | | Retiring soon |
| magazines | | Magazine sets |
| polybags | | Polybags |
| gwp | | Gift with purchase |
| seasonal | | Seasonal |

Themes (Star Wars, City, …) take their picture from the theme image field in
the admin panel; without it, from a representative set of that theme.
