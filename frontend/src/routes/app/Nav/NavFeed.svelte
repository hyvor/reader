<script lang="ts">
	import { NavLink } from '@hyvor/design/components';
	import type { Feed } from '../../../lib/types';
	export let feed: Feed;
</script>

<NavLink href="/app" active={feed.name === 'Food in Jars'}>
	<span class="icon" slot="start">
		<img src={feed.icon_url} alt={feed.name} />
	</span>

	{feed.name}
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
