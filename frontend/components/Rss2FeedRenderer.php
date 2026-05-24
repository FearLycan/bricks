<?php

namespace frontend\components;

use DateTimeImmutable;
use DateTimeInterface;
use XMLWriter;

/**
 * Renders the JSON Feed 1.1 payload built by {@see FeedService} as RSS 2.0 XML.
 *
 * Why RSS 2.0 and not Atom: basic RSS validators (and a long tail of legacy
 * readers / Discord-Slack RSS bots) only recognise `application/rss+xml` as a
 * "feed". Atom 1.0 is technically more standardised but loses compatibility
 * with that group. JSON Feed already covers the modern-API niche at /feed,
 * so /feed.xml takes the broadest-compat XML lane.
 *
 * Emits standard RSS 2.0 plus three widely-supported extensions:
 *   - atom:link rel=self      → required by validators for self-discovery
 *   - content:encoded         → carries rich HTML (description stays plain text)
 *   - dc:creator              → author name without the email RSS' <author> needs
 */
final class Rss2FeedRenderer
{
    /**
     * @param array<string, mixed> $payload JSON Feed 1.1 payload from FeedService.
     */
    public function render(array $payload): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');
        $writer->startDocument('1.0', 'UTF-8');

        $writer->startElement('rss');
        $writer->writeAttribute('version', '2.0');
        $writer->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $writer->writeAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $writer->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $writer->writeAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');

        $writer->startElement('channel');

        $title = (string)($payload['title'] ?? '');
        $homeUrl = (string)($payload['home_page_url'] ?? '');
        $description = (string)($payload['description'] ?? '');
        $feedUrl = (string)($payload['feed_url'] ?? '');
        $language = (string)($payload['language'] ?? '');

        $writer->writeElement('title', $title);
        $writer->writeElement('link', $homeUrl);
        $writer->writeElement('description', $description !== '' ? $description : $title);

        if ($language !== '') {
            $writer->writeElement('language', $language);
        }

        if ($feedUrl !== '') {
            $writer->startElement('atom:link');
            $writer->writeAttribute('href', $feedUrl);
            $writer->writeAttribute('rel', 'self');
            $writer->writeAttribute('type', 'application/rss+xml');
            $writer->endElement();
        }

        $writer->writeElement('lastBuildDate', $this->toRfc822($this->resolveFeedUpdated($payload)));
        $writer->writeElement('generator', 'BrickAtlas Feed Service');

        foreach (($payload['items'] ?? []) as $item) {
            $this->writeItem($writer, (array)$item);
        }

        $writer->endElement(); // channel
        $writer->endElement(); // rss
        $writer->endDocument();

        return $writer->outputMemory();
    }

    /**
     * @param array<string, mixed> $item
     */
    private function writeItem(XMLWriter $writer, array $item): void
    {
        $writer->startElement('item');

        $writer->writeElement('title', (string)($item['title'] ?? ''));

        if (!empty($item['url'])) {
            $writer->writeElement('link', (string)$item['url']);
        }

        // GUID: not a permalink, because event ids ("instructions-set-9") are
        // arbitrary strings — only the linked URL is the permalink.
        if (!empty($item['id'])) {
            $writer->startElement('guid');
            $writer->writeAttribute('isPermaLink', 'false');
            $writer->text((string)$item['id']);
            $writer->endElement();
        }

        if (!empty($item['date_published'])) {
            $writer->writeElement('pubDate', $this->toRfc822((string)$item['date_published']));
        }

        if (!empty($item['content_text'])) {
            $writer->startElement('description');
            $writer->writeCdata($this->sanitizeForCdata((string)$item['content_text']));
            $writer->endElement();
        }

        if (!empty($item['content_html'])) {
            $writer->startElement('content:encoded');
            $writer->writeCdata($this->sanitizeForCdata((string)$item['content_html']));
            $writer->endElement();
        }

        foreach (($item['authors'] ?? []) as $author) {
            $name = (string)(((array)$author)['name'] ?? '');
            if ($name === '') {
                continue;
            }
            // RSS <author> wants `email (Name)` per spec; without an email we use
            // Dublin Core's <dc:creator>, which all major readers display.
            $writer->writeElement('dc:creator', $name);
        }

        foreach (($item['tags'] ?? []) as $tag) {
            $writer->writeElement('category', (string)$tag);
        }

        if (!empty($item['image'])) {
            // Media RSS (yahoo mrss) instead of <enclosure>: enclosure is reserved by
            // the RSS spec for audio/video and triggers podcast-specific validators
            // (Apple Podcasts categories, episode length, etc.). <media:content> with
            // medium="image" is the standard way to advertise image attachments to
            // RSS readers (YouTube, Flickr and Tumblr feeds use the same pattern).
            $imageUrl = (string)$item['image'];
            $imageType = $this->guessImageMimeType($imageUrl);

            $writer->startElement('media:content');
            $writer->writeAttribute('url', $imageUrl);
            $writer->writeAttribute('medium', 'image');
            $writer->writeAttribute('type', $imageType);
            $writer->endElement();

            // Most readers display <media:thumbnail> in the article preview card.
            $writer->startElement('media:thumbnail');
            $writer->writeAttribute('url', $imageUrl);
            $writer->endElement();
        }

        $writer->endElement(); // item
    }

    /**
     * Most recent item timestamp (ISO 8601), or now if the feed is empty.
     *
     * @param array<string, mixed> $payload
     */
    private function resolveFeedUpdated(array $payload): string
    {
        $latest = null;
        foreach (($payload['items'] ?? []) as $item) {
            $date = (string)(((array)$item)['date_published'] ?? '');
            if ($date === '') {
                continue;
            }
            if ($latest === null || strcmp($date, $latest) > 0) {
                $latest = $date;
            }
        }

        return $latest ?? (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
    }

    private function toRfc822(string $iso): string
    {
        try {
            return (new DateTimeImmutable($iso))->format(DateTimeInterface::RSS);
        } catch (\Throwable) {
            return (new DateTimeImmutable())->format(DateTimeInterface::RSS);
        }
    }

    private function guessImageMimeType(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $ext = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            default       => 'image/jpeg',
        };
    }

    private function sanitizeForCdata(string $value): string
    {
        return str_replace(']]>', ']]]]><![CDATA[>', $value);
    }
}
