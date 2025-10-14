<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '$app/stores';
    import api from '$lib/api';
    import { 
        publications, 
        collections, 
        selectedCollection,
        selectedPublication,
        loadingPublications 
    } from '../../appStore';
    import { toast } from '@hyvor/design/components';

    let { children } = $props();
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

            loadingPublications.set(true);
            
            try {
                const res = await api.get('/publications', { collection_slug: collection.slug });
                publications.set(res.publications);
                selectedPublication.set(null);
            } catch (e) {
                if ((e as any)?.code === 401) {
                    const toPage = $page.url.searchParams.has('signup') ? 'signup' : 'login';
                    const url = new URL((e as any)?.data?.[toPage + '_url'], location.origin);
                    url.searchParams.set('redirect', location.href);
                    location.href = url.toString();
                } else {
                    toast.error(e instanceof Error ? e.message : 'Failed to fetch publications');
                }
            } finally {
                loadingPublications.set(false);
            }
        });

        return unsub;
    });
</script>

{@render children()}
