<script lang="ts">
	import FeedList from './FeedList/FeedList.svelte';
	import Nav from './Nav/Nav.svelte';
	import Reader from './Reader/Reader.svelte';

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
	<Loader />
{:else}
	<main>
		<Nav />
		<FeedList />
		<Reader />
	</main>
{/if}

<style>
	main {
		display: flex;
	}
</style>
