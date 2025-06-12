<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import type { Item } from './types';

	interface Props {
		item: Item;
		onclick?: () => void;
		onOpenClick?: (item: Item) => void;
	}

	let { item, onclick, onOpenClick }: Props = $props();

	function getRelativeTime(timestamp: number): string {
		const now = Date.now();
		const diff = now - (timestamp * 1000);
		const minutes = Math.floor(diff / 60000);
		const hours = Math.floor(diff / 3600000);
		const days = Math.floor(diff / 86400000);

		if (minutes < 60) return `${minutes}m ago`;
		if (hours < 24) return `${hours}h ago`;
		return `${days}d ago`;
	}
</script>

<button class="item-card" onclick={onclick}>
	<div class="card-content">
		<div class="card-meta">
			<span class="publisher">{item.publication_title} • <span class="time">{getRelativeTime(item.published_at || item.updated_at || 0)}</span></span>
		</div>
		<h3 class="card-title">{item.title}</h3>
		<p class="card-description">{item.summary || 'No description available'}</p>
		<div class="card-actions">
			<Button 
				size="small" 
				onclick={(e: Event) => { e.stopPropagation(); onOpenClick?.(item); }}
			>
				Open
			</Button>
		</div>
	</div>
	<div class="card-image">
		{#if item.image}
			<img src={item.image} alt={item.title} />
		{:else}
			<div class="image-placeholder">📰</div>
		{/if}
	</div>
</button>

<style>
	.item-card {
		display: flex;
		background: var(--bg);
		border: 1px solid var(--accent-lightest);
		border-radius: 8px;
		overflow: hidden;
		cursor: pointer;
		transition: all 0.2s ease;
		text-align: left;
		width: 100%;
	}

	.item-card:hover {
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		border-color: var(--accent-light);
	}

	.card-content {
		flex: 1;
		padding: 16px;
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.card-meta {
		display: flex;
		align-items: center;
		font-size: 12px;
		color: var(--text-light);
	}

	.publisher {
		font-weight: 500;
	}

	.time {
		color: var(--text-light);
	}

	.card-title {
		margin: 0;
		font-size: 16px;
		font-weight: 600;
		color: var(--text);
		line-height: 1.3;
	}

	.card-description {
		margin: 0;
		font-size: 14px;
		color: var(--text-light);
		line-height: 1.4;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	.card-actions {
		margin-top: auto;
	}

	.card-image {
		width: 120px;
		flex-shrink: 0;
		overflow: hidden;
		background: var(--accent-lightest);
		position: relative;
	}

	.card-image img {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
	}

	.image-placeholder {
		font-size: 24px;
		opacity: 0.5;
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
</style> 
