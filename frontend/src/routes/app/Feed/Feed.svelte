<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import IconFilter from '@hyvor/icons/IconFilter';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';

	interface Item {
		publicationName: string;
		title: string;
		description: string;
	}

	const items: Item[] = [
		{
			publicationName: 'Simon Wilson',
			title: 'The Rise of AI in Everyday Life',
			description:
				'An in-depth look at how artificial intelligence is transforming our daily routines and industries.'
		},
		{
			publicationName: 'HYVOR Blog',
			title: 'Understanding the Basics of Web Development',
			description:
				'A beginner’s guide to web development, covering HTML, CSS, and JavaScript fundamentals.'
		},
		{
			publicationName: 'Derek Sivers',
			title: 'How to Start a Business with No Money',
			description: 'Practical tips and strategies for launching a business on a tight budget.'
		},
		{
			publicationName: 'TechCrunch',
			title: 'The Future of Quantum Computing',
			description:
				'Exploring the potential of quantum computing and its implications for technology and society.'
		},
		{
			publicationName: 'The Verge',
			title: 'Exploring the Metaverse: What You Need to Know',
			description:
				'A comprehensive overview of the metaverse, its technologies, and its future impact.'
		},
		{
			publicationName: 'BBC News',
			title: 'Global Climate Change: What’s Next?',
			description: 'An analysis of the current state of climate change and future projections.'
		},
		{
			publicationName: 'National Geographic',
			title: 'Wildlife Conservation Efforts in 2024',
			description:
				'A look at the latest initiatives in wildlife conservation and their impact on biodiversity.'
		},
		{
			publicationName: 'The Guardian',
			title: 'The Impact of Social Media on Mental Health',
			description: 'Examining the effects of social media usage on mental health and well-being.'
		}
	];
</script>

<div class="feed hds-box">
	<div class="header">
		<div class="collection">
			<Button color="input" variant="invisible">
				My Personal Collection
				{#snippet end()}
					<IconChevronDown size={10} />
				{/snippet}
			</Button>
		</div>
		<div class="feeds-filter">
			<Button color="input" variant="invisible">
				All Feeds
				{#snippet start()}
					<IconFilter size={12} />
				{/snippet}
			</Button>
		</div>
	</div>
	<div class="items">
		{#each items as item}
			<button class="item">
				<div class="left">
					<div class="top">
						<img
							src="https://picsum.photos/40?{item.publicationName}"
							alt="Publication Logo"
							class="logo"
						/>
						<div class="publication-name">
							{item.publicationName}
						</div>
						<div class="publish-time">&nbsp;&middot;&nbsp; 2 hours ago</div>
					</div>
					<div class="title">
						{item.title}
					</div>
					<div class="description">
						{item.description}
					</div>
					<div class="open-button">
						<Button size="small" color="input">
							Open
							{#snippet end()}
								<IconBoxArrowUpRight size={12} />
							{/snippet}
						</Button>
					</div>
				</div>
				<div class="featured-image">
					<img src="https://picsum.photos/400/200?{item.title}" alt="Item" class="item-image" />
				</div>
				<!-- <div class="right">
					<Button size="small" color="input">
						Open
						{#snippet end()}
							<IconBoxArrowUpRight size={12} />
						{/snippet}
					</Button>
				</div> -->
			</button>
		{/each}
	</div>
</div>

<style>
	.feed {
		width: 100%;
		display: flex;
		flex-direction: column;
	}
	.header {
		padding: 15px 30px;
		border-bottom: 1px solid var(--border);
		display: flex;
		align-items: center;
	}
	.collection {
		flex: 1;
	}
	.items {
		padding: 15px 24px;
		overflow: auto;
	}
	.item {
		padding: 15px 20px;
		border-radius: 20px;
		cursor: pointer;
		text-align: left;
		display: flex;
		width: 100%;
		align-items: center;
		gap: 10px;
	}
	.item:hover {
		background-color: var(--hover);
	}
	.left {
		flex: 1;
	}
	img.logo {
		width: 15px;
		height: 15px;
		border-radius: 50%;
	}
	.top {
		flex: 1;
		display: flex;
		align-items: center;
		gap: 4px;
		margin-bottom: 3px;
	}
	.publication-name {
		font-size: 12px;
	}
	.publish-time {
		font-size: 12px;
		color: var(--text-light);
	}
	.title {
		font-weight: 600;
	}
	.description {
		font-size: 14px;
		color: var(--text-light);
		margin-top: 2px;
	}
	.featured-image img {
		max-width: 200px;
		max-height: 100px;
		border-radius: 10px;
	}
	.open-button {
		margin-top: 5px;
	}
</style>
