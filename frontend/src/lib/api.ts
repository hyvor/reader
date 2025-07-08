type Method = 'get' | 'post' | 'patch' | 'put' | 'delete';

export default class api {

    static async call(method: Method, endpoint: string, data : Record<string, any> = {}) {

        const apiUrl = location.origin + "/api/app";
        const endpointUrl = endpoint.replace(/^\//, '');
        let url = `${apiUrl}/${endpointUrl}`;

        if (method === 'get') {
            const query = Object.keys(data).map((key) => {
                return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
            }).join('&');
            url += '?' + query;
        }

        // @ts-ignore
        if (location.hostname.endsWith('.localhost') && !document.cookie.includes('authsess=')) {
            document.cookie = 'authsess=dev-fake-session; Path=/; SameSite=Lax';
        }

        const response = await fetch(url, {
            method: method.toUpperCase(),
            body: method !== 'get' ? JSON.stringify(data) : undefined,
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
        });

        if (!response.ok) {
            const json = await response.json();
            throw new Error(json ? json.error : 'Unknown error');
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