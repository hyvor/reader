<script lang="ts">
	import DomainIcon from '$lib/Components/DomainIcon.svelte';
	import type { Publication, Item } from '../types';
	import ListItem from './ListItem.svelte';

	interface Props {
		publication?: Publication | null;
		items?: Item[];
		currentItem?: Item | null;
	}

	let { publication, items = [], currentItem = $bindable(null) }: Props = $props();

	function handleOnClick(item: Item) {
		currentItem = item;
	}
</script>

<div class="list">
	{#if publication}
		<div class="publication-info">
			<DomainIcon url={publication.url} size={26} />
			<div class="title-url">
				<span class="title">
					{publication.title}
				</span>
				<div class="url">
					{publication.url}
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
	.publication-info {
		padding: 15px 25px;
		border-bottom: 1px solid var(--border);
		margin-bottom: 10px;
		display: flex;
		align-items: center;
		gap: 6px;
		border-top-left-radius: 20px;
		cursor: pointer;
	}
	.publication-info:hover {
		background-color: var(--hover);
	}
	.publication-info .title {
		font-weight: 600;
	}
	.publication-info .title-url {
		display: flex;
		flex-direction: column;
		flex: 1;
		min-width: 0;
	}
	.publication-info .url {
		font-size: 12px;
		color: var(--text-light);
		white-space: nowrap;
		text-overflow: ellipsis;
		overflow: hidden;
	}
</style>
