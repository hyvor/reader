<script lang="ts">
	import type { Item } from '../types';
	import List from './List.svelte';
	import Reader from './Reader.svelte';
	import { items as itemsStore, selectedPublication } from '../appStore';

	let items = $derived($itemsStore);
	let currentItem: Item | null = $state(null);

	$effect(() => {
		if (items.length > 0) {
			currentItem = items[0];
		}
	});
</script>

<section class="wrap">
	<div class="inner hds-box">
		<List items={items} publication={$selectedPublication} bind:currentItem />
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
