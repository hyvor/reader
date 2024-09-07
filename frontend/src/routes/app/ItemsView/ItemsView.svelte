<script lang="ts">
	export let type: 'all' | 'saved' | 'feed' = 'all';
	export let feedId: number | null = null;
</script>

<section class="wrap">
	<div class="inner hds-box">
		<div class="list">Hello World</div>
		<div class="reader">Reading</div>
	</div>
</section>

<style>
	.wrap {
		flex: 1;
		padding: 15px 0;
	}
	.inner {
		display: flex;
		height: 100%;
	}
	.list {
		width: 270px;
		padding: 20px 25px;
		border-right: 1px solid var(--border);
	}
	.reader {
		flex: 1;
	}
</style>
