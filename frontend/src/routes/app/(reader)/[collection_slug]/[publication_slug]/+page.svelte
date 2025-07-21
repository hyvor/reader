<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '$app/stores';
    import api from '$lib/api';
    import { 
        items, 
        loadingItems, 
        publications, 
        selectedPublication 
    } from '../../../appStore';

    let lastFetchedSlug: string | null = null;

    $effect(() => {
        const slug = $page.params.publication_slug;
        const publication = $publications.find((p) => p.slug === slug);
        if (publication && slug !== $selectedPublication?.slug) {
            selectedPublication.set(publication);
        }
    });

    onMount(() => {
        const unsub = selectedPublication.subscribe(async (publication) => {
            if (!publication || publication.slug === lastFetchedSlug) return;
            lastFetchedSlug = publication.slug;

            console.log("Fetching items for publication:", publication.slug);
            loadingItems.set(true);
            
            try {
                const res = await api.get('/items', { publication_slug: publication.slug });
                items.set(res.items);
            } catch (e) {
                console.error('Failed to fetch items:', e);
            } finally {
                loadingItems.set(false);
            }
        });

        return unsub;
    });
</script>
