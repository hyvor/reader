<script lang="ts">
	import { run } from 'svelte/legacy';

	import api from '$lib/api';
	import type { FeedItem } from '../types';
	import List from './List.svelte';
	import Reader from './Reader.svelte';

	type FetchType = 'all' | 'saved' | 'feed';

	interface Props {
		type?: FetchType;
		feedId?: number | null;
	}

	let { type = 'all', feedId = null }: Props = $props();


	let items: FeedItem[] = $state([]);
	let currentItem: FeedItem | null = $state(null);

	function fetchItems(type: FetchType, feedId: number | null) {
		api.get('items', { type, feed_id: feedId }).then((res) => {
			items = res;
			currentItem = items[0];
		});
	}
	run(() => {
		fetchItems(type, feedId);
	});
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
