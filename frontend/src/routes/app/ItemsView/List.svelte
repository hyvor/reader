<script lang="ts">
	import type { FeedItem } from '../types';
	import ListItem from './ListItem.svelte';

	interface Props {
		items?: FeedItem[];
		currentItem?: FeedItem | null;
	}

	let { items = [], currentItem = $bindable(null) }: Props = $props();

	function handleOnClick(item: FeedItem) {
		currentItem = item;
	}
</script>

<div class="list">
	{#each items as item}
		<ListItem {item} active={item.id === currentItem?.id} on:click={() => handleOnClick(item)} />
	{/each}
</div>

<style>
	.list {
		width: 320px;
		padding: 15px 0;
		border-right: 1px solid var(--border);
		overflow: auto;
	}
</style>
