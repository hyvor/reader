<script lang="ts">
	import { collections, publications, selectedCollection, selectedPublication, items as itemsStore } from './appStore';
	import api from '../../lib/api';
	import type { Collection, Publication, Item } from './types';
	import ListItem from './ListItem.svelte';
	import AppHeader from './AppHeader.svelte';
	import ArticleView from './ArticleView.svelte';

	let items = $derived($itemsStore);
	let selectedItem: Item | null = $state(null);
	let isInitialLoad = $state(true);

	$effect(() => {
		if ($selectedCollection && isInitialLoad) {
			isInitialLoad = false;
		}
	});

	function selectCollection(collection: Collection) {
		if (isInitialLoad) {
			selectedCollection.set(collection);
			return;
		}

		selectedCollection.set(collection);
		
		api.get('/publications', { collection_id: collection.uuid })
			.then((res) => {
				publications.set(res.publications);
			})
			.catch((err) => {
				console.error('Failed to fetch publications:', err);
			});
		
		api.get('/items', { collection_id: collection.uuid })
			.then((res) => {
				itemsStore.set(res.items);
			})
			.catch((err) => {
				console.error('Failed to fetch items:', err);
			});
		
		selectedPublication.set(null);
		selectedItem = null;
	}

	function selectPublication(publication: Publication | null) {
		selectedPublication.set(publication);
		
		if (publication === null) {
			api.get('/items', { collection_id: $selectedCollection!.uuid })
				.then((res) => {
					itemsStore.set(res.items);
				})
				.catch((err) => {
					console.error('Failed to fetch all items:', err);
				});
		} else {
			api.get('/items', { publication_id: publication.uuid })
				.then((res) => {
					itemsStore.set(res.items);
				})
				.catch((err) => {
					console.error('Failed to fetch items:', err);
				});
		}
		selectedItem = null;
	}

	function handleItemClick(item: Item) {
		selectedItem = item;
	}

	function handleOpenItem(item: Item) {
		window.open(item.url, '_blank');
	}

	function handleBackToItems() {
		selectedItem = null;
	}
</script>

<div class="app-container">
	<div class="content-box">
		<AppHeader 
			onCollectionSelect={selectCollection}
			onPublicationSelect={selectPublication}
			isArticleView={!!selectedItem}
			onBackToItems={handleBackToItems}
		/>

		<div class="content-area">
			{#if selectedItem}
				<ArticleView 
					item={selectedItem}
				/>
			{:else if items.length > 0}
				<div class="items-grid">
					{#each items as item}
						<ListItem 
							{item} 
							onclick={() => handleItemClick(item)}
							onOpenClick={handleOpenItem}
						/>
					{/each}
				</div>
			{:else}
				<div class="empty-state">
					<h3>No items found</h3>
					<p>
						{#if $selectedPublication}
							No items available for the selected publication.
						{:else if $selectedCollection}
							No items available in the selected collection.
						{:else}
							Please select a collection or publication to view items.
						{/if}
					</p>
				</div>
			{/if}
		</div>
	</div>
</div>

<style>
	.app-container {
		flex: 1;
		display: flex;
		justify-content: center;
		padding: 20px;
		background: var(--bg);
		height: 100vh;
		overflow: hidden;
	}

	.content-box {
		width: 50%;
		max-width: 800px;
		min-width: 500px;
		background: var(--bg);
		border-radius: 12px;
		box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
		overflow: hidden;
		display: flex;
		flex-direction: column;
		max-height: calc(100vh - 40px);
	}



	.content-area {
		flex: 1;
		overflow-y: auto;
		padding: 20px;
	}

	.items-grid {
		display: flex;
		flex-direction: column;
		gap: 16px;
	}

	.empty-state {
		text-align: center;
		padding: 40px 20px;
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
