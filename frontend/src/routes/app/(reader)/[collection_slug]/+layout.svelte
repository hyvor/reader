<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '$app/stores';
    import api from '$lib/api';
    import { 
        publications, 
        collections, 
        selectedCollection,
        loadingPublications 
    } from '../../appStore';

    let lastFetchedSlug: string | null = null;

    $effect(() => {
        const slug = $page.params.collection_slug;
        const found = $collections.find((c) => c.slug === slug);
        if (found && slug !== $selectedCollection?.slug) {
            selectedCollection.set(found);
        }
    });

    onMount(() => {
        const unsub = selectedCollection.subscribe(async (collection) => {
            if (!collection || collection.slug === lastFetchedSlug) return;
            lastFetchedSlug = collection.slug;

            console.log("Fetching publications for collection:", collection.slug);
            loadingPublications.set(true);
            
            try {
                const res = await api.get('/publications', { collection_slug: collection.slug });
                publications.set(res.publications);
            } catch (e) {
                console.error('Failed to fetch publications:', e);
            } finally {
                loadingPublications.set(false);
            }
        });

        return unsub;
    });
</script>

<slot />
