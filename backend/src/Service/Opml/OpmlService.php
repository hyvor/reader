<?php

namespace App\Service\Opml;

class OpmlService
{
    public function __construct(
        private readonly \App\Service\Collection\CollectionService $collectionService,
    )
    {
    }

    public function import(string $content): void
    {
    }

    public function export(string $title): string
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

        $collections = $this->collectionService->getUserCollections(1);
        foreach ($collections as $collection) {
            $outline = $dom->createElement('outline');
            $outline->setAttribute('title', $collection->getName());
            $outline->setAttribute('text', $collection->getName());

            foreach($collection->getPublications() as $publication) {
                $pubOutline = $dom->createElement('outline');
                $pubOutline->setAttribute('type', 'rss');
                $pubOutline->setAttribute('text', $publication->getTitle());
                $pubOutline->setAttribute('title', $publication->getTitle());
                $pubOutline->setAttribute('xmlUrl', $publication->getUrl());
                $outline->appendChild($pubOutline);
            }

            $body->appendChild($outline);
        }

        $opml->appendChild($body);

        return $dom->saveXML();
    }
} 
