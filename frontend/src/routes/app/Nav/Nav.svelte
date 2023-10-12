<script lang="ts">
	import type { Feed } from "$lib/types.js";
	import { Box, Button } from "@hyvor/design/components";
	import Category from "./Category.svelte";
	import NavFeed from "./NavFeed.svelte";
    const category = ['All', 'Blogs', 'Tech', 'Astro'];

    //import IconPlus from '@hyvor/icons/IconPlus';

    const feeds : Feed[] = [
        {
            name: "Daring Fireball",
            icon_url: "https://daringfireball.net/graphics/favicon-64.png"
        },
        {
            name: "Paul Graham",
            icon_url: "https://www.ycombinator.com/arc/arc.png"
        },
        {
            name: "Food in Jars",
            icon_url: "https://foodinjars.com/wp-content/uploads/2018/10/cropped-fij-favicon-32x32.jpg"
        },
        {
            name: "Supun's Blog",
            icon_url: "https://supun.io/media/gbOn3UUi2Zn2rsUB0FuZJdIFLPR4TTu36nlcti16.png"
        },
        {
            name: "Wired",
            icon_url: "https://www.wired.com/favicon.ico"
        },
        {
            name: "HYVOR Blog",
            icon_url: "https://hyvor.com/blog/media/g23hA3xHTLp3ntj867jQDmvwdr8ARY5ec2wQIOsP.png"
        }
    ]

</script>


<div id="left">

    <Box as="nav">


        <div class="categories">
            {#each category as cat}
                <Category name={cat} />
            {/each}
        </div>

        <div class="feeds">

            {#each feeds as feed}
                <NavFeed {feed} />
            {/each}

        </div>

        <div class="footer">
            <Button small>
                Add Feed <!-- <IconPlus slot="end" /> -->
            </Button>
        </div>
    
    </Box>

</div>


<style>

    #left {
        width: 300px;
        flex-shrink: 0;
        padding: 20px;
        height: 100vh;
    }

    #left > :global(nav) {
        margin-bottom: 15px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .categories {
        padding: 15px 20px 11px;
        border-bottom: 1px solid var(--accent-lightest);
    }

    .feeds {
        padding: 15px 0;
        flex: 1;
    }

    .footer {
        border-top: 1px solid var(--accent-lightest);
        padding: 10px;
        text-align: center;
    }

</style>