<?php

namespace App\Tests\Service\Opml;

use App\Service\Opml\OpmlService;
use App\Tests\Case\KernelTestCase;
use App\Factory\PublicationFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(OpmlService::class)]
class OpmlServiceTest extends KernelTestCase
{
    private OpmlService $opmlService;

    protected function setUp(): void
    {
        parent::setUp();
        $mockClient = new MockHttpClient(function (string $method, string $url, array $options = []): MockResponse {
            if (str_contains($url, 'example.com/pub1')) {
                $body = (string) json_encode([
                    'version' => 'https://jsonfeed.org/version/1',
                    'title' => 'Publication 1',
                    'items' => [
                        ['id' => 'i1', 'url' => $url.'/item', 'title' => 'Imported Item']
                    ],
                ]);
                return new MockResponse($body, ['http_code' => 200]);
            }
            if (str_contains($url, 'example.com/pub2')) {
                $body = (string) json_encode([
                    'version' => 'https://jsonfeed.org/version/1',
                    'title' => 'Publication 2',
                    'items' => [
                        ['id' => 'i1', 'url' => $url.'/item', 'title' => 'Imported Item']
                    ],
                ]);
                return new MockResponse($body, ['http_code' => 200]);
            }
            return new MockResponse('', ['http_code' => 200]);
        });
        static::getContainer()->set(HttpClientInterface::class, $mockClient);
        $service = $this->container->get(OpmlService::class);
        $this->assertInstanceOf(OpmlService::class, $service);
        $this->opmlService = $service;
    }

    public function test_import(): void
    {
        $hyvorUserId = 1;
        $collectionService = $this->container->get(\App\Service\Collection\CollectionService::class);
        $this->assertInstanceOf(\App\Service\Collection\CollectionService::class, $collectionService);
        $publicationService = $this->container->get(\App\Service\Publication\PublicationService::class);
        $this->assertInstanceOf(\App\Service\Publication\PublicationService::class, $publicationService);

        $opmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<opml version="2.0">
    <head>
        <title>Test OPML Import</title>
    </head>
    <body>
        <outline title="Test Collection 1" text="Test Collection 1">
            <outline type="rss" title="Publication 1" xmlUrl="http://example.com/pub1"/>
        </outline>
        <outline title="Test Collection 2" text="Test Collection 2">
            <outline type="rss" title="Publication 2" xmlUrl="http://example.com/pub2"/>
        </outline>
    </body>
</opml>
XML;

        $this->opmlService->import($opmlContent, $hyvorUserId);

        $collections = $collectionService->getUserCollections($hyvorUserId);
        $this->assertCount(2, $collections, 'There should be two collections imported');

        $collection1 = $collections[0];
        $this->assertEquals('Test Collection 1', $collection1->getName(), 'First collection name should match');
        $publications1 = $collection1->getPublications();
        $this->assertCount(1, $publications1, 'First collection should have one publication');
        $firstPublication1 = $publications1->first();
        $this->assertInstanceOf(\App\Entity\Publication::class, $firstPublication1);
        $this->assertEquals('Publication 1', $firstPublication1->getTitle(), 'Publication title should match');
        $this->assertEquals('http://example.com/pub1', $firstPublication1->getUrl(), 'Publication URL should match');

        $collection2 = $collections[1];
        $this->assertEquals('Test Collection 2', $collection2->getName(), 'Second collection name should match');
        $publications2 = $collection2->getPublications();
        $this->assertCount(1, $publications2, 'Second collection should have one publication');
        $firstPublication2 = $publications2->first();
        $this->assertInstanceOf(\App\Entity\Publication::class, $firstPublication2);
        $this->assertEquals('Publication 2', $firstPublication2->getTitle(), 'Publication title should match');
        $this->assertEquals('http://example.com/pub2', $firstPublication2->getUrl(), 'Publication URL should match');
    }

    public function test_export(): void
    {
        $hyvorUserId = 1;
        $collectionService = $this->container->get(\App\Service\Collection\CollectionService::class);
        
        $this->assertTrue(method_exists($collectionService, 'createCollection'));
        $collection1 = $collectionService->createCollection($hyvorUserId, 'Test Collection 1');
        $this->assertInstanceOf(\App\Entity\Collection::class, $collection1);
        $collection2 = $collectionService->createCollection($hyvorUserId, 'Test Collection 2');
        $this->assertInstanceOf(\App\Entity\Collection::class, $collection2);

        $publication1 = PublicationFactory::createOne(['collections' => [$collection1]]);
        $publication2 = PublicationFactory::createOne(['collections' => [$collection2]]);

        $title = 'Test OPML Export';
        $opmlContent = $this->opmlService->export($title, $hyvorUserId);

        $dom = new \DOMDocument();
        $dom->loadXML($opmlContent);

        $root = $dom->documentElement;
        $this->assertInstanceOf(\DOMElement::class, $root);
        $this->assertEquals('2.0', $root->getAttribute('version'), 'OPML version should be 2.0');
        $this->assertEquals('UTF-8', $dom->encoding, 'OPML encoding should be UTF-8');

        $head = $dom->getElementsByTagName('head')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $head, 'OPML head element should exist');
        $titleElement = $head->getElementsByTagName('title')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $titleElement, 'OPML title element should exist');
        $this->assertEquals($title, $titleElement->textContent, 'OPML title should match');

        $body = $dom->getElementsByTagName('body')->item(0);
        $this->assertNotNull($body, 'OPML body element should exist');
        
        $xpath = new \DOMXPath($dom);
        $outlines = $xpath->query('//outline[@title and @text and not(@type)]');
        $this->assertNotFalse($outlines);
        $this->assertCount(2, $outlines, 'There should be two collection outlines');

        $collectionOutline1 = $outlines->item(0);
        $this->assertInstanceOf(\DOMElement::class, $collectionOutline1);
        $this->assertEquals($collection1->getName(), $collectionOutline1->getAttribute('title'), 'First collection title should match');
        $this->assertEquals($collection1->getName(), $collectionOutline1->getAttribute('text'), 'First collection text should match');
        $this->assertCount(1, $collectionOutline1->getElementsByTagName('outline'), 'First collection should have one publication outline');
        $publicationOutline1 = $collectionOutline1->getElementsByTagName('outline')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $publicationOutline1);
        $this->assertEquals('rss', $publicationOutline1->getAttribute('type'), 'Publication outline type should be rss');
        $this->assertEquals($publication1->getTitle(), $publicationOutline1->getAttribute('title'), 'Publication title should match');
        $this->assertEquals($publication1->getUrl(), $publicationOutline1->getAttribute('xmlUrl'), 'Publication URL should match');

        $collectionOutline2 = $outlines->item(1);
        $this->assertInstanceOf(\DOMElement::class, $collectionOutline2);
        $this->assertEquals($collection2->getName(), $collectionOutline2->getAttribute('title'), 'Second collection title should match');
        $this->assertEquals($collection2->getName(), $collectionOutline2->getAttribute('text'), 'Second collection text should match');
        $this->assertCount(1, $collectionOutline2->getElementsByTagName('outline'), 'Second collection should have one publication outline');
        $publicationOutline2 = $collectionOutline2->getElementsByTagName('outline')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $publicationOutline2);
        $this->assertEquals('rss', $publicationOutline2->getAttribute('type'), 'Publication outline type should be rss');
        $this->assertEquals($publication2->getTitle(), $publicationOutline2->getAttribute('title'), 'Publication title should match');
        $this->assertEquals($publication2->getUrl(), $publicationOutline2->getAttribute('xmlUrl'), 'Publication URL should match');
    }
}
