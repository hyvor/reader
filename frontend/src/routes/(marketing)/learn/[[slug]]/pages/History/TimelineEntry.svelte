<script lang="ts">
    export let year: string;
    export let title: string;
</script>


<div class="entry">

    <div class="left">

        <div class="year">
            {year}
        </div>

    </div>

    <div class="right">

        <div class="title">
            {title}
        </div>

        <div class="content">
            <slot></slot>
        </div>

    </div>

</div>

<style>

    .entry {
        display: flex;
        flex-direction: row;
    }

    .entry:first-child .right:after {
        border-radius: 10px 10px 0 0;
    }
    .entry:last-child .right:after {
        border-radius: 0 0 10px 10px;
    }

    .left {
        padding: 15px 25px;
        text-align: right;
        flex: 1;
    }

    .right {
        position: relative;
        padding: 15px 25px;
        text-align: left;
        flex: 7;
    }

    .right:after {
        content: "";
        position: absolute;
        height: 100%;
        top: 0;
        left: -2px;
        width: 4px;
        background-color: var(--accent-lightest);
    }

    .right:before {
        content: "";
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: var(--accent);
        left: -10px;
        top: 17.5px;
        z-index: 10;
    }

    .title {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .content {
        margin-top: 10px;
    }

</style>