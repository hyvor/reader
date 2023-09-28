<script lang="ts">
    export let accent : boolean = false;

    export let large : boolean = false;
    export let small : boolean = false;
</script>

<style>

    button {
        font-weight: 600;
        font-size: 1rem;
        position: relative;
        padding: 0.475rem 1rem;
        border-radius: 1.5rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }

    button:hover {
        background-color: var(--color-accent-light);
    }

    button.accent {
        background-color: var(--color-accent);
        color: #fff;
        transition: .2s box-shadow;
    }
    button.accent:hover {
        box-shadow: 0 0 0 2.5px var(--color-accent-light);
    }


    button.large {
        padding: 0.75rem 1.5rem;
    }
    button.small {
        padding: 0.35rem 0.75rem;
        font-size: 12px;
    }

    .icon {
        display: inline-flex;
        align-items: center;
    }

</style>


<button
    class:accent={accent}
    class:large={large}
    class:small={small}
>
    {#if $$slots['icon-left']}
        <span class="icon icon-left">
            <slot name="icon-left"></slot>
        </span>
    {/if}
    <slot></slot>
    {#if $$slots['icon-right']}
        <span class="icon icon-right">
            <slot name="icon-right"></slot>
        </span>
    {/if}
</button>
