<script lang="ts">
	import { Dropdown } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	const publications = [
		{
			name: 'Wired',
			logo: 'https://www.wired.com/verso/static/wired-us/assets/favicon.ico'
		},
		{
			name: 'The Verge',
			logo: 'https://www.theverge.com/static-assets/icons/favicon.ico'
		},
		{
			name: 'TechCrunch',
			logo: 'https://techcrunch.com/wp-content/uploads/2015/02/cropped-cropped-favicon-gradient.png?w=32'
		},
		{
			name: 'The Guardian',
			logo: 'https://static.guim.co.uk/images/favicon-32x32.ico'
		},
		{
			name: 'BBC News',
			logo: 'https://static.files.bbci.co.uk/bbcdotcom/web/20250616-143050-043f96f72f-web-2.23.1-5/favicon-32x32.png'
		},
		{
			name: 'Al Jazeera',
			logo: 'https://www.aljazeera.com/favicon_aje.ico'
		}
	];
</script>

<main>
	<div class="content">
		<div class="header hds-box">
			<div class="collection-wrap">
				<Dropdown>
					{#snippet trigger()}
						<div class="collection-box">
							Supun's Collection
							<IconChevronDown size={12} />
						</div>
					{/snippet}
				</Dropdown>
			</div>
			<a class="logo" href="/">
				<img
					src="https://hyvor.com/api/public/logo/core.svg"
					alt="Hyvor Reader Logo"
					width="26"
					height="26"
				/>
			</a>
		</div>

		<div class="body">
			<div class="publications hds-box">
				{#each publications as publication}
					<div class="publication">
						<img src={publication.logo} alt={publication.name} width="14" height="14" />
						<span>{publication.name}</span>
					</div>
				{/each}
			</div>

			{@render children?.()}
		</div>
	</div>
</main>

<style>
	main {
		display: flex;
		flex-direction: column;
		height: 100vh;
	}
	.content {
		flex: 1;
		display: flex;
		flex-direction: column;
		min-height: 0;
		width: 1000px;
		margin: 0 auto;
	}

	.header {
		padding: 15px 44px;
		margin: 15px 0;
		display: flex;
		align-items: center;
	}

	.collection-box {
		font-size: 14px;
		font-weight: 600;
		display: flex;
		align-items: center;
		gap: 5px;
	}

	.collection-wrap {
		flex: 1;
	}

	.body {
		display: flex;
	}

	.publications {
		width: 350px;
		padding: 25px 0;
		margin-right: 20px;
	}

	.publication {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 10px 20px;
		font-size: 14px;
		cursor: pointer;
	}
	.publication:hover {
		background-color: var(--hover);
	}
</style>
