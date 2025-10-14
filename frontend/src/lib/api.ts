type Method = 'get' | 'post' | 'patch' | 'put' | 'delete';

export default class api {

    static async call(method: Method, endpoint: string, data: Record<string, any> = {}) {
        const apiUrl = location.origin + "/api/app";
        const endpointUrl = endpoint.replace(/^\//, '');
        let url = `${apiUrl}/${endpointUrl}`;

        if (method === 'get') {
            const query = Object.keys(data).map((key) => {
                return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
            }).join('&');
            url += '?' + query;
        }

        const response = await fetch(url, {
            method: method.toUpperCase(),
            body: method !== 'get' ? JSON.stringify(data) : undefined,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });

        if (!response.ok) {
            let json: any = null;
            try {
                json = await response.json();
            } catch {}
            const err: any = new Error(json?.message ?? 'Unknown error');
            err.code = response.status;
            err.data = json?.data ?? null;
            throw err;
        }

        return await response.json();

    }

    static async get(url: string, data: Record<string, any> = {}) {
        return await api.call('get', url, data);
    }

    static async post(url: string, data: Record<string, any> = {}) {
        return await api.call('post', url, data);
    }

    static async patch(url: string, data: Record<string, any> = {}) {
        return await api.call('patch', url, data);
    }

    static async delete(url: string, data: Record<string, any> = {}) {
        return await api.call('delete', url, data);
    }

}