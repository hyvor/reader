<script lang="ts">
	import { Button, NavLink } from '@hyvor/design/components';
	import NavCollection from './NavCollection.svelte';
	import IconBook from '@hyvor/icons/IconBook';
	import IconGear from '@hyvor/icons/IconGear';
	import { collections } from '../appStore';
	import { page } from '$app/stores';
</script>

<div id="left">
	<nav class="hds-box">
		<div class="main">
			<NavLink href="/app" active={$page.url.pathname === '/app'}>
				{#snippet start()}
					<IconBook />
				{/snippet}
				All Collections
			</NavLink>
		</div>

		<div class="collections">
			{#each $collections as collection}
				<NavCollection {collection} />
			{/each}
		</div>

		<div class="bottom">
			<NavLink href="/app/settings" active={$page.url.pathname.startsWith('/app/settings')}>
				{#snippet start()}
					<IconGear />
				{/snippet}
				Settings
			</NavLink>
		</div>

		<div class="footer">
			<Button small>
				Add Publication <!-- <IconPlus slot="end" /> -->
			</Button>
		</div>
	</nav>
</div>

<style>
	#left {
		width: 300px;
		flex-shrink: 0;
		padding: 15px;
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

	.collections {
		padding: 15px 0;
		flex: 1;
		overflow: auto;
	}

	.main :global(a),
	.collections :global(a),
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
