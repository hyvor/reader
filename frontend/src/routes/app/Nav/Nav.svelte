<script lang="ts">
	import { Button, NavLink } from '@hyvor/design/components';
	import NavFeed from './NavFeed.svelte';
	import { IconBook, IconBookmark, IconGear } from '@hyvor/icons';
	import logo from '$lib/images/logo.svg';
	import { feeds } from '../appStore';
	import { page } from '$app/stores';
</script>

<div id="left">
	<nav class="hds-box">
		<div class="head">
			<a href="/">
				<img src={logo} alt="Hyvor Reader" />
				<span class="name"> Hyvor Reader </span>
			</a>
		</div>

		<div class="main">
			<NavLink href="/app" active={$page.url.pathname === '/app'}>
				<IconBook slot="start" />
				All Feeds
			</NavLink>
			<NavLink href="/app/saved" active={$page.url.pathname === '/app/saved'}>
				<IconBookmark slot="start" size={14} />
				Saved
			</NavLink>
		</div>

		<div class="feeds">
			{#each $feeds as feed}
				<NavFeed {feed} />
			{/each}
		</div>

		<div class="bottom">
			<NavLink href="/app/settings" active={$page.url.pathname.startsWith('/app/settings')}>
				<IconGear slot="start" />
				Settings
			</NavLink>
		</div>

		<div class="footer">
			<Button small>
				Add Feed <!-- <IconPlus slot="end" /> -->
			</Button>
		</div>
	</nav>
</div>

<style>
	#left {
		width: 300px;
		flex-shrink: 0;
		padding: 15px;
		height: 100vh;
	}

	#left > :global(nav) {
		margin-bottom: 15px;
		height: 100%;
		display: flex;
		flex-direction: column;
	}

	.categories {
		padding: 15px 20px 11px;
		border-bottom: 1px solid var(--accent-lightest);
	}

	.head {
		padding: 15px 29px;
		border-bottom: 1px solid var(--accent-lightest);
		font-weight: 600;
		display: flex;
		align-items: center;
	}
	.head a {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}

	.head img {
		width: 22px;
		height: 22px;
	}

	.main {
		margin-top: 15px;
	}

	.feeds {
		padding: 15px 0;
		flex: 1;
		overflow: auto;
	}

	.main :global(a),
	.feeds :global(a),
	.bottom :global(a) {
		padding-top: 8px !important;
		padding-bottom: 8px !important;
	}

	.bottom {
		padding: 10px 0;
	}

	.footer {
		border-top: 1px solid var(--accent-lightest);
		padding: 10px;
		text-align: center;
	}
</style>
