<script lang="ts">
	import Content from "$lib/components/Docs/Content.svelte";
    import Docs from "$lib/components/Docs/Docs.svelte";
	import Nav from "$lib/components/Docs/Nav/Nav.svelte";
	import NavCategory from "$lib/components/Docs/Nav/NavCategory.svelte";
	import NavItem from "$lib/components/Docs/Nav/NavItem.svelte";

    export let data;
</script>

<Docs>

    <Nav slot="nav">

        <NavCategory name="Learn RSS">
            <NavItem href="/learn">Start Here</NavItem>
            <NavItem href="/learn/history">RSS History</NavItem>
            <NavItem href="/learn/terminology">RSS Readers</NavItem>
        </NavCategory>

        <NavCategory name="Formats">
            <NavItem href="/learn/rss">RSS</NavItem>
            <NavItem href="/learn/atom">Atom</NavItem>
            <NavItem href="/learn/opml">OPML</NavItem>
        </NavCategory>

    </Nav>

    <Content slot="content">
        <svelte:component this={data.content} />
    </Content>

</Docs>