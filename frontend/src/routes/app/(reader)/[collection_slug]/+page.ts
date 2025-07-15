import api from '$lib/api';
import { error } from '@sveltejs/kit';
import type { PageLoad } from './$types';
import type { Collection } from '../../types';

export const load: PageLoad = async ({ params, parent, fetch }) => {
    const { collections } = await parent();
    const collection = (collections as Collection[]).find((c) => c.slug === params.collection_slug);

    if (!collection) {
        error(404, 'Collection not found');
    }

    	const data = await api.get('/items', { collection_slug: collection.slug }, fetch);

    return {
        items: data.items
    };
};
