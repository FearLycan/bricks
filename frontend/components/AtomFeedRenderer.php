<?php

namespace frontend\components;

use DateTimeImmutable;
use DateTimeInterface;
use XMLWriter;

/**
 * Renders the JSON Feed 1.1 payload built by {@see FeedService} as Atom 1.0 XML
 * (RFC 4287). Atom is consumed by virtually every RSS reader (Feedly, Inoreader,
 * Slack/Discord bots), whereas JSON Feed is supported by a narrower set of modern
 * clients — so /feed and /feed.xml share the same data, expressed in both formats.
 */
final class AtomFeedRenderer
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

        $writer->startElement('feed');
        $writer->writeAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $language = (string)($payload['language'] ?? '');
        if ($language !== '') {
            $writer->writeAttribute('xml:lang', $language);
        }

        $feedUrl = (string)($payload['feed_url'] ?? '');
        $homeUrl = (string)($payload['home_page_url'] ?? '');

        $writer->writeElement('title', (string)($payload['title'] ?? ''));
        if (!empty($payload['description'])) {
            $writer->writeElement('subtitle', (string)$payload['description']);
        }

        // <id> must be a stable, unique IRI. The feed URL serves that purpose.
        if ($feedUrl !== '') {
            $writer->writeElement('id', $feedUrl);

            $writer->startElement('link');
            $writer->writeAttribute('rel', 'self');
            $writer->writeAttribute('type', 'application/atom+xml');
            $writer->writeAttribute('href', $feedUrl);
            $writer->endElement();
        }

        if ($homeUrl !== '') {
            $writer->startElement('link');
            $writer->writeAttribute('rel', 'alternate');
            $writer->writeAttribute('type', 'text/html');
            $writer->writeAttribute('href', $homeUrl);
            $writer->endElement();
        }

        $writer->writeElement('updated', $this->resolveFeedUpdated($payload));

        foreach (($payload['authors'] ?? []) as $author) {
            $this->writeAuthor($writer, (array)$author);
        }

        foreach (($payload['items'] ?? []) as $item) {
            $this->writeEntry($writer, (array)$item, $feedUrl);
        }

        $writer->endElement(); // feed
        $writer->endDocument();

        return $writer->outputMemory();
    }

    /**
     * @param array<string, mixed> $item
     */
    private function writeEntry(XMLWriter $writer, array $item, string $feedUrl): void
    {
        $writer->startElement('entry');

        // Atom requires entry id as IRI. JSON Feed item id is a free string, so we
        // namespace it under the feed URL to guarantee a globally unique value.
        $rawId = (string)($item['id'] ?? '');
        $entryId = $rawId !== ''
            ? rtrim($feedUrl, '/') . '#' . $rawId
            : (string)($item['url'] ?? '');
        $writer->writeElement('id', $entryId);

        $writer->writeElement('title', (string)($item['title'] ?? ''));

        if (!empty($item['url'])) {
            $writer->startElement('link');
            $writer->writeAttribute('rel', 'alternate');
            $writer->writeAttribute('type', 'text/html');
            $writer->writeAttribute('href', (string)$item['url']);
            $writer->endElement();
        }

        $datePublished = (string)($item['date_published'] ?? '');
        if ($datePublished !== '') {
            $writer->writeElement('published', $datePublished);
            $writer->writeElement('updated', $datePublished);
        }

        if (!empty($item['content_text'])) {
            $writer->startElement('summary');
            $writer->writeAttribute('type', 'text');
            $writer->text((string)$item['content_text']);
            $writer->endElement();
        }

        if (!empty($item['content_html'])) {
            $writer->startElement('content');
            $writer->writeAttribute('type', 'html');
            // CDATA keeps our generated HTML readable instead of fully entity-escaped.
            $writer->writeCdata($this->sanitizeForCdata((string)$item['content_html']));
            $writer->endElement();
        }

        if (!empty($item['image'])) {
            $writer->startElement('link');
            $writer->writeAttribute('rel', 'enclosure');
            $writer->writeAttribute('type', $this->guessImageMimeType((string)$item['image']));
            $writer->writeAttribute('href', (string)$item['image']);
            $writer->endElement();
        }

        foreach (($item['tags'] ?? []) as $tag) {
            $writer->startElement('category');
            $writer->writeAttribute('term', (string)$tag);
            $writer->endElement();
        }

        foreach (($item['authors'] ?? []) as $author) {
            $this->writeAuthor($writer, (array)$author);
        }

        $writer->endElement(); // entry
    }

    /**
     * @param array<string, mixed> $author
     */
    private function writeAuthor(XMLWriter $writer, array $author): void
    {
        $name = (string)($author['name'] ?? '');
        if ($name === '') {
            return;
        }
        $writer->startElement('author');
        $writer->writeElement('name', $name);
        if (!empty($author['url'])) {
            $writer->writeElement('uri', (string)$author['url']);
        }
        $writer->endElement();
    }

    /**
     * Atom feed's <updated> is the most recent item timestamp, or now if empty.
     *
     * @param array<string, mixed> $payload
     */
    private function resolveFeedUpdated(array $payload): string
    {
        $latest = null;
        foreach (($payload['items'] ?? []) as $item) {
            $date = (string)($item['date_published'] ?? '');
            if ($date === '') {
                continue;
            }
            if ($latest === null || strcmp($date, $latest) > 0) {
                $latest = $date;
            }
        }

        return $latest ?? (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
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

    /**
     * Split any literal `]]>` sequence so the writer can't accidentally close the
     * surrounding CDATA section early. We never emit `]]>` ourselves, but a
     * defensive split keeps the renderer safe against future content changes.
     */
    private function sanitizeForCdata(string $html): string
    {
        return str_replace(']]>', ']]]]><![CDATA[>', $html);
    }
}
