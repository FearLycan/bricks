<?php
/**
 * Pinterest RSS feed (FeedController::actionPinterest). One <item> per set: Pinterest takes
 * the title, the description and the image (enclosure / media:content) and links the pin to <link>.
 *
 * @var string                $title
 * @var string                $slug
 * @var \common\models\Set[]  $items
 * @var int                   $date
 */

use yii\helpers\Url;

$x = static fn(?string $s): string => htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$https = static fn(string $u): string => str_starts_with($u, '//') ? 'https:' . $u : (string)preg_replace('~^http://~i', 'https://', $u);
$pubDate = gmdate(DATE_RSS, $date);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
    <title><?= $x($title) ?></title>
    <link><?= $x(Url::home(true)) ?></link>
    <description><?= $x($title) ?></description>
    <language>en</language>
    <lastBuildDate><?= $pubDate ?></lastBuildDate>
<?php foreach ($items as $set):
    $image = $set->getMainImage();
    if ($image === null || $image->url === '') {
        continue;
    }
    $name = trim($set->name . ' ' . $set->number);
    $route = ['/lego/lego/view', 'modules' => 'lego', 'slug' => $set->slug];
    $canonical = Url::to($route, true);
    $link = Url::to($route + ['utm_source' => 'pinterest', 'utm_medium' => 'social', 'utm_campaign' => $slug], true);
    $theme = $set->theme?->name;
    $what = (int)$set->pieces . '-piece LEGO' . ($theme ? ' ' . $theme : '') . ' set' . ($set->year ? ' from ' . (int)$set->year : '');
    $desc = $set->name . ' (' . $set->number . '), a ' . $what . '. Compare store prices with the official LEGO price on BrickAtlas.';
    $img = $https($image->url);
?>
    <item>
        <title><?= $x(mb_strlen($name) > 100 ? rtrim(mb_substr($name, 0, 99)) . '…' : $name) ?></title>
        <link><?= $x($link) ?></link>
        <guid isPermaLink="true"><?= $x($canonical) ?></guid>
        <description><?= $x($desc) ?></description>
        <pubDate><?= $pubDate ?></pubDate>
        <enclosure url="<?= $x($img) ?>" type="image/jpeg" length="0"/>
        <media:content url="<?= $x($img) ?>" medium="image"/>
    </item>
<?php endforeach; ?>
</channel>
</rss>
