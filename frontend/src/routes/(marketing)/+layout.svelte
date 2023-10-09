<script>
	import Footer from './Footer.svelte';
	import '../styles.css';
	import { Header } from '@hyvor/design/marketing';

	import logo from '$lib/images/logo.svg';
	import { Button } from '@hyvor/design/components';
	import { page } from "$app/stores";
</script>

<div class="app">

	<Header 
		{logo}
		name="Hyvor Reader"
	>

		<div slot="center">
			<a href="/find">
				<Button active={$page.url.pathname === '/find'} color="invisible">
					Find Feed
				</Button>
			</a>

			<a href="/learn" class="learn-button">
				<Button active={$page.url.pathname.startsWith('/learn')} color="invisible">
					Learn RSS
				</Button>
			</a>
		</div>

		<div slot="end">

			<a href="/app?signup">
				<Button color="accent">
					Go to App
				</Button>
			</a>

		</div>

	</Header>

	<main>
		<slot />
	</main>
	<Footer />
</div>