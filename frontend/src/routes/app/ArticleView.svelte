<script lang="ts">
	import type { Item } from './types';

	interface Props {
		item: Item;
	}

	let { item }: Props = $props();
</script>

<div class="article-view">
	<div class="article-placeholder">
		<h3>TODO: Article Reader</h3>
		<p>Selected: {item.title}</p>
	</div>
</div>

<style>
	.article-view {
		height: 100%;
		display: flex;
		flex-direction: column;
	}

	.article-placeholder {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		color: var(--text-light);
		border: 2px dashed var(--accent-lightest);
		border-radius: 8px;
		padding: 40px;
	}

	.article-placeholder h3 {
		margin: 0 0 12px 0;
		font-size: 18px;
		color: var(--text);
	}

	.article-placeholder p {
		margin: 0;
		font-size: 14px;
		opacity: 0.8;
	}
</style> 