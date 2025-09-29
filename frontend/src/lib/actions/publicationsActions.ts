import type { Publication } from '$lib/types';
import api from '../api';

export async function addPublication(collectionSlug: string, url: string) {
    const res = await api.post('/publications', {
        collection_slug: collectionSlug,
        url
    });
    return res.publication as Publication;
}


