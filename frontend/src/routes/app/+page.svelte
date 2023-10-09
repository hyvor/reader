<script lang="ts">
	import FeedList from "./FeedList/FeedList.svelte";
	import Nav from "./Nav/Nav.svelte";
	import Reader from "./Reader/Reader.svelte";
</script>

<main>
    <Nav />
    <FeedList />
    <Reader />
</main>


<style>

    main {
        display: flex;
    }

</style>