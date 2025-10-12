import type { Collection } from '$lib/types';
import api from '../api';

export async function getCollections() {
    const res = await api.get('/collections');
    return res.collections as Collection[];
}

export async function getCollectionBySlug(slug: string) {
    const res = await api.get(`/collections/${slug}`);
    return res.collection as Collection;
}

export async function createCollection(name: string, isPublic: boolean) {
    const res = await api.post('/collections', { name, is_public: isPublic });
    return res.collection as Collection;
}


