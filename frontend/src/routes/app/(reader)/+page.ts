import { redirect } from '@sveltejs/kit';
import type { PageLoad } from './$types';

export const load: PageLoad = async ({ parent }) => {
    const { collections, selectedCollection } = await parent();

    const collectionToRedirect = selectedCollection ?? collections?.[0];

    if (collectionToRedirect) {
        throw redirect(307, `/app/${collectionToRedirect.slug}`);
    }
}; 