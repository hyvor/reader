<script lang="ts">
	import type { FeedItem } from '../types';

	export let item: FeedItem;
</script>

<div class="reader">
	<div class="title">{item.title}</div>
	<div class="metadata">
		<span class="date"> {item.created_at} </span>
	</div>
	<div class="link">
		<a href={item.url} target="_blank">
			{item.url}
		</a>
	</div>
	<div class="featured-image" />
	<div class="content">
		{@html item.content_html}
	</div>
</div>

<style>
	.reader {
		padding: 30px;
		height: 100%;
		overflow: auto;
		flex: 1;
		min-width: 0;
	}

	.title {
		font-size: 24px;
		font-weight: 600;
	}

	.featured-image {
		margin-top: 20px;
	}
	.featured-image img {
		max-width: 100%;
		border-radius: 20px;
		max-height: 600px;
	}

	.metadata {
		margin-top: 6px;
		color: var(--text-light);
		font-size: 14px;
	}
	.link {
		margin-top: 3px;
		font-size: 14px;
		color: var(--link);
	}
	.link a:hover {
		text-decoration: underline;
	}
</style>
