<script lang="ts">

    export let type : 'info' | 'success' | 'warning' | 'danger' | 'light' = 'light'; 

</script>

<style>

    .callout {
        padding: 15px;
        border-radius: var(--box-radius);    
        margin: 15px 0;
    }

    .callout.info {
        background-color: var(--blue-light);
        color: var(--blue-dark);
    }

    .callout.warning {
        background-color: var(--orange-light);
        color: var(--orange-dark)
    }

    .callout.danger {
        background-color: var(--red-light);
        color: var(--red-dark);
    }

    .top {
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .title-icon {
        vertical-align: middle;
        margin-right: 5px;
    }

    .text {
        display: flex;
        align-items: center;
    }

    .icon {
        margin-right: 5px;
        font-size: 18px;
    }

    .title {
    }

</style>

<div class={"callout " + type}>

    {#if $$slots['title-icon'] || $$slots.title}

        <div class="top">
            {#if $$slots['title-icon']}<span class="title-icon"><slot name="title-icon"></slot></span>{/if}
            <div class="title"><slot name="title"></slot></div>
        </div>

    {/if}

    <div class="text">

        {#if $$slots.icon}
            <span class="icon"><slot name="icon" /></span>
        {/if}

        <div class="text-inner">
            {#if $$slots.default}
                <slot />
            {/if}
            <slot name="text" />
        </div>

    </div>


</div>