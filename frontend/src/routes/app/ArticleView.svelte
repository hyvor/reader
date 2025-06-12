<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconChevronLeft from '@hyvor/icons/IconChevronLeft';
	import IconChevronRight from '@hyvor/icons/IconChevronRight';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';
	import type { Item } from './types';

	interface Props {
		item: Item;
		isLoading?: boolean;
		canGoToPrevious?: boolean;
		canGoToNext?: boolean;
		onPrevious?: () => void;
		onNext?: () => void;
		onBackToItems?: () => void;
	}

	let { item, isLoading = false, canGoToPrevious = false, canGoToNext = false, onPrevious, onNext, onBackToItems }: Props = $props();

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

	function estimateReadingTime(content: string): number {
		const wordsPerMinute = 200;
		const wordCount = content.split(/\s+/).length;
		return Math.ceil(wordCount / wordsPerMinute);
	}

	function openOriginal() {
		window.open(item.url, '_blank');
	}
</script>

<div class="article-reader hds-box">
	<header class="back-header">
		<Button variant="invisible" onclick={onBackToItems} class="back-button">
			{#snippet start()}
				<IconArrowLeft size={16} />
			{/snippet}
			Back to feed
		</Button>
	</header>

	<header class="article-header">
		<div class="article-meta">
			<img
				src="https://picsum.photos/40?{item.publication_title}"
				alt="Publication Logo"
				class="logo"
			/>
			<span class="publication">{item.publication_title}</span>
			<span class="separator">•</span>
			<span class="time">{getRelativeTime(item.published_at || item.updated_at || 0)}</span>
			{#if item.content_html}
				<span class="separator">•</span>
				<span class="reading-time">{estimateReadingTime(item.content_html)} min read</span>
			{/if}
		</div>
		<h1 class="article-title">{item.title}</h1>
		{#if item.authors && item.authors.length > 0}
			<div class="article-authors">
				By {item.authors.join(', ')}
			</div>
		{/if}
		<div class="article-actions">
			<Button size="small" color="input" onclick={openOriginal}>
				Read Original
				{#snippet end()}
					<IconBoxArrowUpRight size={12} />
				{/snippet}
			</Button>
		</div>
	</header>

	<main class="article-content">
		{#if item.image}
			<div class="article-hero-image">
				<img src={item.image} alt={item.title} />
			</div>
		{/if}

		{#if isLoading}
			<div class="loading-state">
				<p>Loading article content...</p>
			</div>
		{:else if item.content_html}
			<div class="article-body">
				{@html item.content_html}
			</div>
		{:else if item.summary}
			<div class="article-summary">
				<p><strong>Summary:</strong></p>
				<p>{item.summary}</p>
				<p class="no-content-message">Full article content not available. <button onclick={openOriginal}>Read the original article</button> for the complete content.</p>
			</div>
		{:else}
			<div class="no-content">
				<p>Article content not available.</p>
				<Button onclick={openOriginal}>Read Original Article</Button>
			</div>
		{/if}
	</main>

	<footer class="article-navigation">
		<Button 
			variant="invisible" 
			disabled={!canGoToPrevious}
			onclick={onPrevious}
			class="nav-button"
		>
			{#snippet start()}
				<IconChevronLeft size={16} />
			{/snippet}
			Previous Article
		</Button>
		
		<Button 
			variant="invisible" 
			disabled={!canGoToNext}
			onclick={onNext}
			class="nav-button"
		>
			Next Article
			{#snippet end()}
				<IconChevronRight size={16} />
			{/snippet}
		</Button>
	</footer>
</div>

<style>
	.article-reader {
		width: 100%;
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.back-header {
		padding: 15px 30px;
		border-bottom: 1px solid var(--border);
		background: var(--bg);
	}

	:global(.back-button) {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.article-header {
		padding: 15px 30px;
		border-bottom: 1px solid var(--border);
		background: var(--bg);
		flex-shrink: 0;
	}

	.article-meta {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 12px;
		color: var(--text-light);
		margin-bottom: 12px;
	}

	.logo {
		width: 15px;
		height: 15px;
		border-radius: 50%;
	}

	.separator {
		opacity: 0.5;
	}

	.publication {
		font-weight: 500;
		font-size: 12px;
	}

	.time, .reading-time {
		font-size: 12px;
		color: var(--text-light);
	}

	.article-title {
		margin: 0 0 12px 0;
		font-size: 24px;
		font-weight: 600;
		line-height: 1.3;
		color: var(--text);
	}

	.article-authors {
		font-size: 14px;
		color: var(--text-light);
		margin-bottom: 12px;
		font-style: italic;
	}

	.article-actions {
		display: flex;
		gap: 12px;
	}

	.article-content {
		flex: 1;
		overflow-y: auto;
		padding: 15px 24px;
	}

	.article-hero-image {
		margin-bottom: 20px;
		border-radius: 10px;
		overflow: hidden;
	}

	.article-hero-image img {
		width: 100%;
		height: auto;
		max-height: 300px;
		object-fit: cover;
		display: block;
	}

	.loading-state {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 60px 20px;
		color: var(--text-light);
	}

	.article-body {
		line-height: 1.6;
		font-size: 16px;
		color: var(--text);
	}

	.article-body :global(h1),
	.article-body :global(h2),
	.article-body :global(h3),
	.article-body :global(h4),
	.article-body :global(h5),
	.article-body :global(h6) {
		margin: 24px 0 12px 0;
		font-weight: 600;
		line-height: 1.3;
	}

	.article-body :global(h1) { font-size: 24px; }
	.article-body :global(h2) { font-size: 20px; }
	.article-body :global(h3) { font-size: 18px; }
	.article-body :global(h4) { font-size: 16px; }

	.article-body :global(p) {
		margin: 0 0 16px 0;
	}

	.article-body :global(img) {
		max-width: 100%;
		height: auto;
		border-radius: 8px;
		margin: 20px 0;
	}

	.article-body :global(blockquote) {
		margin: 20px 0;
		padding: 12px 20px;
		border-left: 3px solid var(--border);
		background: var(--hover);
		border-radius: 0 6px 6px 0;
		font-style: italic;
	}

	.article-body :global(code) {
		background: var(--hover);
		padding: 2px 6px;
		border-radius: 4px;
		font-family: 'SF Mono', Monaco, monospace;
		font-size: 14px;
	}

	.article-body :global(pre) {
		background: var(--hover);
		padding: 16px;
		border-radius: 8px;
		overflow-x: auto;
		margin: 16px 0;
	}

	.article-body :global(ul),
	.article-body :global(ol) {
		margin: 16px 0;
		padding-left: 24px;
	}

	.article-body :global(li) {
		margin: 6px 0;
	}

	.article-body :global(a) {
		color: var(--text);
		text-decoration: underline;
	}

	.article-body :global(a:hover) {
		color: var(--text-light);
	}

	.article-summary {
		padding: 20px;
		background: var(--hover);
		border-radius: 10px;
		border-left: 3px solid var(--border);
	}

	.no-content-message {
		margin-top: 12px;
		font-style: italic;
		color: var(--text-light);
		font-size: 14px;
	}

	.no-content-message button {
		color: var(--text);
		background: none;
		border: none;
		text-decoration: underline;
		cursor: pointer;
		font-size: 14px;
	}

	.no-content {
		text-align: center;
		padding: 60px 20px;
		color: var(--text-light);
	}

	.article-navigation {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 15px 30px;
		border-top: 1px solid var(--border);
		gap: 16px;
		background: var(--bg);
	}

	:global(.nav-button) {
		display: flex;
		align-items: center;
		gap: 8px;
		transition: all 0.2s ease;
	}

	:global(.nav-button:disabled) {
		opacity: 0.4;
		cursor: not-allowed;
	}

	:global(.nav-button:not(:disabled):hover) {
		background: var(--hover);
	}

	@media (max-width: 768px) {
		.article-title {
			font-size: 20px;
		}

		.article-body {
			font-size: 15px;
		}

		.article-content {
			padding: 15px 20px;
		}

		.article-header, .back-header {
			padding: 15px 20px;
		}

		.article-navigation {
			padding: 15px 20px;
			flex-direction: column;
			gap: 12px;
		}

		.article-hero-image {
			margin-bottom: 16px;
			border-radius: 8px;
		}

		.article-hero-image img {
			max-height: 200px;
		}
	}
</style> 