<?php

namespace App\Tests\Service\Parser;

use App\Entity\Feed;
use App\Service\Parser\Parser;
use App\Service\Parser\ParserException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Parser::class)]
class ParserTest extends TestCase
{
    #[Test]
    public function testParseJSONFeed(): void
    {
        $jsonContent = json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'Test Feed',
            'home_page_url' => 'http://example.com',
            'items' => []
        ]);

        $parser = new Parser($jsonContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
    }

    #[Test]
    public function testParseRSSFeed(): void
    {
        $rssContent = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>Test RSS Feed</title>
        <link>http://example.com</link>
        <description>Test Description</description>
    </channel>
</rss>
XML;

        $parser = new Parser($rssContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
    }

    #[Test]
    public function testParseAtomFeed(): void
    {
        $atomContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Test Atom Feed</title>
    <link href="http://example.com"/>
    <updated>2024-03-20T12:00:00Z</updated>
    <id>urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6</id>
</feed>
XML;

        $parser = new Parser($atomContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
    }

    #[Test]
    public function testInvalidContent(): void
    {
        $invalidContent = 'This is neither JSON nor XML';

        $parser = new Parser($invalidContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Content is neither JSON nor valid XML');

        $parser->parse();
    }

    #[Test]
    public function testInvalidXMLFeedType(): void
    {
        $invalidXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<unknown>
    <title>Invalid Feed Type</title>
</unknown>
XML;

        $parser = new Parser($invalidXml);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Unknown feed type. Supported types are RSS, Atom, and JSON Feed');

        $parser->parse();
    }

    #[Test]
    public function testMalformedJSON(): void
    {
        $malformedJson = '{title: "Test Feed",}'; // Invalid JSON syntax

        $parser = new Parser($malformedJson);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Content is neither JSON nor valid XML');

        $parser->parse();
    }

    #[Test]
    public function testMalformedXML(): void
    {
        $malformedXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss>
    <channel>
        <title>Malformed XML Feed</title>
    </channel>
    </wrongClosingTag>
</rss>
XML;

        $parser = new Parser($malformedXml);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Content is neither JSON nor valid XML');

        $parser->parse();
    }

    #[Test]
    public function testEmptyContent(): void
    {
        $parser = new Parser('');
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Content is neither JSON nor valid XML');

        $parser->parse();
    }

    #[Test]
    public function testWithUTF8BOM(): void
    {
        $rssWithBOM = chr(239) . chr(187) . chr(191) . <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>Test RSS Feed with BOM</title>
        <link>http://example.com</link>
        <description>Test Description</description>
    </channel>
</rss>
XML;

        $parser = new Parser($rssWithBOM);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
    }

    #[Test]
    public function testWithWhitespacePrefix(): void
    {
        $xmlWithWhitespace = "\n\r\t" . <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>Test RSS Feed with Whitespace</title>
        <link>http://example.com</link>
        <description>Test Description</description>
    </channel>
</rss>
XML;

        $parser = new Parser($xmlWithWhitespace);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Content is neither JSON nor valid XML');
        $parser->parse();
    }
}
