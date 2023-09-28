<script>
	import Button from "$lib/components/Button.svelte";
	import Faq from "./Faq.svelte";
</script>

<svelte:head>
	<title>Hyvor Reader - Free RSS Reader</title>
	<meta name="description" content="Hyvor Reader is a free and open-source RSS reader" />
</svelte:head>

<section class="container" id="hero">

	<h1>
		Free RSS Reader
	</h1>

	<div class="button-wrap">

		<a href="/app" target="_blank">
			<Button accent large>Open App</Button>
		</a>
	</div>

</section>

<section class="container" id="faqs">

	<Faq title="Is it really free?">
		Yes, Hyvor Reader is a completely free & open-source RSS reader. And, no, you are not the product. We do not track you or show you ads. It is merely a product created by the HYVOR team out of love for the open web.
	</Faq>

	<Faq title="How does it work?">
		It's so simple - just subscribe to RSS/Atom feeds in the app and read. No magic. No advanced algorithms. No AI. Just you and the content you love.
	</Faq>

	<Faq title="Do you have Android/iOS apps?">
		No, we are only focusing on the web app for now.
	</Faq>

	<Faq title="How can I support you?">
		You can support us by spreading the word about Hyvor Reader, contributing to the <a href="https://github.com/hyvor/reader.hyvor.com" target="_blank" class="link">codebase</a>, using other <a href="https://hyvor.com" target="_blank" class="link">HYVOR paid products</a>, or donating via <a href="https://github.com/hyvor" target="_blank" class="link">Github Sponsors</a> or <a href="https://www.paypal.com/donate/?hosted_button_id=Z2E682B6KLRYS" target="_blank" class="link">Paypal</a>.
	</Faq>

</section>

<style>

	#hero {
		text-align: center;
		padding: 40px 0 100px;
	}

	#faqs {
		margin-bottom: 60px;
	}
	
</style>
