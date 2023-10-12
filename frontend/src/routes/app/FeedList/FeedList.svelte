<script lang="ts">
	import { Box } from "@hyvor/design/components";
import type { FeedEntry } from "../../../lib/types";
	import Entry from "./Entry.svelte";

    const feedEntries : FeedEntry[] = [
        {
            url: "https://foodinjars.com/recipe/three-ingredient-fig-jam-for-canning/",
            title: "Three Ingredient Fig Jam for Canning",
            description: "This three ingredient fig jam uses just fresh figs, sugar, and lemon juice to create a shelf stable, delectable preserve.",
            thumbnail_url: 'https://foodinjars.com/wp-content/uploads/2023/09/fig-jam-jars-labeled-horizontal.jpg',
            published_at: new Date(),
        },
        {
            url: 'https://foodinjars.com/recipe/sugar-free-peach-jam/',
            title: 'Sugar Free Peach Jam',
            description: 'Looking for a sugar free peach jam that is spreadable, satisfying, and bursting with summer fruit flavor? You\'ve come to the right place!',
            thumbnail_url: 'https://foodinjars.com/wp-content/uploads/2022/08/four-jars-sugar-free-jam-horizontal.jpg',
            published_at: new Date(),
        },
        {
            url: 'https://foodinjars.com/blog/i-canned-fruit-and-my-jars-are-sticky-help/',
            title: 'I Canned Fruit and My Jars are Sticky! Help!',
            description: 'Finished preserving and your jars are sticky on the outside? Worry not! It\'s a normal part of canning and your fruit is safe to eat!',
            thumbnail_url: 'https://foodinjars.com/wp-content/uploads/2017/08/canned-peaches-True-Value.jpg',
            published_at: new Date(),
        }
    ]

</script>

<div class="feed-list">

    <Box class="box">
        <div class="feed-list-inner">
            {#each feedEntries as entry}
                <Entry {entry} />
            {/each}
        </div>
    </Box>

</div>

<style>

    .feed-list {
        width: 400px;
        padding: 20px 0;
        height: 100vh;
    }

    .feed-list :global(.box) {
        height: 100%;
    }

    .feed-list-inner {
        overflow: auto;
        height: 100%;
        padding: 20px 0;
    }

</style>