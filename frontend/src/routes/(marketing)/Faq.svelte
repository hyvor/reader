<script lang="ts">
    interface Props {
        title: string;
        children?: import('svelte').Snippet;
    }

    let { title, children }: Props = $props();
</script>

<div class="faq">

    <div class="faq-title">
        {title}
    </div>

    <div class="faq-text">
        {@render children?.()}
    </div>

</div>

<style>
    .faq {
        padding: 15px;
        width: 600px;
        margin: auto;
    }
    .faq-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .faq-text {
        font-size: 1.2rem;
        line-height: 1.5;
    }
</style>