<script>
	import Footer from './Footer.svelte';
	import Header from './Header.svelte';
	import '../styles.css';
</script>

<div class="app">
	<Header />
	<main>
		<slot />
	</main>
	<Footer />
</div>

<style>
	.app {
		display: flex;
		flex-direction: column;
		min-height: 100vh;
	}

	main {
		margin-top: 65px;
	}
</style>
