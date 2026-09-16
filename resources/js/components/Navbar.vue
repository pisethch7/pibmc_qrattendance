<template>
  <div v-if="isAuthenticated">
    <!-- Desktop & Tablet Top Navbar (Hidden on Mobile) -->
    <header class="hidden md:block sticky top-0 z-40 bg-slate-950/85 backdrop-blur-xl border-b border-slate-800/80">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Brand -->
          <div class="flex items-center space-x-3">
          <router-link to="/" class="flex items-center space-x-2.5 sm:space-x-3 group active:scale-95 transition-transform">
              <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl overflow-hidden bg-white flex items-center justify-center shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform flex-shrink-0">
                <img :src="logoUrl" alt="PIBMC Logo" class="w-full h-full object-contain" />
              </div>
              <div>
                <div class="flex items-center">
                  <span class="text-base sm:text-lg font-bold tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent font-['Outfit']">
                    PIBMC
                  </span>
                  <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-indigo-400 ml-1.5 px-1.5 py-0.5 rounded-full bg-indigo-500/10 border border-indigo-500/20">
                    Attendance
                  </span>
                </div>
              </div>
            </router-link>
          </div>

          <!-- Desktop & Tablet Navigation Links -->
          <div class="flex items-center space-x-1 lg:space-x-2">
            <router-link
              v-if="isTeacher"
              to="/teacher"
              class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center space-x-2"
              :class="$route.name === 'teacher-dashboard' ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
              <span>Dashboard</span>
            </router-link>

            <router-link
              v-if="isStudent"
              to="/student"
              class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center space-x-2"
              :class="$route.name === 'student-dashboard' ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              <span>My Classes</span>
            </router-link>

            <router-link
              v-if="isStudent"
              to="/student/scan"
              class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center space-x-2"
              :class="$route.name === 'student-scan' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20'"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              </svg>
              <span>Scan QR</span>
            </router-link>

            <router-link
              to="/reports"
              class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center space-x-2"
              :class="$route.name === 'attendance-report' ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              <span>Reports</span>
            </router-link>
          </div>

          <!-- User Profile & Logout -->
          <div class="flex items-center space-x-2 sm:space-x-3">
            <div class="flex items-center space-x-2 px-2.5 py-1.5 sm:px-3 sm:py-1.5 rounded-xl bg-slate-900/90 border border-slate-800/90 text-left">
              <div
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center font-bold text-xs shadow-inner"
                :class="isTeacher ? 'bg-gradient-to-tr from-indigo-600 to-purple-500 text-white' : 'bg-gradient-to-tr from-emerald-600 to-teal-400 text-white'"
              >
                {{ state.user?.name?.charAt(0) || 'U' }}
              </div>
              <div>
                <div class="text-xs font-semibold text-slate-200 leading-tight max-w-[120px] truncate">
                  {{ state.user?.name }}
                </div>
                <div class="text-[10px] uppercase font-bold tracking-wider" :class="isTeacher ? 'text-indigo-400' : 'text-emerald-400'">
                  {{ state.user?.role }}
                </div>
              </div>
            </div>

            <button
              @click="handleLogout"
              class="p-2 sm:p-2.5 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 active:scale-95 transition-all"
              title="Log out"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile App Bottom Navigation Bar (Strictly Fixed to Bottom on Mobile) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 mobile-nav-blur border-t border-slate-800/90 pb-safe pt-1.5 px-3 shadow-2xl">
      <div class="flex items-center justify-around relative">
        <!-- Student Tab: Home / Dashboard -->
        <router-link
          v-if="isStudent"
          to="/student"
          class="flex flex-col items-center py-1.5 px-3 rounded-2xl active:scale-95 transition-all"
          :class="$route.name === 'student-dashboard' ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-slate-200'"
        >
          <div class="p-1 rounded-xl" :class="$route.name === 'student-dashboard' ? 'bg-emerald-500/15' : ''">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </div>
          <span class="text-[10px] mt-0.5 tracking-tight">Classes</span>
        </router-link>

        <!-- Teacher Tab: Dashboard -->
        <router-link
          v-if="isTeacher"
          to="/teacher"
          class="flex flex-col items-center py-1.5 px-3 rounded-2xl active:scale-95 transition-all"
          :class="$route.name === 'teacher-dashboard' ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200'"
        >
          <div class="p-1 rounded-xl" :class="$route.name === 'teacher-dashboard' ? 'bg-indigo-500/15' : ''">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
          </div>
          <span class="text-[10px] mt-0.5 tracking-tight">Courses</span>
        </router-link>

        <!-- Student Center Action: Scan QR (Elevated floating action button) -->
        <router-link
          v-if="isStudent"
          to="/student/scan"
          class="flex flex-col items-center -mt-6 group active:scale-90 transition-transform"
        >
          <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-emerald-500/40 ring-4 ring-slate-950 group-hover:scale-105 transition-all">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
          </div>
          <span class="text-[10px] font-bold text-emerald-400 mt-1 tracking-tight">Scan QR</span>
        </router-link>

        <!-- Teacher Center Action: Dashboard Launch Session / Scanner -->
        <router-link
          v-if="isTeacher"
          to="/teacher"
          class="flex flex-col items-center -mt-6 group active:scale-90 transition-transform"
        >
          <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-500 text-white flex items-center justify-center shadow-lg shadow-indigo-500/40 ring-4 ring-slate-950 group-hover:scale-105 transition-all">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span class="text-[10px] font-bold text-indigo-400 mt-1 tracking-tight">Session</span>
        </router-link>

        <!-- Common Tab: Reports -->
        <router-link
          to="/reports"
          class="flex flex-col items-center py-1.5 px-3 rounded-2xl active:scale-95 transition-all"
          :class="$route.name === 'attendance-report' ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200'"
        >
          <div class="p-1 rounded-xl" :class="$route.name === 'attendance-report' ? 'bg-indigo-500/15' : ''">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <span class="text-[10px] mt-0.5 tracking-tight">Reports</span>
        </router-link>

        <!-- Quick Logout button for mobile bottom nav -->
        <button
          @click="handleLogout"
          class="flex flex-col items-center py-1.5 px-3 rounded-2xl text-slate-400 hover:text-rose-400 active:scale-95 transition-all"
          title="Sign out"
        >
          <div class="p-1 rounded-xl">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </div>
          <span class="text-[10px] mt-0.5 tracking-tight">Logout</span>
        </button>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuth } from '../stores/auth';

const router = useRouter();
const { state, isAuthenticated, isTeacher, isStudent, logout } = useAuth();

const logoUrl = '/Institute_logo.png';

const handleLogout = async () => {
  await logout();
  router.push('/login');
};
</script>
