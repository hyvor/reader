<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Publication } from '../types';
	import { selectedCollection, publications, selectedPublication, items } from '../appStore';
	import api from '../../../lib/api';
	import DomainIcon from '$lib/Components/DomainIcon.svelte';

	let currentPublications = $derived($publications);
	let currentSelectedPublication = $derived($selectedPublication);

	function selectPublication(publication: Publication) {
		selectedPublication.set(publication);
		api.get('/items', { publication_id: publication.uuid })
			.then((res) => {
				items.set(res.items);
			})
			.catch((err) => {
				console.error('Failed to fetch items:', err);
			});
	}
</script>

<div class="publication-list">
	{#if $selectedCollection}
		<div class="header">
			<h2>{$selectedCollection.name}</h2>
			<p class="count">{currentPublications.length} publications</p>
		</div>

		<div class="list">
			{#each currentPublications as publication}
				<Button 
					variant={currentSelectedPublication?.uuid === publication.uuid ? 'filled' : 'ghost'}
					onclick={() => selectPublication(publication)}
					class="publication-item"
				>
					{#snippet start()}
						<span class="icon">
							<DomainIcon url={publication.url} />
						</span>
					{/snippet}

					<div class="content">
						<div class="title">{publication.title || 'Untitled'}</div>
						<div class="meta">
							{publication.subscribers} subscribers
						</div>
					</div>
				</Button>
			{/each}
		</div>
	{:else}
		<div class="empty">
			<p>Select a collection to view its publications</p>
		</div>
	{/if}
</div>

<style>
	.publication-list {
		width: 350px;
		border-right: 1px solid var(--accent-lightest);
		background: var(--bg);
		display: flex;
		flex-direction: column;
	}

	.header {
		padding: 20px;
		border-bottom: 1px solid var(--accent-lightest);
	}

	.header h2 {
		margin: 0 0 4px 0;
		font-size: 18px;
		font-weight: 600;
	}

	.count {
		margin: 0;
		color: var(--text-light);
		font-size: 14px;
	}

	.list {
		flex: 1;
		overflow-y: auto;
		padding: 10px;
	}

	:global(.publication-item) {
		width: 100%;
		justify-content: flex-start;
		margin-bottom: 8px;
		text-align: left;
		padding: 12px;
	}

	.icon {
		display: inline-flex;
		align-items: center;
		margin-right: 12px;
	}

	.content {
		flex: 1;
		min-width: 0;
	}

	.title {
		font-weight: 500;
		line-height: 1.3;
		margin-bottom: 4px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.meta {
		font-size: 12px;
		color: var(--text-light);
	}

	.empty {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 40px;
		text-align: center;
		color: var(--text-light);
	}
</style> 