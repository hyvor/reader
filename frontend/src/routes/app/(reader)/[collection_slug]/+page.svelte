<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import api from '$lib/api';
	import { collections, publications, selectedCollection, loadingPublications } from '../../appStore';

	onMount(async () => {
		const { collection_slug } = $page.params;

		selectedCollection.set($collections.find((c) => c.slug === collection_slug) ?? null);
		loadingPublications.set(true);
		try {
			const res = await api.get('/publications', { collection_slug });
			publications.set(res.publications);

			const firstPublication = res.publications?.[0];
			if (firstPublication) {
				goto(`/app/${collection_slug}/${firstPublication.slug}`);
			}
		} catch (e) {
			console.error('Failed to fetch publications', e);
		} finally {
			loadingPublications.set(false);
		}
	});
</script>

<div class="loader-wrap">Loading publications...</div>

<style>
	.loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 100%;
		height: 100%;
	}
</style>
