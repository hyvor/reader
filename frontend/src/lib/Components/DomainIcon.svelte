<script lang="ts">
	const { url, size = 18 }: { url: string; size?: number } = $props();

	const iconUrl = $derived.by(() => {
		if (!url) return undefined;
		const domain = url.match(/https?:\/\/([^/]+)/)?.[1];
		return domain ? `https://icons.duckduckgo.com/ip3/${domain}.ico` : undefined;
	});
</script>

{#if iconUrl}
	<img src={iconUrl} alt="" style="width: {size}px; height: {size}px;" />
{/if}

<style>
	img {
		border-radius: 50%;
	}
</style>
