<?php

namespace App\Tests\Service\Parser;

use App\Service\Parser\Types\Feed;
use App\Service\Parser\RssParser;
use App\Service\Parser\ParserException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RssParser::class)]
class RssParserTest extends TestCase
{
    public function test_valid_rss_feed(): void
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

        $parser = new RssParser($rssContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals('https://example.org/', $feed->homepage_url);
        $this->assertEquals('My RSS Feed', $feed->title);
        $this->assertEquals('A sample RSS feed', $feed->description);
    }

    public function test_invalid_rss_structure(): void
    {
        $invalidContent = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <wrongElement>
        <title>Invalid RSS</title>
    </wrongElement>
</rss>
XML;

        $parser = new RssParser($invalidContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Invalid RSS feed. <channel> element not found');
        $parser->parse();
    }

    public function test_missing_title(): void
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

        $parser = new RssParser($contentWithoutTitle);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: title');
        $parser->parse();
    }

    public function test_missing_link(): void
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

        $parser = new RssParser($contentWithoutLink);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: link');
        $parser->parse();
    }

    public function test_empty_channel(): void
    {
        $emptyContent = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
    </channel>
</rss>
XML;

        $parser = new RssParser($emptyContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: title');
        $parser->parse();
    }

    public function test_invalid_xml_syntax(): void
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
        new RssParser($invalidXml);
    }

    public function test_empty_content(): void
    {
        $this->expectException(ParserException::class);
        new RssParser('');
    }
} 