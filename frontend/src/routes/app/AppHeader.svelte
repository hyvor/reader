<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { collections, publications, selectedCollection, selectedPublication } from './appStore';
	import Dropdown from './Dropdown.svelte';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';
	import type { Collection, Publication } from './types';

	interface Props {
		onCollectionSelect: (collection: Collection) => void;
		onPublicationSelect: (publication: Publication | null) => void;
		isArticleView?: boolean;
		onBackToItems?: () => void;
	}

	let { onCollectionSelect, onPublicationSelect, isArticleView = false, onBackToItems }: Props = $props();

	let showCollectionDropdown = $state(false);
	let showPublicationDropdown = $state(false);

	let selectedCollectionText = $derived($selectedCollection?.name || '');
	let selectedPublicationText = $derived($selectedPublication?.title || 'All Publications');

	function handleCollectionSelect(collection: Collection) {
		onCollectionSelect(collection);
		showCollectionDropdown = false;
	}

	function handlePublicationSelect(publication: Publication | null) {
		onPublicationSelect(publication);
		showPublicationDropdown = false;
	}

	function handleClickOutside(event: Event) {
		const target = event.target as HTMLElement;
		if (!target.closest('.dropdown-container')) {
			showCollectionDropdown = false;
			showPublicationDropdown = false;
		}
	}
</script>

<svelte:window on:click={handleClickOutside} />

<header class="box-header">
	{#if isArticleView}
		<Button variant="invisible" onclick={onBackToItems} class="back-button">
			{#snippet start()}
				<IconArrowLeft size={16} />
			{/snippet}
			Back to items
		</Button>
		<div></div>
	{:else}
		<Dropdown 
			label={selectedCollectionText}
			isOpen={showCollectionDropdown}
			onToggle={() => showCollectionDropdown = !showCollectionDropdown}
		>
			{#each $collections as collection}
				<Button 
					variant={$selectedCollection?.uuid === collection.uuid ? 'fill' : 'invisible'}
					onclick={() => handleCollectionSelect(collection)}
					class="dropdown-item"
				>
					{collection.name}
				</Button>
			{/each}
		</Dropdown>
		
		<Dropdown 
			label={selectedPublicationText}
			isOpen={showPublicationDropdown}
			onToggle={() => showPublicationDropdown = !showPublicationDropdown}
		>
			<Button 
				variant={$selectedPublication === null ? 'fill' : 'invisible'}
				onclick={() => handlePublicationSelect(null)}
				class="dropdown-item"
			>
				All Publications
			</Button>
			{#each $publications as publication}
				<Button 
					variant={$selectedPublication?.uuid === publication.uuid ? 'fill' : 'invisible'}
					onclick={() => handlePublicationSelect(publication)}
					class="dropdown-item"
				>
					{publication.title || 'Untitled'}
				</Button>
			{/each}
		</Dropdown>
	{/if}
</header>

<style>
	.box-header {
		padding: 16px 20px;
		border-bottom: 1px solid var(--accent-lightest);
		background: var(--bg);
		display: flex;
		justify-content: space-between;
		align-items: center;
		flex-shrink: 0;
	}

	:global(.back-button) {
		display: flex;
		align-items: center;
		gap: 8px;
	}
</style> 
