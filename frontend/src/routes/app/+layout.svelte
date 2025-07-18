<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import api from '$lib/api';
	import {
		collections,
		items,
		publications,
		selectedCollection,
		selectedPublication,
		loadingInit
	} from './appStore';

	let { children } = $props();

	onMount(async () => {
		loadingInit.set(true);
		try {
			const initData = await api.get('/init');

			collections.set(initData.collections || []);
			publications.set(initData.publications || []);
			items.set(initData.items || []);

			const defaultCollection = initData.selectedCollection ?? initData.collections?.[0] ?? null;
			selectedCollection.set(defaultCollection);
			selectedPublication.set(initData.selectedPublication ?? null);

			if (defaultCollection) {
				goto(`/app/${defaultCollection.slug}`);
			}
		} catch (e) {
			console.error('Initialization failed', e);
		} finally {
			loadingInit.set(false);
		}
	});
</script>

<svelte:head>
	<title>Hyvor Reader</title>
</svelte:head>

{#if $loadingInit}
	<div class="loader-wrap">Loading...</div>
{:else}
	{@render children?.()}
{/if}

<style>
	.loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}
</style>
