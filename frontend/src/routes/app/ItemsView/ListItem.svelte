<script lang="ts">
	import { createBubbler } from 'svelte/legacy';

	const bubble = createBubbler();
	import type { FeedItem } from '../types';

	interface Props {
		item: FeedItem;
		active?: boolean;
	}

	let { item, active = false }: Props = $props();
</script>

<button class="item" class:active onclick={bubble('click')}>
	<div class="title">{item.title}</div>

	{#if item.description}
		<div class="description">
			{item.description}
		</div>
	{/if}
</button>

<style>
	.item {
		display: block;
		text-align: left;
		padding: 10px 25px;
		cursor: pointer;
		width: 100%;
	}
	.item:hover {
		background-color: var(--hover);
	}
	.item.active {
		background-color: var(--accent-lightest);
	}
</style>
