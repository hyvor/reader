

<div class="docs-nav">

    <nav class="g-box">

        <slot />

    </nav>

</div>

<style>

    .docs-nav {
        width: 220px;
        top: var(--header-height);
        padding: 25px 0;
        position: sticky;
        flex-shrink: 0;
        align-self: flex-start;;
    }

    nav {
        padding: 15px 0;
        overflow-y: auto;
    }

</style>