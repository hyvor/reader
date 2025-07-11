<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import IconFilter from '@hyvor/icons/IconFilter';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import {
		collections,
		publications,
		selectedCollection,
		selectedPublication,
		items as itemsStore
	} from '../appStore';
	import api from '../../../lib/api';
	import type { Collection, Publication, Item } from '../types';
	import ArticleView from '../ArticleView.svelte';

	let items = $derived($itemsStore);
	let selectedItem: Item | null = $state(null);
	let currentArticleIndex = $state(-1);
	let showCollectionDropdown = $state(false);
	let showPublicationDropdown = $state(false);

	function selectCollection(collection: Collection) {
		selectedCollection.set(collection);

		api
			.get('/publications', { collection_id: collection.uuid })
			.then((res) => {
				publications.set(res.publications);
			})
			.catch((err) => {
				console.error('Failed to fetch publications:', err);
			});

		api
			.get('/items', { collection_id: collection.uuid })
			.then((res) => {
				itemsStore.set(res.items);
			})
			.catch((err) => {
				console.error('Failed to fetch items:', err);
			});

		selectedPublication.set(null);
		selectedItem = null;
		showCollectionDropdown = false;
	}

	function selectPublication(publication: Publication | null) {
		selectedPublication.set(publication);

		if (publication === null) {
			if ($selectedCollection) {
				api
					.get('/items', { collection_id: $selectedCollection.uuid })
					.then((res) => {
						itemsStore.set(res.items);
					})
					.catch((err) => {
						console.error('Failed to fetch all items:', err);
					});
			}
		} else {
			api
				.get('/items', { publication_id: publication.uuid })
				.then((res) => {
					itemsStore.set(res.items);
				})
				.catch((err) => {
					console.error('Failed to fetch items:', err);
				});
		}
		selectedItem = null;
		showPublicationDropdown = false;
	}

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

	function handleClickOutside(event: Event) {
		const target = event.target as HTMLElement;
		if (!target.closest('.dropdown-container')) {
			showCollectionDropdown = false;
			showPublicationDropdown = false;
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

<svelte:window on:keydown={handleKeydown} on:click={handleClickOutside} />

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
		<!-- <div class="header">
			<div class="collection">
				<div class="dropdown-container">
					<Button
						color="input"
						variant="invisible"
						onclick={() => (showCollectionDropdown = !showCollectionDropdown)}
					>
						{$selectedCollection?.name || 'Select Collection'}
						{#snippet end()}
							<IconChevronDown size={10} />
						{/snippet}
					</Button>

					{#if showCollectionDropdown}
						<div class="dropdown-menu">
							{#each $collections as collection}
								<button
									class="dropdown-item"
									class:active={$selectedCollection?.uuid === collection.uuid}
									onclick={() => selectCollection(collection)}
								>
									{collection.name}
								</button>
							{/each}
						</div>
					{/if}
				</div>
			</div>
			<div class="feeds-filter">
				<div class="dropdown-container">
					<Button
						color="input"
						variant="invisible"
						onclick={() => (showPublicationDropdown = !showPublicationDropdown)}
					>
						{#snippet start()}
							<IconFilter size={12} />
						{/snippet}
						{$selectedPublication?.title || 'All Publications'}
					</Button>

					{#if showPublicationDropdown}
						<div class="dropdown-menu">
							<button
								class="dropdown-item"
								class:active={$selectedPublication === null}
								onclick={() => selectPublication(null)}
							>
								All Publications
							</button>
							{#each $publications as publication}
								<button
									class="dropdown-item"
									class:active={$selectedPublication?.uuid === publication.uuid}
									onclick={() => selectPublication(publication)}
								>
									{publication.title || 'Untitled'}
								</button>
							{/each}
						</div>
					{/if}
				</div>
			</div>
		</div> -->
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
