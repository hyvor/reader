<script lang="ts">
	import { Dropdown } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import { collections, publications, selectedCollection, selectedPublication } from '../appStore';
	import { ActionList, ActionListItem } from '@hyvor/design/components';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import type { Collection, Publication } from '../types';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	$effect(() => {
		selectedCollection.set($collections.find(c => c.slug === $page.params.collection_slug) ?? null);
		selectedPublication.set($publications.find(p => p.slug === $page.params.publication_slug) ?? null);
	});

	let showCollections = $state(false);

	function selectCollection(collection: Collection) {
		goto(`/app/${collection.slug}`);
		showCollections = false;
	}

	function togglePublication(publication: Publication) {
		const currentSlug = $page.params.publication_slug;
		const collectionSlug = $page.params.collection_slug;

		if (currentSlug && currentSlug === publication.slug) {
			goto(`/app/${collectionSlug}`);
		} else {
			goto(`/app/${collectionSlug}/${publication.slug}`);
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
</script>

<main>
	<div class="content">
		<div class="header hds-box">
			<div class="collection-wrap">
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
									selected={$selectedCollection?.uuid === collection.uuid}
									on:select={() => selectCollection(collection)}
								>
									{collection.name}
								</ActionListItem>
							{/each}
						</ActionList>
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
				{#each $publications as publication}
					<button
						type="button"
						class="publication { $selectedPublication?.uuid === publication.uuid ? 'active' : '' }"
						onclick={() => togglePublication(publication)}
					>
						{#if publication.url}
							<img src={getFavicon(publication.url)} alt={publication.title} width="14" height="14" />
						{/if}
						<span>{publication.title}</span>
					</button>
				{/each}
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
	}

	.collection-wrap {
		flex: 1;
	}

	.body {
		display: flex;
	}

	.publications {
		width: 350px;
		padding: 25px 0;
		margin-right: 20px;
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
</style>
