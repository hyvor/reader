<script lang="ts">
	import { Dropdown } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import { collections, selectedCollection, publications, selectedPublication, items } from '../appStore';
	import api from '../../../lib/api';
	import type { Collection, Publication } from '../types';
	import { onMount } from 'svelte';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	$effect(() => {
		if ($selectedCollection) {
			fetchCollectionData($selectedCollection);
		}
	});

	function fetchCollectionData(collection: Collection) {
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
				items.set(res.items);
			})
			.catch((err) => {
				console.error('Failed to fetch items:', err);
			});
	}

	function selectCollection(collection: Collection) {
		selectedCollection.set(collection);
	}

	function selectPublication(publication: Publication | null) {
		selectedPublication.set(publication);

		if (publication === null) {
			if ($selectedCollection) {
				api
					.get('/items', { collection_id: $selectedCollection.uuid })
					.then((res) => {
						items.set(res.items);
					})
					.catch((err) => {
						console.error('Failed to fetch all items:', err);
					});
			}
		} else {
			api
				.get('/items', { publication_id: publication.uuid })
				.then((res) => {
					items.set(res.items);
				})
				.catch((err) => {
					console.error('Failed to fetch items:', err);
				});
		}
	}
</script>

<main>
	<div class="content">
		<div class="header hds-box">
			<div class="collection-wrap">
				<Dropdown>
					{#snippet trigger()}
						<div class="collection-box">
							{$selectedCollection?.name || 'Select Collection'}
							<IconChevronDown size={12} />
						</div>
					{/snippet}
					{#snippet content()}
						<div class="collection-dropdown">
							{#each $collections as collection}
								<button
									class="collection-item"
									class:active={$selectedCollection?.uuid === collection.uuid}
									onclick={() => selectCollection(collection)}
								>
									{collection.name}
								</button>
							{/each}
						</div>
					{/snippet}
				</Dropdown>
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
				<div class="publications-header">
					<h3>Publications</h3>
				</div>
				
				<button 
					class="publication"
					class:active={$selectedPublication === null}
					onclick={() => selectPublication(null)}
				>
					<span>All Publications</span>
				</button>

				{#each $publications as publication}
					<button 
						class="publication"
						class:active={$selectedPublication?.uuid === publication.uuid}
						onclick={() => selectPublication(publication)}
					>
						<img 
							src="https://picsum.photos/32?{publication.title}" 
							alt={publication.title} 
							width="14" 
							height="14" 
						/>
						<span>{publication.title}</span>
					</button>
				{/each}

				{#if $publications.length === 0}
					<div class="empty-publications">
						<p>No publications available</p>
					</div>
				{/if}
			</div>

			{@render children?.()}
		</div>
	</div>
</main>

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
		cursor: pointer;
		padding: 8px 12px;
		border-radius: 6px;
		transition: background-color 0.2s ease;
	}

	.collection-box:hover {
		background-color: var(--hover);
	}

	.collection-wrap {
		flex: 1;
	}

	.collection-dropdown {
		padding: 8px;
		min-width: 200px;
	}

	.collection-item {
		width: 100%;
		text-align: left;
		padding: 12px 16px;
		background: none;
		border: none;
		border-radius: 6px;
		cursor: pointer;
		transition: background-color 0.2s ease;
		margin-bottom: 2px;
		font-size: 14px;
	}

	.collection-item:hover {
		background-color: var(--hover);
	}

	.collection-item.active {
		background-color: var(--accent-lightest);
		font-weight: 500;
	}

	.body {
		display: flex;
	}

	.publications {
		width: 350px;
		padding: 0;
		margin-right: 20px;
		display: flex;
		flex-direction: column;
	}

	.publications-header {
		padding: 20px 20px 10px 20px;
		border-bottom: 1px solid var(--border);
	}

	.publications-header h3 {
		margin: 0;
		font-size: 16px;
		font-weight: 600;
		color: var(--text);
	}

	.publication {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 12px 20px;
		font-size: 14px;
		cursor: pointer;
		border: none;
		background: none;
		text-align: left;
		width: 100%;
		transition: background-color 0.2s ease;
	}

	.publication:hover {
		background-color: var(--hover);
	}

	.publication.active {
		background-color: var(--accent-lightest);
		font-weight: 500;
	}

	.publication-icon {
		font-size: 14px;
	}

	.empty-publications {
		padding: 20px;
		text-align: center;
		color: var(--text-light);
		font-size: 14px;
	}
</style>
