<script lang="ts">
	import { page } from '$app/stores';
	import api from '$lib/api';
	import { toast } from '@hyvor/design/components';
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
                toast.error(e instanceof Error ? e.message : 'Failed to fetch items');
            } finally {
                loadingItems.set(false);
            }
        })()
	});
</script>
