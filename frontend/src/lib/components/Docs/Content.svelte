
<div class="content-wrap">
    <content class="g-box">
        <slot />
    </content>
</div>

<style>
    .content-wrap {
        flex: 1;
        padding: 25px 0;
        margin: 0 15px;
    }
    content {
        display: block;
        padding: 30px 50px;
        line-height: 26px;
    }

    content :global(h1) {
        margin-top: 0;
        font-size: 36px;
        font-weight: 600;
        letter-spacing: -.03em;
        margin: 0 0 30px;
        position: relative;
        display: table;
    }
    content :global(h1::after) {
        position: absolute;
        content: "";
        bottom: -13px;
        left: 0px;
        width: 30%;
        height: 3px;
        background: var(--accent);
        margin-top: 10px;
    }   
    content :global(a) {
        color: var(--link);
        text-decoration: underline;
    }
    content :global(li) {
        margin-bottom: 8px;
    }

</style>