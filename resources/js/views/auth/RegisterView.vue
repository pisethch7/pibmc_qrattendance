<template>
  <div class="min-h-[calc(100vh-5rem)] flex items-center justify-center px-3 sm:px-6 lg:px-8 py-6 sm:py-12 relative overflow-hidden">
    <div class="absolute top-1/4 right-1/2 translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
      <div class="glass-panel p-6 sm:p-10 rounded-3xl shadow-2xl border border-slate-800/80">
        <div class="text-center mb-6 sm:mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-xl shadow-blue-900/30 mb-3 sm:mb-4 overflow-hidden">
            <img :src="logoUrl" alt="PIBMC Logo" class="w-full h-full object-contain p-1" />
          </div>
          <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white font-['Outfit']">
            Create an Account
          </h2>
          <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-slate-400">
            Join the smart attendance portal
          </p>
        </div>

        <div v-if="error" class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm flex items-start space-x-3">
          <svg class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ error }}</span>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">

          <!-- Full Name -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
              Full Name
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. Chan Piseth"
              class="w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 transition-all text-sm"
            />
          </div>

          <!-- Username -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
              Username
            </label>
            <input
              v-model="form.username"
              type="text"
              required
              autocomplete="username"
              placeholder="e.g. chan_piseth (letters, numbers, _ .)"
              class="w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 transition-all text-sm"
            />
          </div>

          <!-- Password -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
              Password
            </label>
            <input
              v-model="form.password"
              type="password"
              required
              minlength="6"
              placeholder="Minimum 6 characters"
              class="w-full px-4 py-3 rounded-xl bg-slate-900/90 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 transition-all text-sm"
            />
          </div>

          <!-- Role -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Select Your Role
            </label>
            <div class="grid grid-cols-2 gap-3">
              <label
                class="flex items-center space-x-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                :class="form.role === 'student' ? 'bg-emerald-500/15 border-emerald-500/50 text-emerald-300' : 'bg-slate-900/50 border-slate-800 text-slate-400 hover:border-slate-700'"
              >
                <input type="radio" v-model="form.role" value="student" class="sr-only" />
                <span class="text-lg">📱</span>
                <div>
                  <div class="text-xs font-semibold text-white">Student</div>
                  <div class="text-[10px] opacity-75">Scan QR & check in</div>
                </div>
              </label>

              <label
                class="flex items-center space-x-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                :class="form.role === 'teacher' ? 'bg-indigo-500/15 border-indigo-500/50 text-indigo-300' : 'bg-slate-900/50 border-slate-800 text-slate-400 hover:border-slate-700'"
              >
                <input type="radio" v-model="form.role" value="teacher" class="sr-only" />
                <span class="text-lg">🎓</span>
                <div>
                  <div class="text-xs font-semibold text-white">Teacher</div>
                  <div class="text-[10px] opacity-75">Start sessions & QR</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Sex (students only) -->
          <div v-if="form.role === 'student'">
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Sex
            </label>
            <div class="grid grid-cols-2 gap-3">
              <label
                class="flex items-center justify-center space-x-2 p-3 rounded-xl border cursor-pointer transition-all"
                :class="form.sex === 'male' ? 'bg-sky-500/15 border-sky-500/50 text-sky-300 ring-1 ring-sky-500/30' : 'bg-slate-900/50 border-slate-800 text-slate-400 hover:border-slate-700'"
              >
                <input type="radio" v-model="form.sex" value="male" class="sr-only" />
                <span class="text-base">♂</span>
                <span class="text-xs font-semibold">Male</span>
              </label>

              <label
                class="flex items-center justify-center space-x-2 p-3 rounded-xl border cursor-pointer transition-all"
                :class="form.sex === 'female' ? 'bg-rose-500/15 border-rose-500/50 text-rose-300 ring-1 ring-rose-500/30' : 'bg-slate-900/50 border-slate-800 text-slate-400 hover:border-slate-700'"
              >
                <input type="radio" v-model="form.sex" value="female" class="sr-only" />
                <span class="text-base">♀</span>
                <span class="text-xs font-semibold">Female</span>
              </label>
            </div>
            <p v-if="form.role === 'student' && !form.sex" class="mt-1.5 text-[10px] text-slate-500">
              * Please select your sex to continue
            </p>
          </div>

          <button
            type="submit"
            :disabled="loading || (form.role === 'student' && !form.sex)"
            class="w-full mt-2 py-3.5 px-4 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-purple-600/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Creating account...' : 'Create Account' }}</span>
          </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400">
          Already registered?
          <router-link to="/login" class="text-indigo-400 hover:text-indigo-300 font-semibold ml-1">
            Sign in
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
const { register, isTeacher } = useAuth();

const logoUrl = '/Institute_logo.png';

const form = reactive({
  name: '',
  username: '',
  password: '',
  role: 'student',
  sex: '',
});

const loading = ref(false);
const error = ref('');

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  try {
    await register(form.name, form.username, form.password, form.role, form.sex || null);
    if (isTeacher.value) {
      router.push('/teacher');
    } else {
      router.push('/student');
    }
  } catch (err) {
    error.value = err.response?.data?.message
      || Object.values(err.response?.data?.errors || {}).flat()[0]
      || err.message
      || 'Registration failed.';
  } finally {
    loading.value = false;
  }
};
</script>
