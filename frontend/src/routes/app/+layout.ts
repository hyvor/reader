import api from '../../lib/api';

export const ssr = false;

export const load = async ({ fetch }: { fetch: typeof window.fetch }) => {
    const initData = await api.get('/init', {}, fetch);
    return initData;
};