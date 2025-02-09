<script lang="ts">
	import { NavLink } from '@hyvor/design/components';
	import type { Feed } from '../types';
	import { page } from '$app/stores';
	import DomainIcon from '$lib/Components/DomainIcon.svelte';

	interface Props {
		feed: Feed;
	}

	let { feed }: Props = $props();
</script>

<NavLink href={'/app/feed/' + feed.id} active={$page.url.pathname === '/app/feed/' + feed.id}>
	{#snippet start()}
		<span class="icon">
			<DomainIcon url={feed.url} />
		</span>
	{/snippet}

	{feed.title}
</NavLink>

<style>
	.icon {
		display: inline-flex;
		align-items: center;
	}
</style>
