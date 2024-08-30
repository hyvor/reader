<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Feed;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultFeeds = [
            ['https://daringfireball.net/feeds/json', 'Daring Fireball'],
            ['https://feeds.bbci.co.uk/news/world/rss.xml', 'BBC News'],
            ['https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml', 'The New York Times'],
            ['https://news.ycombinator.com/rss', 'Hacker News'],
            ['https://techcrunch.com/feed/', 'TechCrunch'],
            ['https://www.reddit.com/r/programming/.rss', 'Reddit Programming'],
            ['https://www.wired.com/feed/rss', 'Wired']
        ];

        foreach ($defaultFeeds as $feed) {
            Feed::factory()->create([
                'url' => $feed[0],
                'title' => $feed[1]
            ]);
        }
    }
}
