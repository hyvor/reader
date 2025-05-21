<?php

namespace App\Tests\Service\Parser;

use App\Service\Parser\Types\Feed;
use App\Service\Parser\JsonFeedParser;
use App\Service\Parser\ParserException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonFeedParser::class)]
class JsonFeedParserTest extends TestCase
{
    public function test_valid_json_feed(): void
    {
        $jsonContent = (string) json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'My Example Feed',
            'home_page_url' => 'https://example.org/',
            'feed_url' => 'https://example.org/feed.json',
            'description' => 'A detailed description of my feed',
            'items' => [
                [
                    'id' => '1',
                    'title' => 'First Post',
                    'content_html' => '<p>Hello World</p>',
                    'url' => 'https://example.org/posts/1',
                    'date_published' => '2024-03-20T12:00:00Z',
                    'authors' => [
                        ['name' => 'John Doe', 'url' => 'https://example.org/john']
                    ],
                    'tags' => ['tech', 'news']
                ]
            ]
        ]);

        $parser = new JsonFeedParser($jsonContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals('https://example.org/', $feed->homepage_url);
        $this->assertEquals('My Example Feed', $feed->title);
        $this->assertEquals('A detailed description of my feed', $feed->description);
    }

    public function test_invalid_json(): void
    {
        $invalidJson = '{title: "Invalid JSON Feed",}';

        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Invalid JSON');
        new JsonFeedParser($invalidJson);
    }

    public function test_missing_version(): void
    {
        $contentWithoutVersion = (string) json_encode([
            'title' => 'JSON Feed',
            'home_page_url' => 'https://example.org/'
        ]);

        $parser = new JsonFeedParser($contentWithoutVersion);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: version');
        $parser->parse();
    }

    public function test_missing_title(): void
    {
        $contentWithoutTitle = (string) json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'home_page_url' => 'https://example.org/'
        ]);

        $parser = new JsonFeedParser($contentWithoutTitle);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: title');
        $parser->parse();
    }

    public function test_empty_feed(): void
    {
        $emptyContent = json_encode([]);

        $parser = new JsonFeedParser($emptyContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: version');
        $parser->parse();
    }

    public function test_invalid_item_structure(): void
    {
        $contentWithInvalidItem = json_encode([
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => 'Feed with Invalid Item',
            'home_page_url' => 'https://example.org/',
            'items' => [
                'not-an-object',
                ['id' => '1', 'url' => 'https://example.org/1'], // Missing required content
                ['content_html' => '<p>Content</p>'] // Missing required url
            ]
        ]);

        $parser = new JsonFeedParser($contentWithInvalidItem);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals('https://example.org/', $feed->homepage_url);
        $this->assertEquals('Feed with Invalid Item', $feed->title);
    }

    public function test_empty_content(): void
    {
        $this->expectException(ParserException::class);
        new JsonFeedParser('');
    }
} 