<?php

namespace App\Service\Opml;

use App\Service\Collection\CollectionService;
use App\Service\Fetch\FetchService;
use App\Service\Publication\PublicationService;

class OpmlService
{
    public function __construct(
        private readonly CollectionService $collectionService,
        private readonly PublicationService $publicationService,
        private readonly FetchService $fetchService,
    )
    {
    }

    public function import(string $content, int $hyvorUserId): void
    {
        $dom = new \DOMDocument();
        $dom->loadXML($content);

        $xpath = new \DOMXPath($dom);
        $outlines = $xpath->query('//outline[@title and @text and not(@type)]');
        if ($outlines === false) {
            return;
        }
        foreach ($outlines as $outline) {
            if (!($outline instanceof \DOMElement)) {
                continue;
            }
            $collectionName = (string) $outline->getAttribute('title');
            $collection = $this->collectionService->createCollection($hyvorUserId, $collectionName);

            foreach ($outline->childNodes as $child) {
                if ($child instanceof \DOMElement && $child->tagName === 'outline') {
                    $publicationUrl = (string) $child->getAttribute('xmlUrl');
                    $inspection = $this->fetchService->inspectFeed($publicationUrl);
                    $this->publicationService->addPublication($collection, $inspection);
                }
            }
        }
    }

    public function export(string $title, int $hyvorUserId): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $opml = $dom->createElement('opml');
        $opml->setAttribute('version', '2.0');
        $dom->appendChild($opml);

        $head = $dom->createElement('head');
        $titleElement = $dom->createElement('title', $title);
        $head->appendChild($titleElement);
        $opml->appendChild($head);

        $body = $dom->createElement('body');

        $collections = $this->collectionService->getUserCollections($hyvorUserId);
        foreach ($collections as $collection) {
            $outline = $dom->createElement('outline');
            $outline->setAttribute('title', (string) $collection->getName());
            $outline->setAttribute('text', (string) $collection->getName());

            foreach($collection->getPublications() as $publication) {
                $pubOutline = $dom->createElement('outline');
                $pubOutline->setAttribute('type', 'rss');
                $pubOutline->setAttribute('text', (string) ($publication->getTitle() ?? ''));
                $pubOutline->setAttribute('title', (string) ($publication->getTitle() ?? ''));
                $pubOutline->setAttribute('xmlUrl', $publication->getUrl());
                $outline->appendChild($pubOutline);
            }

            $body->appendChild($outline);
        }

        $opml->appendChild($body);

        return (string) $dom->saveXML();
    }
} 
