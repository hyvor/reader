<script lang="ts">
	import { run } from 'svelte/legacy';

	import api from '$lib/api';
	import type { Feed, FeedItem } from '../types';
	import List from './List.svelte';
	import Reader from './Reader.svelte';

	type FetchType = 'all' | 'saved' | 'feed';

	interface Props {
		type?: FetchType;
		feedId?: number | null;
	}

	let { type = 'all', feedId = null }: Props = $props();

	let feed: Feed = $state({} as Feed);
	let items: FeedItem[] = $state([]);
	let currentItem: FeedItem | null = $state(null);

	function fetchItems(type: FetchType, feedId: number | null) {
		api.get('items', { type, feed_id: feedId }).then((res) => {
			items = res.items;
			feed = res.feed;
			currentItem = items[0];
		});
	}
	run(() => {
		fetchItems(type, feedId);
	});
</script>

<section class="wrap">
	<div class="inner hds-box">
		<List {items} {feed} bind:currentItem />
		{#if currentItem}
			<Reader item={currentItem} />
		{/if}
	</div>
</section>

<style>
	.wrap {
		flex: 1;
		padding: 15px 0;
		padding-right: 15px;
	}
	.inner {
		display: flex;
		height: 100%;
	}
</style>
