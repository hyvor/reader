<?php

namespace App\Tests\Service\Parser;

use App\Entity\Feed;
use App\Service\Parser\Parser;
use App\Service\Parser\ParserException;
use App\Service\Parser\Types\FeedType;
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
            'title' => 'My Example Feed',
            'home_page_url' => 'https://example.org/',
            'feed_url' => 'https://example.org/feed.json',
            'description' => 'A detailed description of my feed',
            'icon' => 'https://example.org/icon.png',
            'language' => 'en',
            'items' => [
                [
                    'id' => '1',
                    'title' => 'First Post',
                    'content_html' => '<p>Hello World</p>',
                    'url' => 'https://example.org/posts/1',
                    'date_published' => '2024-03-20T12:00:00Z',
                    'authors' => [
                        ['name' => 'John Doe']
                    ]
                ]
            ]
        ]);

        $parser = new Parser($jsonContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals(FeedType::JSONFEED, $feed->type);
        $this->assertEquals('1.1', $feed->version);
        $this->assertEquals('My Example Feed', $feed->title);
        $this->assertEquals('https://example.org/', $feed->homepageUrl);
        $this->assertEquals('https://example.org/feed.json', $feed->feedUrl);
        $this->assertEquals('A detailed description of my feed', $feed->description);
        $this->assertEquals('https://example.org/icon.png', $feed->icon);
        $this->assertEquals('en', $feed->language);
        
        $this->assertCount(1, $feed->items);
        $item = $feed->items[0];
        $this->assertEquals('1', $item->id);
        $this->assertEquals('First Post', $item->title);
        $this->assertEquals('<p>Hello World</p>', $item->contentHtml);
        $this->assertEquals('https://example.org/posts/1', $item->url);
        $this->assertEquals('2024-03-20T12:00:00Z', $item->publishedAt?->format('Y-m-d\TH:i:s\Z'));
        $this->assertCount(1, $item->authors);
        $this->assertEquals('John Doe', $item->authors[0]->name);
    }

    #[Test]
    public function testParseRSSFeed(): void
    {
        $rssContent = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
    <channel>
        <title>My RSS Feed</title>
        <link>https://example.org/</link>
        <description>A sample RSS feed</description>
        <language>en</language>
        <image>
            <url>https://example.org/icon.png</url>
        </image>
        <item>
            <guid>urn:uuid:1</guid>
            <title>RSS Post</title>
            <link>https://example.org/posts/1</link>
            <description><![CDATA[<p>RSS content here</p>]]></description>
            <pubDate>Wed, 20 Mar 2024 12:00:00 GMT</pubDate>
            <author>jane@example.org (Jane Smith)</author>
        </item>
    </channel>
</rss>
XML;

        $parser = new Parser($rssContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals(FeedType::RSS, $feed->type);
        $this->assertEquals('2.0', $feed->version);
        $this->assertEquals('My RSS Feed', $feed->title);
        $this->assertEquals('https://example.org/', $feed->homepageUrl);
        $this->assertEquals('A sample RSS feed', $feed->description);
        $this->assertEquals('https://example.org/icon.png', $feed->icon);
        $this->assertEquals('en', $feed->language);

        $this->assertCount(1, $feed->items);
        $item = $feed->items[0];
        $this->assertEquals('urn:uuid:1', $item->id);
        $this->assertEquals('RSS Post', $item->title);
        $this->assertEquals('<p>RSS content here</p>', $item->contentHtml);
        $this->assertEquals('https://example.org/posts/1', $item->url);
        $this->assertCount(1, $item->authors);
        $this->assertEquals('Jane Smith', $item->authors[0]->name);
    }

    #[Test]
    public function testParseAtomFeed(): void
    {
        $atomContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>My Atom Feed</title>
    <link href="https://example.org/"/>
    <link rel="self" href="https://example.org/atom.xml"/>
    <subtitle>An Atom formatted feed</subtitle>
    <icon>https://example.org/icon.png</icon>
    <updated>2024-03-20T12:00:00Z</updated>
    <id>urn:uuid:feed</id>
    <entry>
        <id>urn:uuid:1</id>
        <title>Atom Post</title>
        <link href="https://example.org/posts/1"/>
        <content type="html">&lt;p&gt;Atom content here&lt;/p&gt;</content>
        <published>2024-03-20T12:00:00Z</published>
        <author>
            <name>Alice Johnson</name>
        </author>
    </entry>
</feed>
XML;

        $parser = new Parser($atomContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals(FeedType::ATOM, $feed->type);
        $this->assertEquals('My Atom Feed', $feed->title);
        $this->assertEquals('https://example.org/', $feed->homepageUrl);
        $this->assertEquals('An Atom formatted feed', $feed->description);
        $this->assertEquals('https://example.org/icon.png', $feed->icon);

        $this->assertCount(1, $feed->items);
        $item = $feed->items[0];
        $this->assertEquals('urn:uuid:1', $item->id);
        $this->assertEquals('Atom Post', $item->title);
        $this->assertEquals('<p>Atom content here</p>', $item->contentHtml);
        $this->assertEquals('https://example.org/posts/1', $item->url);
        $this->assertCount(1, $item->authors);
        $this->assertEquals('Alice Johnson', $item->authors[0]->name);
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
        $malformedJson = '{title: "Test Feed",}';

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
}
