<script lang="ts">
    import { onMount } from 'svelte';
    import api from '$lib/api';
    import { 
        items, 
        loadingItems, 
        selectedCollection, 
        selectedPublication 
    } from '../../appStore';

    let lastFetchedSlug: string | null = null;

    onMount(() => {
        const unsub = selectedCollection.subscribe(async (collection) => {
            if (!collection || collection.slug === lastFetchedSlug) return;
            lastFetchedSlug = collection.slug;

            console.log("Fetching items for collection:", collection.slug);
            loadingItems.set(true);
            
            try {
                const res = await api.get('/items', { collection_slug: collection.slug });
                items.set(res.items);
                selectedPublication.set(null);
            } catch (e) {
                console.error('Failed to fetch items:', e);
            } finally {
                loadingItems.set(false);
            }
        });

        return unsub;
    });
</script>
