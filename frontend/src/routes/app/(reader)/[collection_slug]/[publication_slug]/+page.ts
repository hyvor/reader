import api from '$lib/api';
import type { PageLoad } from './$types';
import type { Publication } from '../../../types';

export const load: PageLoad = async ({ params, parent, fetch }) => {
    const { publications } = await parent();

    const publication = (publications as Publication[]).find((p) => p.slug === params.publication_slug);

    if (!publication) {
        return {
            status: 404,
            error: new Error('Publication not found')
        };
    }

    const data = await api.get('/items', { publication_id: publication.uuid }, fetch);

    return {
        items: data.items,
    };
};
