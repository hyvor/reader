<script lang="ts">
	import { page } from '$app/stores';
	import api from '$lib/api';
	import {
		items,
		loadingItems,
		publications,
		selectedPublication,
		selectedCollection
	} from '../../../appStore';

	$effect(() => {
		const slug = $page.params.publication_slug;
		const pub = $publications.find((p) => p.slug === slug) ?? null;
		if (pub?.slug !== $selectedPublication?.slug) {
			selectedPublication.set(pub);
		}

		const col = $selectedCollection;
		if (!col) return;

        (async () => {
            loadingItems.set(true);
            try {
                const params = pub ? { publication_slug: pub.slug } : { collection_slug: col.slug };
                const res = await api.get('/items', params);
                items.set(res.items);
            } catch (e) {
                console.error('Failed to fetch items:', e);
            } finally {
                loadingItems.set(false);
            }
        })()
	});
</script>
