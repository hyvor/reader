<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import IconFilter from '@hyvor/icons/IconFilter';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import {
		publications as publicationsStore,
		items as itemsStore,
		selectedCollection,
		selectedPublication
	} from '../../appStore';
	import type { Item, Publication } from '../../types';
	import ArticleView from '../../ArticleView.svelte';
	import type { PageData } from './$types';

	interface Props {
		data: PageData;
	}

	let { data }: Props = $props();

	$effect(() => {
		itemsStore.set(data.items);
		publicationsStore.set(data.publications);
	});

	let items = $derived($itemsStore);
	let selectedItem: Item | null = $state(null);
	let currentArticleIndex = $state(-1);

	function handleItemClick(item: Item) {
		const index = items.findIndex((i) => i.id === item.id);
		currentArticleIndex = index;
		selectedItem = item;
	}

	function handleOpenItem(item: Item) {
		window.open(item.url, '_blank');
	}

	function handleBackToItems() {
		selectedItem = null;
		currentArticleIndex = -1;
	}

	function goToPreviousArticle() {
		if (currentArticleIndex > 0) {
			const prevItem = items[currentArticleIndex - 1];
			currentArticleIndex--;
			selectedItem = prevItem;
		}
	}

	function goToNextArticle() {
		if (currentArticleIndex < items.length - 1) {
			const nextItem = items[currentArticleIndex + 1];
			currentArticleIndex++;
			selectedItem = nextItem;
		}
	}

	let canGoToPrevious = $derived(currentArticleIndex > 0);
	let canGoToNext = $derived(currentArticleIndex < items.length - 1);

	function handleKeydown(event: KeyboardEvent) {
		if (!selectedItem) return;

		switch (event.key) {
			case 'ArrowLeft':
				if (canGoToPrevious) {
					event.preventDefault();
					goToPreviousArticle();
				}
				break;
			case 'ArrowRight':
				if (canGoToNext) {
					event.preventDefault();
					goToNextArticle();
				}
				break;
			case 'Escape':
				event.preventDefault();
				handleBackToItems();
				break;
		}
	}

	function getRelativeTime(timestamp: number): string {
		const now = Date.now();
		const diff = now - timestamp * 1000;
		const minutes = Math.floor(diff / 60000);
		const hours = Math.floor(diff / 3600000);
		const days = Math.floor(diff / 86400000);

		if (minutes < 60) return `${minutes}m ago`;
		if (hours < 24) return `${hours}h ago`;
		return `${days}d ago`;
	}
</script>

<svelte:window on:keydown={handleKeydown} />

{#if selectedItem}
	<ArticleView
		item={selectedItem}
		isLoading={false}
		{canGoToPrevious}
		{canGoToNext}
		onPrevious={goToPreviousArticle}
		onNext={goToNextArticle}
		onBackToItems={handleBackToItems}
	/>
{:else}
	<div class="feed hds-box">
		<div class="items">
			{#if items.length > 0}
				{#each items as item}
					<button class="item" onclick={() => handleItemClick(item)}>
						<div class="left">
							<div class="top">
								<img
									src="https://picsum.photos/40?{item.publication_title}"
									alt="Publication Logo"
									class="logo"
								/>
								<div class="publication-name">
									{item.publication_title || 'Unknown'}
								</div>
								<div class="publish-time">
									&nbsp;&middot;&nbsp; {getRelativeTime(item.published_at || item.updated_at || 0)}
								</div>
							</div>
							<div class="title">
								{item.title}
							</div>
							<div class="description">
								{item.summary || 'No description available'}
							</div>
							<div class="open-button">
								<Button
									size="small"
									color="input"
									onclick={(event: Event) => {
										event.stopPropagation();
										handleOpenItem(item);
									}}
								>
									Open
									{#snippet end()}
										<IconBoxArrowUpRight size={12} />
									{/snippet}
								</Button>
							</div>
						</div>
						{#if item.image}
							<div class="featured-image">
								<img src={item.image} alt={item.title} class="item-image" />
							</div>
						{/if}
					</button>
				{/each}
			{:else}
				<div class="empty-state">
					<h3>No items found</h3>
					<p>
						{#if $selectedPublication}
							No items available for the selected publication.
						{:else if $selectedCollection}
							No items available in the selected collection.
						{:else}
							Please select a collection to view items.
						{/if}
					</p>
				</div>
			{/if}
		</div>
	</div>
{/if}

<style>
	.feed {
		width: 100%;
		display: flex;
		flex-direction: column;
	}
	.header {
		padding: 15px 30px;
		border-bottom: 1px solid var(--border);
		display: flex;
		align-items: center;
		gap: 20px;
	}
	.collection {
		flex: 1;
	}
	.items {
		padding: 15px 24px;
		overflow: auto;
	}
	.item {
		padding: 15px 20px;
		border-radius: 20px;
		cursor: pointer;
		text-align: left;
		display: flex;
		width: 100%;
		align-items: center;
		gap: 10px;
		background: none;
		border: none;
		margin-bottom: 8px;
		transition: background-color 0.2s ease;
	}
	.item:hover {
		background-color: var(--hover);
	}
	.left {
		flex: 1;
	}
	img.logo {
		width: 15px;
		height: 15px;
		border-radius: 50%;
	}
	.top {
		flex: 1;
		display: flex;
		align-items: center;
		gap: 4px;
		margin-bottom: 3px;
	}
	.publication-name {
		font-size: 12px;
		font-weight: 500;
	}
	.publish-time {
		font-size: 12px;
		color: var(--text-light);
	}
	.title {
		font-weight: 600;
		margin-bottom: 4px;
		line-height: 1.3;
	}
	.description {
		font-size: 14px;
		color: var(--text-light);
		margin-top: 2px;
		line-height: 1.4;
	}
	.featured-image img {
		max-width: 200px;
		max-height: 100px;
		border-radius: 10px;
		object-fit: cover;
	}
	.open-button {
		margin-top: 8px;
	}

	.dropdown-container {
		position: relative;
	}

	.dropdown-menu {
		position: absolute;
		top: 100%;
		left: 0;
		min-width: 200px;
		background: white;
		border: 1px solid var(--border);
		border-radius: 8px;
		box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
		z-index: 1000;
		max-height: 300px;
		overflow-y: auto;
		margin-top: 6px;
		padding: 8px;
	}

	.dropdown-item {
		width: 100%;
		text-align: left;
		padding: 12px 16px;
		background: none;
		border: none;
		border-radius: 6px;
		cursor: pointer;
		transition: background-color 0.2s ease;
		margin-bottom: 2px;
	}

	.dropdown-item:hover {
		background-color: var(--hover);
	}

	.dropdown-item.active {
		background-color: var(--accent-lightest);
		font-weight: 500;
	}

	.dropdown-item:last-child {
		margin-bottom: 0;
	}

	.empty-state {
		text-align: center;
		padding: 60px 20px;
		color: var(--text-light);
	}

	.empty-state h3 {
		margin: 0 0 12px 0;
		font-size: 18px;
		color: var(--text);
	}

	.empty-state p {
		margin: 0;
		font-size: 14px;
		line-height: 1.5;
	}
</style>
