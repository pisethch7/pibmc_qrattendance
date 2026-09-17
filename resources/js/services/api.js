import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

import { getDeviceId, getDeviceName } from '../utils/device';

// Request interceptor to attach bearer token and device headers
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('pibmc_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    config.headers['X-Device-Id'] = getDeviceId();
    config.headers['X-Device-Name'] = getDeviceName();
    return config;
});

// Response interceptor to handle unauthenticated 401s
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('pibmc_token');
            localStorage.removeItem('pibmc_user');
            if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default api;
