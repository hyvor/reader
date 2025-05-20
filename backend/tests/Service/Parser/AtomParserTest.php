<?php

namespace App\Tests\Service\Parser;

use App\Entity\Feed;
use App\Service\Parser\AtomParser;
use App\Service\Parser\ParserException;
use PHPUnit\Framework\TestCase;

class AtomParserTest extends TestCase
{
    public function testValidAtomFeed(): void
    {
        $atomContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>My Atom Feed</title>
    <link href="https://example.org/"/>
    <link rel="self" href="https://example.org/atom.xml"/>
    <subtitle>An Atom formatted feed</subtitle>
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
            <uri>https://example.org/alice</uri>
        </author>
        <category term="tech"/>
    </entry>
</feed>
XML;

        $parser = new AtomParser($atomContent);
        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals('https://example.org/', $feed->getUrl());
        $this->assertEquals('My Atom Feed', $feed->getTitle());
        $this->assertEquals('An Atom formatted feed', $feed->getDescription());
    }

    public function testInvalidAtomStructure(): void
    {
        $invalidContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<wrongRoot>
    <title>Invalid Atom Feed</title>
</wrongRoot>
XML;

        $parser = new AtomParser($invalidContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Invalid Atom feed. <feed> element not found');
        $parser->parse();
    }

    public function testMissingTitle(): void
    {
        $contentWithoutTitle = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <link href="https://example.org/"/>
    <updated>2024-03-20T12:00:00Z</updated>
    <id>urn:uuid:feed</id>
</feed>
XML;

        $parser = new AtomParser($contentWithoutTitle);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: title');
        $parser->parse();
    }

    public function testMissingId(): void
    {
        $contentWithoutId = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Atom Feed</title>
    <link href="https://example.org/"/>
    <updated>2024-03-20T12:00:00Z</updated>
</feed>
XML;

        $parser = new AtomParser($contentWithoutId);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: id');
        $parser->parse();
    }

    public function testMissingUpdated(): void
    {
        $contentWithoutUpdated = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Atom Feed</title>
    <link href="https://example.org/"/>
    <id>urn:uuid:feed</id>
</feed>
XML;

        $parser = new AtomParser($contentWithoutUpdated);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: updated');
        $parser->parse();
    }

    public function testMissingLink(): void
    {
        $contentWithoutLink = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Atom Feed</title>
    <updated>2024-03-20T12:00:00Z</updated>
    <id>urn:uuid:feed</id>
</feed>
XML;

        $parser = new AtomParser($contentWithoutLink);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: link');
        $parser->parse();
    }

    public function testEmptyFeed(): void
    {
        $emptyContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
</feed>
XML;

        $parser = new AtomParser($emptyContent);
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('Required field missing: title');
        $parser->parse();
    }

    public function testInvalidXMLSyntax(): void
    {
        $invalidXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Bad XML Feed</title>
    </wrongClosingTag>
</feed>
XML;

        $this->expectException(ParserException::class);
        new AtomParser($invalidXml);
    }

    public function testEmptyContent(): void
    {
        $this->expectException(ParserException::class);
        new AtomParser('');
    }
} 