<script lang="ts">
    export let name: string;

    let current = 'All';
</script>

<span 
    class="category"
    class:active={current === name}
>
    <span class="name">{name}</span>
    {#if name === 'All'}
        <span class="count">25</span>
    {/if}
</span>

<style>
    .category {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        background-color: #eee;
        font-size: 12px;
        margin-right: 4px;
        cursor: pointer;
        margin-bottom: 4px;
    }
    .category:hover {
        box-shadow: 0 0 0 1px var(--accent-light);
    }
    .category.active {
        background-color: var(--accent);
        color: white;
    }
    .name {
        vertical-align: middle;
    }
    .count {
        margin-left: 2px;
        font-size: 9px;
        display: inline-flex;
        vertical-align: middle;
        border-radius: 10px;
        background-color: var(--accent-light);
        padding: 0px 3px;
        align-items: center;
        justify-content: center;
    }
    .category.active .count {
        background-color: white;
        color: var(--accent);
    }
</style>