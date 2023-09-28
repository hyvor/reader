<script lang="ts">
	import type { Feed } from "../../../lib/types";
    export let feed: Feed;
</script>

<div 
    class="feed"
    class:active={feed.name === 'Food in Jars'}
>

    <span class="icon">
        <img src={feed.icon_url} alt={feed.name} />
    </span>

    <span class="name">{feed.name}</span>

</div>


<style>

    .feed {
        padding: 10px 20px;
        display: flex;
        align-items: center;
        border-left: 3px solid transparent;
    }
    .feed:hover {
        background-color: var(--color-hover);
        cursor: pointer;
    }
    .feed.active {
        background-color: var(--color-accent-lightest);
        border-left-color: var(--color-accent);
    }

    .icon {
        display: inline-flex;
        align-items: center;
    }

    img {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 6px;
    }

</style>