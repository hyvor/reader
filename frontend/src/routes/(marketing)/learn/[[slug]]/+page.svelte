<script lang="ts">
    import { 
        Docs, 
        DocsNav as Nav, 
        DocsNavItem as NavItem,
        DocsNavCategory as NavCategory,
        DocsContent as Content,
    } from '@hyvor/design/marketing';

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