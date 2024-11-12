<script lang="ts">
	import type { FeedItem } from '../types';

	export let item: FeedItem;
	export let active = false;
</script>

<button class="item" class:active on:click>
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
