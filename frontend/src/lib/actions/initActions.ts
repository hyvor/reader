import type { Collection } from '$lib/types';
import api from '../api';

export async function init() {
    const res = await api.get('/init');
    return {
        collections: res.collections as Collection[]
    };
}


