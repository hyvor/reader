<?php

namespace App\Tests\Service\Parser;

use App\Service\Parser\Types\Feed;
use App\Service\Parser\RSSParser;
use App\Service\Parser\ParserException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RSSParser::class)]
class RSSParserTest extends TestCase
{
    public function testValidRSSFeed(): void
    {
        $rssContent = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>My RSS Feed</title>
        <link>https://example.org/</link>
        <description>A sample RSS feed</description>
        <language>en</language>
        <item>
            <guid>urn:uuid:1</guid>
            <title>RSS Post</title>
            <link>https://example.org/posts/1</link>
            <description><![CDATA[<p>RSS content here</p>]]></description>
            <pubDate>Wed, 20 Mar 2024 12:00:00 GMT</pubDate>
            <author>jane@example.org (Jane Smith)</author>
            <category>Tech</category>
        </item>
    </channel>
</rss>
XML;

        $parser = new RSSParser($rssContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals('https://example.org/', $feed->homepage_url);
        $this->assertEquals('My RSS Feed', $feed->title);
        $this->assertEquals('A sample RSS feed', $feed->description);
    }

    public function testInvalidRSSStructure(): void
    {
        $invalidContent = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <wrongElement>
        <title>Invalid RSS</title>
    </wrongElement>
</rss>
XML;

        $parser = new RSSParser($invalidContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Invalid RSS feed. <channel> element not found');
        $parser->parse();
    }

    public function testMissingTitle(): void
    {
        $contentWithoutTitle = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <link>https://example.org/</link>
        <description>A feed without title</description>
    </channel>
</rss>
XML;

        $parser = new RSSParser($contentWithoutTitle);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: title');
        $parser->parse();
    }

    public function testMissingLink(): void
    {
        $contentWithoutLink = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>RSS Feed</title>
        <description>A feed without link</description>
    </channel>
</rss>
XML;

        $parser = new RSSParser($contentWithoutLink);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: link');
        $parser->parse();
    }

    public function testEmptyChannel(): void
    {
        $emptyContent = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
    </channel>
</rss>
XML;

        $parser = new RSSParser($emptyContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: title');
        $parser->parse();
    }

    public function testInvalidXMLSyntax(): void
    {
        $invalidXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>Bad XML Feed</title>
    </wrongClosingTag>
</rss>
XML;

        $this->expectException(ParserException::class);
        new RSSParser($invalidXml);
    }

    public function testEmptyContent(): void
    {
        $this->expectException(ParserException::class);
        new RSSParser('');
    }
} 