import axios from 'axios';

/** Axios instance shared by the public site. Locale travels as a header so URLs stay cacheable. */
export function createHttp(getLocale) {
    const http = axios.create({
        baseURL: '/api/v1',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        timeout: 15000,
    });
    http.interceptors.request.use((cfg) => {
        cfg.headers['X-Locale'] = getLocale();
        return cfg;
    });
    return http;
}

/** Laravel 422 → { field: 'first message' } */
export function validationErrors(error) {
    const errs = error?.response?.status === 422 ? error.response.data?.errors : null;
    if (!errs) return null;
    return Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]));
}
