<script lang="ts">
	import { NavLink } from '@hyvor/design/components';
	import type { Feed } from '../types';
	import { page } from '$app/stores';

	interface Props {
		feed: Feed;
	}

	let { feed }: Props = $props();

	const domain = feed.url.match(/https?:\/\/([^/]+)/)[1];
	const iconUrl = `https://icons.duckduckgo.com/ip3/${domain}.ico`;
</script>

<NavLink href={'/app/feed/' + feed.id} active={$page.url.pathname === '/app/feed/' + feed.id}>
	{#snippet start()}
		<span class="icon">
			<img src={iconUrl} alt={feed.title} />
		</span>
	{/snippet}

	{feed.title}
</NavLink>

<style>
	.icon {
		display: inline-flex;
		align-items: center;
	}

	img {
		width: 18px;
		height: 18px;
		border-radius: 50%;
	}
</style>
