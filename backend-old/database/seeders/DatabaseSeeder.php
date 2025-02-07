<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Feed;
use App\Models\FeedSubscription;
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
            ['https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml', 'The New York Times'],
            ['https://news.ycombinator.com/rss', 'Hacker News'],
            ['https://techcrunch.com/feed/', 'TechCrunch'],
            ['https://www.reddit.com/r/programming/.rss', 'Reddit Programming'],
            ['https://www.wired.com/feed/rss', 'Wired']
        ];


        foreach ($defaultFeeds as $feed) {
            $feed = Feed::factory()->create([
                'url' => $feed[0],
                'title' => $feed[1]
            ]);

            FeedSubscription::create([
                'user_id' => 1,
                'feed_id' => $feed->id,
            ]);
        }
    }
}
