<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import api from '../../lib/api';
	import { collections } from './appStore';
	import { onMount } from 'svelte';
	import Feed from './Feed/Feed.svelte';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	let loading = $state(true);

	onMount(() => {
		api
			.get('/collections')
			.then((res) => {
				collections.set(res.collections);
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
		<!-- <HyvorBar product="core" config={{ name: 'Hyvor Reader' }} /> -->
		<div class="inner">
			<!-- <Nav /> -->
			<Feed />
			<!-- {@render children?.()} -->
		</div>
		<!-- <FeedList />
		<Reader /> -->
	</main>
{/if}

<style>
	main {
		display: flex;
		flex-direction: column;
		height: 100vh;
	}
	.inner {
		display: flex;
		flex: 1;
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
