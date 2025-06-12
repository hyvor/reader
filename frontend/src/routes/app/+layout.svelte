<script lang="ts">
	import { Loader, HyvorBar } from '@hyvor/design/components';
	import api from '../../lib/api';
	import { collections, items, publications, selectedCollection, selectedPublication } from './appStore';
	import { onMount } from 'svelte';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	let loading = $state(true);

	onMount(() => {
		api
			.get('/init')
			.then((res) => {
				collections.set(res.collections);
                publications.set(res.publications || []);
                items.set(res.items || []);
                selectedCollection.set(res.selectedCollection);
                selectedPublication.set(res.selectedPublication);
			})
			.catch((err) => {
				console.error(err);
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

<svelte:head>
	<title>Hyvor Reader</title>
</svelte:head>

{#if loading}
	<div class="loader-wrap">
		<Loader full />
	</div>
{:else}
	<main>
		<HyvorBar product="core" config={{ name: 'Hyvor Reader' }} />
		<div class="content">
			{@render children?.()}
		</div>
	</main>
{/if}

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
		width: 900px;
		margin: 0 auto;
		padding: 15px;
	}
	.loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}
</style>
