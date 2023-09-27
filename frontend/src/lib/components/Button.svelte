<script lang="ts">
    export let accent : boolean = false;
</script>

<style>

    button {
        font-weight: 600;
        font-size: 1rem;
        position: relative;
        padding: 0.475rem 1rem;
        border-radius: 1.5rem;
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

</style>


<button
    class:accent={accent}
>
    <slot></slot>
</button>
