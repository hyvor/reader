<script lang="ts">
	import { NavLink } from '@hyvor/design/components';
	import type { Feed } from '../types';
	import { page } from '$app/stores';

	export let feed: Feed;

	const domain = feed.url.match(/https?:\/\/([^/]+)/)[1];
	const iconUrl = `https://icons.duckduckgo.com/ip3/${domain}.ico`;
</script>

<NavLink href={'/app/feed/' + feed.id} active={$page.url.pathname === '/app/feed/' + feed.id}>
	<span class="icon" slot="start">
		<img src={iconUrl} alt={feed.title} />
	</span>

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
