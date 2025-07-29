<?php

namespace App\Tests\Service\Opml;

use App\Service\Opml\OpmlService;
use App\Tests\Case\KernelTestCase;
use App\Factory\CollectionFactory;
use App\Factory\PublicationFactory;
use Psr\Log\LoggerInterface;

class OpmlServiceTest extends KernelTestCase
{
    private OpmlService $opmlService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $service = $this->container->get(OpmlService::class);
        assert($service instanceof OpmlService);
        $this->opmlService = $service;
        $this->logger = static::getContainer()->get(LoggerInterface::class);
    }

    public function test_export_creates_valid_opml(): void
    {
        $collection1 = CollectionFactory::createOne(['hyvorUserId' => 1]);
        $collection2 = CollectionFactory::createOne(['hyvorUserId' => 1]);

        $publication1 = PublicationFactory::createOne(['collections' => [$collection1]]);
        $publication2 = PublicationFactory::createOne(['collections' => [$collection2]]);

        $title = 'Test OPML Export';
        $opmlContent = $this->opmlService->export($title);

        $this->logger->error('OPML Content: ' . $opmlContent);

        $dom = new \DOMDocument();
        $dom->loadXML($opmlContent);

        $this->assertEquals('2.0', $dom->documentElement->getAttribute('version'), 'OPML version should be 2.0');
        $this->assertEquals('UTF-8', $dom->encoding, 'OPML encoding should be UTF-8');
        $this->assertTrue($dom->validate(), 'OPML should be valid XML');

        $head = $dom->getElementsByTagName('head')->item(0);
        $this->assertNotNull($head, 'OPML head element should exist');
        $titleElement = $head->getElementsByTagName('title')->item(0);
        $this->assertNotNull($titleElement, 'OPML title element should exist');
        $this->assertEquals($title, $titleElement->textContent, 'OPML title should match');

        $body = $dom->getElementsByTagName('body')->item(0);
        $this->assertNotNull($body, 'OPML body element should exist');
        $outlines = $body->getElementsByTagName('outline');
        $this->assertCount(2, $outlines, 'There should be two collection outlines');

        $collectionOutline1 = $outlines->item(0);
        $this->assertEquals($collection1->getName(), $collectionOutline1->getAttribute('title'), 'First collection title should match');
        $this->assertEquals($collection1->getName(), $collectionOutline1->getAttribute('text'), 'First collection text should match');
        $this->assertCount(1, $collectionOutline1->getElementsByTagName('outline'), 'First collection should have one publication outline');
        $publicationOutline1 = $collectionOutline1->getElementsByTagName('outline')->item(0);
        $this->assertEquals('rss', $publicationOutline1->getAttribute('type'), 'Publication outline type should be rss');
        $this->assertEquals($publication1->getTitle(), $publicationOutline1->getAttribute('title'), 'Publication title should match');
        $this->assertEquals($publication1->getUrl(), $publicationOutline1->getAttribute('xmlUrl'), 'Publication URL should match');

        $collectionOutline2 = $outlines->item(1);
        $this->assertEquals($collection2->getName(), $collectionOutline2->getAttribute('title'), 'Second collection title should match');
        $this->assertEquals($collection2->getName(), $collectionOutline2->getAttribute('text'), 'Second collection text should match');
        $this->assertCount(1, $collectionOutline2->getElementsByTagName('outline'), 'Second collection should have one publication outline');
        $publicationOutline2 = $collectionOutline2->getElementsByTagName('outline')->item(0);
        $this->assertEquals('rss', $publicationOutline2->getAttribute('type'), 'Publication outline type should be rss');
        $this->assertEquals($publication2->getTitle(), $publicationOutline2->getAttribute('title'), 'Publication title should match');
        $this->assertEquals($publication2->getUrl(), $publicationOutline2->getAttribute('xmlUrl'), 'Publication URL should match');
    }
}
