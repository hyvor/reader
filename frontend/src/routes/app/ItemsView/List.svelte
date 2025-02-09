<script lang="ts">
	import DomainIcon from '$lib/Components/DomainIcon.svelte';
	import type { Feed, FeedItem } from '../types';
	import ListItem from './ListItem.svelte';

	interface Props {
		feed?: Feed;
		items?: FeedItem[];
		currentItem?: FeedItem | null;
	}

	let { feed, items = [], currentItem = $bindable(null) }: Props = $props();

	function handleOnClick(item: FeedItem) {
		currentItem = item;
	}
</script>

<div class="list">
	{#if feed}
		<div class="feed-info">
			<DomainIcon url={feed.url} size={26} />
			<div class="title-url">
				<span class="title">
					{feed.title}
				</span>
				<div class="url">
					{feed.url}
				</div>
			</div>
		</div>
	{/if}
	{#each items as item}
		<ListItem {item} active={item.id === currentItem?.id} on:click={() => handleOnClick(item)} />
	{/each}
</div>

<style>
	.list {
		width: 320px;
		border-right: 1px solid var(--border);
		overflow: auto;
	}
	.feed-info {
		padding: 15px 25px;
		border-bottom: 1px solid var(--border);
		margin-bottom: 10px;
		display: flex;
		align-items: center;
		gap: 6px;
		border-top-left-radius: 20px;
		cursor: pointer;
	}
	.feed-info:hover {
		background-color: var(--hover);
	}
	.feed-info .title {
		font-weight: 600;
	}
	.feed-info .title-url {
		display: flex;
		flex-direction: column;
		flex: 1;
		min-width: 0;
	}
	.feed-info .url {
		font-size: 12px;
		color: var(--text-light);
		white-space: nowrap;
		text-overflow: ellipsis;
		overflow: hidden;
	}
</style>
