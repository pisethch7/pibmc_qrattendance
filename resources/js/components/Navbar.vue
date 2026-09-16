<template>
  <nav v-if="isAuthenticated" class="sticky top-0 z-40 glass-panel border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Brand -->
        <div class="flex items-center space-x-3">
          <router-link to="/" class="flex items-center space-x-3 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-200">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
              </svg>
            </div>
            <div>
              <span class="text-lg font-bold tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">
                PIBMC
              </span>
              <span class="text-xs font-semibold uppercase tracking-wider text-indigo-400 ml-1.5 px-1.5 py-0.5 rounded bg-indigo-500/10 border border-indigo-500/20">
                Attendance
              </span>
            </div>
          </router-link>
        </div>

        <!-- Navigation Links -->
        <div class="hidden md:flex items-center space-x-1">
          <router-link
            v-if="isTeacher"
            to="/teacher"
            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="$route.name === 'teacher-dashboard' ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'"
          >
            Dashboard
          </router-link>

          <router-link
            v-if="isStudent"
            to="/student"
            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="$route.name === 'student-dashboard' ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'"
          >
            My Classes
          </router-link>

          <router-link
            v-if="isStudent"
            to="/student/scan"
            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-1.5"
            :class="$route.name === 'student-scan' ? 'bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20' : 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20'"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            </svg>
            <span>Scan QR</span>
          </router-link>

          <router-link
            to="/reports"
            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="$route.name === 'attendance-report' ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50'"
          >
            Reports
          </router-link>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-3">
          <div class="flex items-center space-x-2.5 px-3 py-1.5 rounded-lg bg-slate-900/80 border border-slate-800 text-left">
            <div
              class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs"
              :class="isTeacher ? 'bg-indigo-500 text-white' : 'bg-emerald-500 text-white'"
            >
              {{ state.user?.name?.charAt(0) || 'U' }}
            </div>
            <div class="hidden sm:block">
              <div class="text-xs font-semibold text-slate-200 leading-tight">
                {{ state.user?.name }}
              </div>
              <div class="text-[10px] uppercase font-bold tracking-wider" :class="isTeacher ? 'text-indigo-400' : 'text-emerald-400'">
                {{ state.user?.role }}
              </div>
            </div>
          </div>

          <button
            @click="handleLogout"
            class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition-colors"
            title="Log out"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Nav Bar (bottom fixed on small screens) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 glass-panel border-t border-slate-800/90 bg-slate-950/95 px-4 py-2">
      <div class="flex items-center justify-around">
        <router-link
          v-if="isTeacher"
          to="/teacher"
          class="flex flex-col items-center py-1 text-xs"
          :class="$route.name === 'teacher-dashboard' ? 'text-indigo-400 font-semibold' : 'text-slate-400'"
        >
          <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
          Dashboard
        </router-link>

        <router-link
          v-if="isStudent"
          to="/student"
          class="flex flex-col items-center py-1 text-xs"
          :class="$route.name === 'student-dashboard' ? 'text-emerald-400 font-semibold' : 'text-slate-400'"
        >
          <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          Classes
        </router-link>

        <router-link
          v-if="isStudent"
          to="/student/scan"
          class="flex flex-col items-center py-1 px-4 rounded-xl bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/25 -mt-4 text-xs"
        >
          <svg class="w-6 h-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
          Scan
        </router-link>

        <router-link
          to="/reports"
          class="flex flex-col items-center py-1 text-xs"
          :class="$route.name === 'attendance-report' ? 'text-indigo-400 font-semibold' : 'text-slate-400'"
        >
          <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          Reports
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuth } from '../stores/auth';

const router = useRouter();
const { state, isAuthenticated, isTeacher, isStudent, logout } = useAuth();

const handleLogout = async () => {
  await logout();
  router.push('/login');
};
</script>
