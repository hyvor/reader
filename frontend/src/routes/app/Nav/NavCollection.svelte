<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Collection } from '../types';
	import { selectedCollection, publications } from '../appStore';
	import api from '../../../lib/api';
	import IconFolder from '@hyvor/icons/IconFolder';

	interface Props {
		collection: Collection;
	}

	let { collection }: Props = $props();

	function selectCollection() {
		selectedCollection.set(collection);
		api.get('/publications', { collection_id: collection.uuid })
			.then((res) => {
				publications.set(res.publications);
			})
			.catch((err) => {
				console.error('Failed to fetch publications:', err);
			});
	}

	let isSelected = $derived($selectedCollection?.uuid === collection.uuid);
</script>

<Button 
	variant={isSelected ? 'filled' : 'ghost'}
	small
	onclick={selectCollection}
	class="collection-nav-button"
>
	{#snippet start()}
		<span class="icon">
			<IconFolder size={14} />
		</span>
	{/snippet}

	{collection.name}
</Button>

<style>
	:global(.collection-nav-button) {
		width: 100%;
		justify-content: flex-start;
		margin-bottom: 4px;
	}

	.icon {
		display: inline-flex;
		align-items: center;
	}
</style> 