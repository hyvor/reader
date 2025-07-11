import api from '$lib/api';
import { error } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';
import type { Collection } from '../../types';

export const load: LayoutLoad = async ({ params, parent, fetch }) => {
    const { collections } = await parent();
    const collection = (collections as Collection[]).find((c) => c.slug === params.collection_slug);

    if (!collection) {
        error(404, 'Collection not found');
    }

    const data = await api.get('/publications', { collection_id: collection.uuid }, fetch);
    
    return {
        publications: data.publications
    };
};
