<?php

use App\Domain\Feed\Parser\RssParser;

it('parses xml', function () {
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Example Feed</title>
        <link>http://www.example.com</link>
        <description>Example feed description</description>
        <language>en</language>
        <item>
            <title>Item 1</title>
            <link>http://www.example.com/item1</link>
            <description>Item 1 description</description>
        </item>
        <item>
            <title>Item 2</title>
            <link>http://www.example.com/item2</link>
            <description>Item 2 description</description>
        </item>
    </channel>
</rss>
XML;

    $parser = new RssParser($xml);
    $feed = $parser->parse();
});
