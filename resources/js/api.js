import axios from 'axios';

const api = axios.create({
    baseURL: '/api', 
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true, 
});

api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    const locale = localStorage.getItem('locale');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    if (locale) {
        config.headers['X-Locale'] = locale;
    }

    return config;
});

export default api;
