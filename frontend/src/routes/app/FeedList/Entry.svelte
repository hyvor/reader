<script lang="ts">
	import type { FeedEntry } from "../../../lib/types";
    export let entry: FeedEntry;
</script>

<div 
    class="entry"
    class:active={entry.url === 'https://foodinjars.com/recipe/three-ingredient-fig-jam-for-canning/'}
>

    <div class="title">{entry.title}</div>

    <div class="description">
        {entry.description}
    </div>

    <img src={entry.thumbnail_url} alt={entry.title} />

</div>

<style>

    .entry {
        padding: 15px 20px;
        border-left: 5px solid transparent;
    }
    .entry:hover {
        background-color: var(--color-hover);
        cursor: pointer;
    }
    .entry.active {
        /* background-color: var(--color-accent);
        color: var(--color-text-white);
        --color-text-light: #ddd; */
        /* background-color: var(--color-accent-lightest); */

        background-color: var(--color-accent-lightest);
        border-left-color: var(--color-accent);
    }

    img {
        max-width: 100%;
        border-radius: 20px;
        max-height: 300px;
        margin-top: 10px;
    }

    .title {
        font-weight: 600;
        font-size: 16px;
    }
    
    .description {
        color: var(--color-text-light);
        line-height: 1.2;
        margin-top: 5px;
    }

</style>