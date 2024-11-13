<script lang="ts">
	import api from '$lib/api';
	import type { FeedItem } from '../types';
	import List from './List.svelte';
	import Reader from './Reader.svelte';

	type FetchType = 'all' | 'saved' | 'feed';

	export let type: FetchType = 'all';
	export let feedId: number | null = null;

	$: fetchItems(type, feedId);

	let items: FeedItem[] = [];
	let currentItem: FeedItem | null = null;

	function fetchItems(type: FetchType, feedId: number | null) {
		api.get('items', { type, feed_id: feedId }).then((res) => {
			items = res;
			currentItem = items[0];
		});
	}
</script>

<section class="wrap">
	<div class="inner hds-box">
		<List {items} bind:currentItem />
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
