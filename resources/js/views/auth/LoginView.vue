<template>
  <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <!-- Ambient Background Glows -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/3 w-80 h-80 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
      <!-- Card Container -->
      <div class="glass-panel p-8 sm:p-10 rounded-2xl shadow-2xl border border-slate-800/80">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-500 shadow-xl shadow-indigo-500/30 mb-4">
            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold tracking-tight text-white font-['Outfit']">
            Welcome Back
          </h2>
          <p class="mt-2 text-sm text-slate-400">
            Sign in to access your attendance portal
          </p>
        </div>

        <!-- Error alert -->
        <div v-if="error" class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm flex items-start space-x-3">
          <svg class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ error }}</span>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Email Address
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@pibmc.edu.kh"
              class="w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all text-sm"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Password
            </label>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all text-sm"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
          </button>
        </form>

        <!-- Quick Demo Switcher -->
        <div class="mt-8 pt-6 border-t border-slate-800/80">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider text-center mb-3">
            Instant Demo Sign-In
          </p>
          <div class="grid grid-cols-2 gap-2.5">
            <button
              type="button"
              @click="fillDemo('teacher@pibmc.edu.kh', 'password')"
              class="px-3 py-2.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 text-indigo-300 text-xs font-medium text-center transition-colors flex items-center justify-center space-x-1.5"
            >
              <span>🎓</span>
              <span>Teacher Demo</span>
            </button>
            <button
              type="button"
              @click="fillDemo('student1@pibmc.edu.kh', 'password')"
              class="px-3 py-2.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 text-emerald-300 text-xs font-medium text-center transition-colors flex items-center justify-center space-x-1.5"
            >
              <span>📱</span>
              <span>Student Demo</span>
            </button>
          </div>
        </div>

        <!-- Footer Link -->
        <div class="mt-6 text-center text-xs text-slate-400">
          Don't have an account?
          <router-link to="/register" class="text-indigo-400 hover:text-indigo-300 font-semibold ml-1">
            Create account
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../stores/auth';

const router = useRouter();
const { login, isTeacher } = useAuth();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const fillDemo = (email, password) => {
  form.email = email;
  form.password = password;
  handleSubmit();
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  try {
    await login(form.email, form.password);
    if (isTeacher.value) {
      router.push('/teacher');
    } else {
      router.push('/student');
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Invalid credentials.';
  } finally {
    loading.value = false;
  }
};
</script>
