import { reactive, computed } from 'vue';
import api from '../services/api';
import { getDeviceId, getDeviceName } from '../utils/device';

const state = reactive({
    user: JSON.parse(localStorage.getItem('pibmc_user') || 'null'),
    token: localStorage.getItem('pibmc_token') || null,
    loading: false,
    error: null,
});

export const useAuth = () => {
    const isAuthenticated = computed(() => !!state.token && !!state.user);
    const isTeacher = computed(() => state.user?.role === 'teacher');
    const isStudent = computed(() => state.user?.role === 'student');

    const setAuth = (user, token) => {
        state.user = user;
        state.token = token;
        localStorage.setItem('pibmc_user', JSON.stringify(user));
        localStorage.setItem('pibmc_token', token);
    };

    const clearAuth = () => {
        state.user = null;
        state.token = null;
        localStorage.removeItem('pibmc_user');
        localStorage.removeItem('pibmc_token');
    };

    const login = async (username, password) => {
        state.loading = true;
        state.error = null;
        try {
            const { data } = await api.post('/auth/login', {
                username,
                password,
                device_id: getDeviceId(),
                device_name: getDeviceName(),
            });
            setAuth(data.user, data.token);
            return data;
        } catch (err) {
            state.error = err.response?.data?.errors?.device?.[0] || err.response?.data?.message || 'Login failed.';
            throw err;
        } finally {
            state.loading = false;
        }
    };

    const register = async (name, username, password, role, sex = null) => {
        state.loading = true;
        state.error = null;
        try {
            const { data } = await api.post('/auth/register', {
                name,
                username,
                password,
                role,
                ...(sex ? { sex } : {}),
                device_id: getDeviceId(),
                device_name: getDeviceName(),
            });
            setAuth(data.user, data.token);
            return data;
        } catch (err) {
            state.error = err.response?.data?.message || 'Registration failed.';
            throw err;
        } finally {
            state.loading = false;
        }
    };

    const logout = async () => {
        try {
            if (state.token) {
                await api.post('/auth/logout');
            }
        } catch {
            // Ignore API logout error if token already invalid
        } finally {
            clearAuth();
        }
    };

    const fetchUser = async () => {
        if (!state.token) return null;
        try {
            const { data } = await api.get('/auth/user');
            state.user = data.user;
            localStorage.setItem('pibmc_user', JSON.stringify(data.user));
            return data.user;
        } catch {
            clearAuth();
            return null;
        }
    };

    return {
        state,
        isAuthenticated,
        isTeacher,
        isStudent,
        login,
        register,
        logout,
        fetchUser,
    };
};
