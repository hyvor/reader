<script lang="ts">
    export let accent : boolean = false;
    export let large : boolean = false;
    export let small : boolean = false;
    export let active : boolean = false;
</script>

<style>

    button {
        font-weight: 600;
        font-size: 0.875rem;
        position: relative;
        padding: 0.475em 1em;
        border-radius: 1.5rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }

    button:hover, button.active {
        background-color: var(--accent-light);
    }

    button.accent {
        background-color: var(--accent);
        color: #fff;
        transition: .2s box-shadow;
    }
    button.accent:hover {
        box-shadow: 0 0 0 2.5px var(--accent-light);
    }

    button.large {
        padding: 0.75em 1.5em;
    }
    button.small {
        padding: 0.35em 0.75em;
        font-size: 0.9em;
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

    class:active={active}

    {...$$restProps}
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
