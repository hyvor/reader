<script lang="ts">
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { Button, Dropdown, ActionList, ActionListItem, Loader, Modal, TextInput } from '@hyvor/design/components';
	import {
		collections,
		publications,
		items,
		selectedCollection,
		selectedPublication,
		loadingInit,
		loadingPublications,
		loadingItems
	} from '../appStore';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import type { Collection, Publication, Item } from '../types';
	import { onMount } from 'svelte';
	import api from '$lib/api';
	import ArticleView from '../ArticleView.svelte';

	let showCollections = $state(false);
	let showAddPublicationModal = $state(false);
	let rssUrl = $state('');
	let selectedItem: Item | null = $state(null);
	let currentItemIndex = $derived(
		selectedItem ? $items.findIndex(item => item.id === selectedItem!.id) : -1
	);

	function selectCollection(collection: Collection) {
		goto(`/app/${collection.slug}`);
	}

	function selectPublication(publication?: Publication) {
		if (publication) {
			goto(`/app/${$page.params.collection_slug}/${publication.slug}`);
		} else {
			goto(`/app/${$page.params.collection_slug}`);
		}
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

    function handleNext() {
        if (currentItemIndex < $items.length - 1) {
            selectedItem = $items[currentItemIndex + 1];
        }
    }

    function handlePrevious() {
        if (currentItemIndex > 0) {
            selectedItem = $items[currentItemIndex - 1];
        }
    }

	function getFavicon(url: string, size: number = 14) {
		try {
			const encoded = encodeURIComponent(url);
			return `https://www.google.com/s2/favicons?sz=${size}&domain_url=${encoded}`;
		} catch (_) {
			return '';
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

	function isValidUrl(url: string): boolean {
		const trimmed = url.trim();
		try {
			new URL(trimmed);
			return true;
		} catch (_) {
			return false;
		}
	}

	function handleAdd() {
		const value = rssUrl.trim();
		if (!isValidUrl(value)) return;
		showAddPublicationModal = false;
	}

	onMount(async () => {
		$loadingInit = true;

		try {
			const res = await api.get('/init');
			$collections = res.collections;
		} catch (e) {
			console.error('Initialization failed', e);
		} finally {
			$loadingInit = false;
		}
	});
</script>

<!--<svelte:window on:keydown={handleKeydown} on:click={handleClickOutside} />-->

<main>
	<div class="content">
		<div class="header hds-box">
			<div class="collection-wrap">
				{#if $loadingInit}
					<Loader size="small" />
				{:else}
					<Dropdown bind:show={showCollections}>
						{#snippet trigger()}
							<div class="collection-box">
								{$selectedCollection?.name || 'Select Collection'}
								<IconChevronDown size={12} />
							</div>
						{/snippet}
						{#snippet content()}
							<ActionList selection="single">
								{#each $collections as collection}
									<ActionListItem
										selected={$selectedCollection?.slug === collection.slug}
										on:select={() => selectCollection(collection)}
									>
										{collection.name}
									</ActionListItem>
								{/each}
							</ActionList>
						{/snippet}
					</Dropdown>
				{/if}
			</div>
			<a class="logo" href="/">
				<img
					src="https://hyvor.com/api/public/logo/core.svg"
					alt="Hyvor Reader Logo"
					width="26"
					height="26"
				/>
			</a>
		</div>

		<div class="body">
			<div class="publications hds-box">
				<div class="publications-list">
				{#if $loadingPublications}
					<div class="loader-wrapper">
						<Loader size="small" />
					</div>
				{:else}
                    <button
                        type="button"
                        class="publication {$selectedPublication == null
                            ? 'active'
                            : ''}"
                        onclick={() => selectPublication()}
                    >
                        <span>All publications</span>
                    </button>
					{#each $publications as publication}
						<button
							type="button"
							class="publication {$selectedPublication?.slug === publication.slug
								? 'active'
								: ''}"
							onclick={() => selectPublication(publication)}
						>
							{#if publication.url}
								<img
									src={getFavicon(publication.url)}
									alt={publication.title}
									width="14"
									height="14"
								/>
							{/if}
							<span>{publication.title}</span>
						</button>
					{/each}
				{/if}
				</div>
				<div class="publications-footer">
					<Button class="add-publication-button" on:click={() => { rssUrl = ''; showAddPublicationModal = true; }}>
						{#snippet start()}
							<IconPlus size={12} />
						{/snippet}
						Add publication
					</Button>
				</div>
			</div>

			<div class="feed hds-box">
				{#if selectedItem}
					<ArticleView 
						item={selectedItem} 
						onBackToItems={handleBackToItems}
						onNext={handleNext}
						onPrevious={handlePrevious}
						canGoToNext={currentItemIndex < $items.length - 1}
						canGoToPrevious={currentItemIndex > 0}
					/>
				{:else if $loadingItems}
					<div class="loader-wrapper">
						<Loader size="small" />
					</div>
				{:else}
					<div class="items">
						{#if $items.length > 0}
							{#each $items as item}
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
												&nbsp;&middot;&nbsp; {getRelativeTime(
													item.published_at || item.updated_at || 0
												)}
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
											<img
												src={item.image}
												alt={item.title}
												class="item-image"
											/>
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
				{/if}
			</div>
		</div>
	</div>
</main>


<Modal bind:show={showAddPublicationModal} size="small" title="Add Publication" closeOnOutsideClick={true} closeOnEscape={true}>
	<div class="modal-body">
		<TextInput
			id="rssUrl"
			type="url"
			placeholder="https://example.com/feed.xml"
			autofocus
			bind:value={rssUrl}
			on:keydown={(e: KeyboardEvent) => {
				if (e.key === 'Enter' && isValidUrl(rssUrl)) {
					handleAdd();
				}
			}}
		/>
	</div>

	{#snippet footer()}
		<div class="modal-footer">
			<Button disabled={!isValidUrl(rssUrl)} on:click={handleAdd}>Add</Button>
			<Button color="input" on:click={() => { showAddPublicationModal = false; }}>Cancel</Button>
		</div>
	{/snippet}
</Modal>

<slot />

<style>
	main {
		display: flex;
		flex-direction: column;
		height: 100vh;
	}
	.content {
		flex: 1;
		display: flex;
		flex-direction: column;
		min-height: 0;
		width: 1000px;
		margin: 0 auto;
	}

	.header {
		padding: 15px 44px;
		margin: 15px 0;
		display: flex;
		align-items: center;
	}

	.collection-box {
		font-size: 14px;
		font-weight: 600;
		display: flex;
		align-items: center;
		gap: 5px;
	}

	.collection-wrap {
		flex: 1;
	}

	.body {
		display: flex;
		flex: 1;
		min-height: 0;
		margin-bottom: 15px;
	}

	.publications {
		width: 350px;
		padding: 0;
		margin-right: 20px;
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.publications-list {
		padding: 25px 0 10px 0;
		overflow: auto;
		flex: 1;
		min-height: 0;
	}

	.publications-footer {
		border-top: 1px solid var(--border);
		padding: 10px;
		background: var(--surface);
	}

	.add-publication-button {
		width: 100%;
	}

	.modal-body {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.modal-input {
		width: 100%;
		padding: 10px 12px;
		border: 1px solid var(--border);
		border-radius: 8px;
		background: var(--surface);
		color: var(--text);
	}

	.modal-label {
		font-size: 12px;
		color: var(--text-light);
	}

	.modal-footer {
		display: flex;
		gap: 8px;
		justify-content: flex-end;
	}

	.publication {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 10px 20px;
		font-size: 14px;
		cursor: pointer;
		width: 100%;
		background: none;
		border: none;
		text-align: left;
	}
	.publication:hover,
	.publication.active {
		background-color: var(--hover);
	}

	.loader-wrapper {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100%;
	}

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
