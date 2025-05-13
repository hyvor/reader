<script lang="ts">
	import { onMount } from 'svelte';
	import { CodeBlock, Callout } from '@hyvor/design/components';

	onMount(() => {
		if ((window as any).Tally) {
			(window as any).Tally.loadEmbeds();
		}
	});
</script>

<h1>Learn RSS</h1>

<p>
	Welcome to the World of RSS! In this section, we'll cover the basics of RSS, why it's useful, and
	how to use it.
</p>

<Callout type="info">
	{#snippet icon()}
		<span>💡</span>
	{/snippet}
	RSS stands for <b>Really Simple Syndication</b>.
</Callout>

<h2 id="how">How it works</h2>

<p>
	Websites that publish content regularly, like blogs, news sites, and podcasts, use RSS to let
	people know when they've published something new. Here's how it works:
</p>

<ul>
	<li>
		<b>Step 1: </b> Websites publish a file called an <b>RSS feed</b> that lists all of their
		content. This can be in <a href="/learn/rss">RSS format</a> or in
		<a href="/learn/atom">Atom format</a>. This file is updated every time the website publishes
		something new.
	</li>
	<li>
		<b>Step 2:</b> You can use an <b>RSS Reader</b> like <a href="/">Hyvor Reader</a> to subscribe to
		the RSS feeds of your favorite websites.
	</li>
	<li>
		<b>Step 3:</b> Your RSS Reader will regularly poll the RSS feeds you've subscribed to, and let you
		know when there's new content. You can then read the content in your RSS Reader, or click through
		to the website to read it there.
	</li>
</ul>

<h2 id="how-to-find-rss">How to find and subscribe to RSS feeds</h2>

<p>
	Most RSS readers support subscribing to RSS feeds by just entering the website's URL. If you
	simply need to find the RSS feed URL, you can use our <a href="/find">Find Feed</a> tool.
</p>

<Callout type="info">
	{#snippet icon()}
		<span>🔎</span>
	{/snippet}
	Some websites have <b>multiple RSS feeds</b> for different categories (e.g. news, sports, etc.).
</Callout>

<p>There are couple of other ways to find RSS feeds:</p>

<ul>
	<li>
		<b>Method 1:</b> Look for the RSS icon <!-- <IconRssFill style="color: #f26522;vertical-align:middle;" /> -->
		on the website. This is usually a link to the RSS feed. Don't be frightened by the XML code you see
		when you click on the link. Copy the feed URL from the address bar of your browser and paste it into
		your RSS reader.
	</li>
	<li>
		<b>Method 2:</b> Look into the source code of the website and search for an
		<code>{`<link>`}</code>
		element with <code>type="application/rss+xml"</code> or
		<code>type="application/atom+xml"</code>. The <code>href</code> attribute of the element will be
		the URL of the RSS feed.

		<CodeBlock
			code={`
            <link 
                rel="alternate" 
                type="application/rss+xml"
                href="https://example.com/feed" 
            />
        `}
			language="html"
		/>
	</li>
	<li>
		<b>Method 3:</b> In Many CMSs, including
		<a href="https://wordpress.org" target="_blank">WordPress</a>
		and <a href="https://blogs.hyvor.com" target="_blank">Hyvor Blogs</a>, you can simply add
		<code>/feed</code> to the end of the URL to get the RSS feed.
	</li>
</ul>

<h2 id="why-rss">Why RSS</h2>

<p>
	Why RSS when you can just follow your favorite websites on social media? Here are some reasons:
</p>

<ul>
	<li>
		<b>Complete Control</b>: You have complete control over what you see. You can subscribe to any
		website you want, and you can unsubscribe from them whenever you want. With social media,
		algorithms often curate your feed, and you may miss content from sources you care about.
	</li>
	<li>
		<b>No algorithmic manipulation</b>: Social media platforms use algorithms to prioritize and
		display content (which is mostly motivated by keeping you longer on the platform), which can
		lead into filter bubbles and the promotion of popular or sensational content over more niche or
		important information. RSS, on the other hand, provides a chronological feed of content from
		your chosen sources without algorithmic manipulation.
	</li>
	<li>
		<b>Privacy</b>: When you follow websites on social media, those platforms collect data about
		your interests and behavior for targeted advertising. RSS does not involve tracking or data
		collection, offering a more private way to consume content, although some RSS readers may still
		collect data.
	</li>
	<li>
		<b>Reduced distractions</b>: Social media platforms are designed to keep users engaged, often
		with features like infinite scrolling and notifications. This can lead to distractions and
		decreased productivity. RSS readers offer a more focused and less distracting reading
		experience.
	</li>
	<li>
		<b>Avoiding platform-specific restrictions</b>: Social media platforms have their own rules and
		restrictions, which can lead to censorship and the removal of content. RSS readers are not
		subject to these restrictions, and you can choose to follow sources that may be censored on
		social media.
	</li>
	<li>
		<b>Organized content consumption</b>: RSS readers allow you to organize and categorize your
		subscriptions, making it easier to manage and prioritize your content. You can create folders
		and tags to stay organized, rather that mindlessly scrolling through a social media feed.
	</li>
	<li>
		<b>Better for the whole humanity</b>: Social media platforms are centralized, and they can
		censor content and restrict access to it anytime they want. RSS, on the other hand, is a
		decentralized protocol. Anyone can publish content and anyone can consume it. It gives everyone
		a voice and a chance to share their ideas and knowledge.
	</li>
</ul>

<p>More related articles:</p>

<ul>
	<li>
		<a href="https://www.wired.com/story/rss-readers-feedly-inoreader-old-reader/" target="_blank">
			It's Time for an RSS Revival - Wired (2018)
		</a>
	</li>
	<li>
		<a href="https://quakkels.com/posts/rss-is-wonderful/" target="_blank">
			RSS is Wonderful - Brandon Quakkelaar (2020)
		</a>
	</li>
	<li>
		<a href="https://danielmiessler.com/p/its-time-to-get-back-into-rss/" target="_blank">
			It's Time to Get Back Into RSS - Daniel Miessler (2020)
		</a>
	</li>
	<li>
		<a href="https://davidyat.es/2017/05/18/rss-nothing-better/" target="_blank">
			RSS: there's nothing better - David Yates (2017)
		</a>
	</li>
</ul>

<h2 id="rss-limitations">RSS Limitations</h2>

<p>RSS isn't a solution for everything. Here are some of its limitations:</p>

<ul>
	<li>
		No feedback system. In social media, you can like, comment, and share posts directly. RSS
		doesn't have any of these features in the protocol itself. However, some RSS Readers have
		built-in support for these features.
	</li>
	<li>
		Some websites don't have RSS feeds, even though there are some tools that generate RSS feeds
		from websites that don't have them.
	</li>
	<li>
		Because RSS readers don't load the full website, publishers who rely on ads for revenue may not
		get ad revenue from RSS readers. Also, tracking analytics is harder for them as the readers do
		not visit the website.
	</li>
	<li>
		Some RSS feeds only show a summary of the content, and you have to click through to the website
		to read the full content (mostly to avoid the above problem). Some readers, however, support
		full content feeds by crawling the website.
	</li>
	<li>
		RSS is not a real-time protocol. RSS readers poll the RSS feeds at regular intervals (adapting
		to the frequency of publishing on a website). So, you may not get notified of new content
		immediately. This can be a good thing if you don't want to be distracted by notifications. But,
		if you need real-time updates, RSS may not be the best solution.
	</li>
</ul>

<h2 id="next">What's Next?</h2>

<ul>
	<li>
		Sign up for <a href="/">Hyvor Reader</a> or other <a href="/learn/readers">RSS Readers</a> to start
		reading your favorite websites.
	</li>
	<li>
		See the <a href="/learn/history">History of RSS</a> to learn how RSS came to be.
	</li>
	<li>
		If you are a developer, see the <a href="/learn/rss">RSS</a> and <a href="/learn/atom">Atom</a> format
		guides to learn how to implement RSS feeds on your CMS/website.
	</li>
	<li>
		If you are developing a RSS reader, see the <a href="/learn/opml">OPML</a> guide to learn how to
		import and export feed subscriptions between RSS readers.
	</li>
</ul>

<h2 id="survey">Survey</h2>

<p>
	Goal of this "Learn RSS" documentation is to increase awareness and adoption of RSS. We would
	really appreciate it if you could take a minute to answer the following questions (it's completely
	anonymous).
</p>

<svelte:head>
	<script src="https://tally.so/widgets/embed.js"></script>
</svelte:head>

<div class="survey-wrap">
	<iframe
		data-tally-src="https://tally.so/embed/wMaD7Y?alignLeft=1&hideTitle=1&transparentBackground=1&dynamicHeight=1"
		loading="lazy"
		width="100%"
		height="200"
		frameborder="0"
		marginheight="0"
		marginwidth="0"
		title="Hyvor Reader Learn RSS Feedback"
	></iframe>
</div>

<style>
	.survey-wrap {
		padding: 15px;
		background-color: var(--accent-lightest);
		border-radius: 20px;
	}
</style>
