
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
        min-width: 0;
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

    content :global(:not(pre) > code) {
        font-size: 85%;
        padding: 0.2em 0.4em;
        display: inline-block;
        background-color: #f1f1f1;
        color: #eb5757;
        font-family: Consolas,monospace;
        border-radius: 4px;
        line-height: normal;
        font-weight: 400;
    }

    content :global(pre) {
        color: #000;
        background: #f5f2f0;
        text-shadow: 0 1px #fff;
        font-family: Consolas,Monaco,Andale Mono,Ubuntu Mono,monospace;
        font-size: 0.9em;
        text-align: left;
        white-space: pre;
        word-spacing: normal;
        word-break: normal;
        word-wrap: normal;
        line-height: 1.5;
        tab-size: 4;
        hyphens: none;
        overflow: auto;
        border-radius: 20px;
        padding: 20px;
    }

</style>