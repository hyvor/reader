<script lang="ts">
	import Nav from './Nav/Nav.svelte';
	import { Loader } from '@hyvor/design/components';
	import api from '../../lib/api';
	import { feeds } from './appStore';
	import { onMount } from 'svelte';

	let loading = true;

	onMount(() => {
		api
			.get('/init')
			.then((res) => {
				feeds.set(res.feeds);
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
		<Nav />
		<slot />
		<!-- <FeedList />
		<Reader /> -->
	</main>
{/if}

<style>
	main {
		display: flex;
		height: 100vh;
	}
	.loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}
</style>
