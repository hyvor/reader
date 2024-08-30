<script lang="ts">
	import FeedList from "./FeedList/FeedList.svelte";
	import Nav from "./Nav/Nav.svelte";
	import Reader from "./Reader/Reader.svelte";

	import { Loader } from "@hyvor/design/components";
	import api from "../../lib/api";

    const response = api.get('/init');

</script>

{#await response}
    <Loader />
{:then data}
    <main>
        <Nav />
        <FeedList />
        <Reader />
    </main>
{/await}



<style>

    main {
        display: flex;
    }

</style>