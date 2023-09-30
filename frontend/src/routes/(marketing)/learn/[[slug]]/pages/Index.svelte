<script>
	import Callout from "$lib/components/Callout/Callout.svelte";
	import { Rss, RssFill } from "svelte-bootstrap-icons";
</script>
<h1>
    Learn RSS
</h1>

<p>
    Welcome to the World of RSS! In this section, we'll cover the basics of RSS, why it's useful, and how to use it.
</p>


<Callout type="info">
    <span slot="icon">💡</span>
    RSS stands for <b>Really Simple Syndication</b>.
</Callout>

<h2 id="how">How it works</h2>

<p>
    Websites that publish content regularly, like blogs, news sites, and podcasts, use RSS to let people know when they've published something new. Here's how it works:
</p>

<ul>
    <li>
        <b>Step 1: </b> Websites publish a file called an <b>RSS feed</b> that lists all of their content. This can be in <a href="rss">RSS format</a> or in <a href="atom">Atom format</a>. This file is updated every time the website publishes something new.
    </li>
    <li>
        <b>Step 2:</b> You can use an <b>RSS Reader</b> like <a href="/">Hyvor Reader</a> to subscribe to the RSS feeds of your favorite websites.
    </li>
    <li>
        <b>Step 3:</b> Your RSS Reader will regularly poll the RSS feeds you've subscribed to, and let you know when there's new content. You can then read the content in your RSS Reader, or click through to the website to read it there.
    </li>
</ul>

<h2 id="how-to-find-rss">How to find and subscribe to RSS feeds</h2>

<p>
    Most RSS readers support subscribing to RSS feeds by just entering the website's URL. If you simply need to find the RSS feed URL, you can use our <a href="/find">Find Feed</a> tool.
</p>

<p>
    There are couple of other ways to find RSS feeds:
</p>

<ul>
    <li>
        <b>Method 1:</b> Look for the RSS icon <RssFill style="color: #f26522;vertical-align:middle;" /> on the website. Clicking on it will usually take you to the RSS feed. Paste the URL of the RSS feed into your RSS reader.
    </li>
    <li>
        <b>Method 2:</b> Look for a link that says "RSS" or "Subscribe". It will usually be in the header or footer of the website.
    </li>
    <li>
        <b>Method 3:</b> If you can't find an RSS feed, try adding <code>/feed</code> to the end of the website's URL. For example, if the website's URL is <code>https://example.com</code>, try <code>https://example.com/feed</code>.
    </li>
</ul>

<h2 id="why-rss">Why RSS over Social Media</h2>

<p>
    Why RSS when you can just follow your favorite websites on social media? Here are some reasons:
</p>


<p>
    More resources on this topic:
</p>

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

<h2 id="rss-limitations">
    RSS Limitations
</h2>

<p>
    RSS isn't a solution for everything. Here are some of its limitations:
</p>

<ul>
    <li>
        No feedback system. In social media, you can like, comment, and share posts directly. RSS doesn't have any of these features in the protocol itself. However, some RSS Readers have built-in support for these features.
    </li>
    <li>
        Some websites don't have RSS feeds, even though there are some tools that generate RSS feeds from websites that don't have them.
    </li>
    <li>
        Because RSS readers don't load the full website, publishers who rely on ads for revenue may not get ad revenue from RSS readers. Also, tracking analytics is harder for them as the readers do not visit the website.
    </li>
    <li>
        Some RSS feeds only show a summary of the content, and you have to click through to the website to read the full content (mostly to avoid the above problem). Some readers, however, support full content feeds by crawling the website.
    </li>
</ul>