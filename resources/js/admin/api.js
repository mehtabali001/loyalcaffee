import axios from 'axios';

const TOKEN_KEY = 'loyal_admin_token';

export const token = {
    get: () => localStorage.getItem(TOKEN_KEY),
    set: (t) => (t ? localStorage.setItem(TOKEN_KEY, t) : localStorage.removeItem(TOKEN_KEY)),
};

export const api = axios.create({
    baseURL: '/api/admin',
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    timeout: 60000,
});

api.interceptors.request.use((cfg) => {
    const t = token.get();
    if (t) cfg.headers.Authorization = `Bearer ${t}`;
    return cfg;
});

let onUnauthorized = () => {};
export const setUnauthorizedHandler = (fn) => (onUnauthorized = fn);

api.interceptors.response.use(
    (r) => r,
    (err) => {
        if (err.response?.status === 401 && !err.config.url.endsWith('/login')) onUnauthorized();
        return Promise.reject(err);
    },
);

/** Laravel 422 → { 'name.en': 'message' }, keeps dotted keys so nested fields can show their own error. */
export function fieldErrors(err) {
    const e = err?.response?.status === 422 ? err.response.data?.errors : null;
    return e ? Object.fromEntries(Object.entries(e).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])) : null;
}

export function errorMessage(err) {
    return err?.response?.data?.message || err?.message || 'Something went wrong.';
}

/** Upload one or many files; returns [{path, url}]. */
export async function upload(files, folder = 'uploads', onProgress) {
    const fd = new FormData();
    [...files].forEach((f) => fd.append('files[]', f));
    fd.append('folder', folder);
    const { data } = await api.post('/uploads', fd, {
        onUploadProgress: (e) => onProgress?.(e.total ? Math.round((e.loaded / e.total) * 100) : 0),
    });
    return data.files;
}
