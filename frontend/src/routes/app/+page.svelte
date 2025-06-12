<script lang="ts">
	import PublicationList from './PublicationList/PublicationList.svelte';
	import ItemsView from './ItemsView/ItemsView.svelte';
	import { selectedPublication } from './appStore';
</script>

<div class="app-content">
	<PublicationList />
	{#if $selectedPublication}
		<ItemsView />
	{:else}
		<div class="empty-state">
			<p>Select a publication to view its items</p>
		</div>
	{/if}
</div>

<style>
	.app-content {
		display: flex;
		flex: 1;
		min-height: 0;
	}

	.empty-state {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--text-light);
	}
</style>
