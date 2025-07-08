<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import api from '../../lib/api';
	import {
		collections,
		items,
		publications,
		selectedCollection,
		selectedPublication
	} from './appStore';
	import { onMount } from 'svelte';
	import { get } from 'svelte/store';

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
				const col = res.selectedCollection ?? res.collections?.[0] ?? null;
				selectedCollection.set(col);
				const pub = res.selectedPublication ?? (res.publications?.[0] ?? null);
				selectedPublication.set(pub);
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
