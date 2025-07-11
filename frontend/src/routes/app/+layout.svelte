<script lang="ts">
	import {
		collections,
		items,
		publications,
		selectedCollection,
		selectedPublication
	} from './appStore';
	import type { PageData } from './$types';

	interface Props {
		children?: import('svelte').Snippet;
        data: PageData;
	}

	let { children, data }: Props = $props();

    $effect(() => {
        collections.set(data.collections);
        publications.set(data.publications || []);
        items.set(data.items || []);

        const defaultCollection = data.selectedCollection ?? data.collections?.[0] ?? null;
        selectedCollection.set(defaultCollection);

        selectedPublication.set(data.selectedPublication);
    });

</script>

<svelte:head>
	<title>Hyvor Reader</title>
</svelte:head>

{@render children?.()}

<style>
	.loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}
</style>
