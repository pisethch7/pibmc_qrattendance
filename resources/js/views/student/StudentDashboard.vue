<template>
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">
    <!-- Top Greeting & CTA Mobile App Card -->
    <div class="p-5 sm:p-8 rounded-3xl bg-gradient-to-br from-emerald-950/70 via-slate-900 to-teal-950/50 border border-emerald-500/30 glow-emerald flex flex-col md:flex-row md:items-center md:justify-between gap-5 relative overflow-hidden">
      <!-- Ambient light effect -->
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

      <div class="space-y-2 relative z-10">
        <div class="flex items-center space-x-2">
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
            Student Portal
          </span>
          <span class="text-xs text-slate-400">PIBMC Smart Campus</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-['Outfit'] tracking-tight">
          Hello, {{ state.user?.name }}!
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
          Check in on time to maintain high attendance records. Tap below when class starts to scan the projector QR code.
        </p>
      </div>

      <router-link
        to="/student/scan"
        class="relative z-10 w-full md:w-auto px-6 py-3.5 sm:py-4 rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-400 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-sm shadow-xl shadow-emerald-500/30 flex items-center justify-center space-x-2.5 active:scale-95 transition-all flex-shrink-0"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
        </svg>
        <span>Scan Classroom QR</span>
      </router-link>
    </div>

    <!-- Stats Grid (Responsive 2x2 on Mobile, 4-Col on Tablet/Desktop) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-5">
      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800 relative overflow-hidden">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Attendance</span>
          <span class="w-2 h-2 rounded-full" :class="stats.attendance_rate >= 80 ? 'bg-emerald-400' : 'bg-amber-400'"></span>
        </div>
        <div class="mt-2 sm:mt-3 text-2xl sm:text-3xl font-extrabold font-['Outfit']" :class="stats.attendance_rate >= 80 ? 'text-emerald-400' : 'text-amber-400'">
          {{ stats.attendance_rate }}%
        </div>
        <div class="mt-1 text-[10px] sm:text-[11px] text-slate-400">Target: 80%+</div>
      </div>

      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Present</span>
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        </div>
        <div class="mt-2 sm:mt-3 text-2xl sm:text-3xl font-extrabold text-emerald-400 font-['Outfit']">
          {{ stats.present }}
        </div>
        <div class="mt-1 text-[10px] sm:text-[11px] text-slate-400">On-time checks</div>
      </div>

      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Late</span>
          <span class="w-2 h-2 rounded-full bg-amber-400"></span>
        </div>
        <div class="mt-2 sm:mt-3 text-2xl sm:text-3xl font-extrabold text-amber-400 font-['Outfit']">
          {{ stats.late }}
        </div>
        <div class="mt-1 text-[10px] sm:text-[11px] text-slate-400">Past start time</div>
      </div>

      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Absent</span>
          <span class="w-2 h-2 rounded-full bg-rose-400"></span>
        </div>
        <div class="mt-2 sm:mt-3 text-2xl sm:text-3xl font-extrabold text-slate-200 font-['Outfit']">
          {{ stats.absent }} <span class="text-xs font-normal text-slate-400">/ {{ stats.excused }} exc</span>
        </div>
        <div class="mt-1 text-[10px] sm:text-[11px] text-slate-400">Missed classes</div>
      </div>
    </div>

    <!-- Enrolled Courses & History (Tablet 2-col, Mobile Stacked) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
      <!-- Enrolled Courses (5 Cols on Desktop/Tablet) -->
      <div class="lg:col-span-5 space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base sm:text-lg font-bold text-white font-['Outfit'] flex items-center space-x-2">
            <span>Enrolled Courses</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400">
              {{ enrolledCourses.length }}
            </span>
          </h2>
        </div>

        <div v-if="enrolledCourses.length === 0" class="glass-panel p-8 rounded-2xl text-center border border-slate-800 text-xs text-slate-400">
          You are not currently enrolled in any courses.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="c in enrolledCourses"
            :key="c.id"
            class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800 hover:border-slate-700 space-y-2.5 transition-colors"
          >
            <div class="flex items-start justify-between gap-2">
              <h3 class="text-sm sm:text-base font-bold text-white">
                {{ c.name }}
              </h3>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700/50 whitespace-nowrap">
                {{ c.sessions_count || 0 }} Sessions
              </span>
            </div>
            <p class="text-xs text-slate-400 flex items-center space-x-2">
              <svg class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              <span class="truncate">{{ c.teacher?.name || 'Faculty Member' }}</span>
            </p>
            <p class="text-xs text-slate-400 flex items-center space-x-2">
              <svg class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="truncate">{{ c.schedule_info || 'Standard Schedule' }}</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Attendance History Timeline (7 Cols on Desktop/Tablet) -->
      <div class="lg:col-span-7 space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base sm:text-lg font-bold text-white font-['Outfit']">
            Recent Attendance
          </h2>
          <router-link to="/reports" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold active:scale-95 transition-transform">
            View All Reports →
          </router-link>
        </div>

        <div v-if="records.length === 0" class="glass-panel p-8 rounded-2xl text-center border border-slate-800 text-xs text-slate-400">
          No attendance records recorded yet.
        </div>

        <div v-else class="glass-panel p-3 sm:p-5 rounded-2xl border border-slate-800 space-y-2.5">
          <div
            v-for="rec in records.slice(0, 10)"
            :key="rec.id"
            class="p-3 sm:p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between gap-3 hover:border-slate-700/80 transition-colors"
          >
            <div class="min-w-0 flex-1">
              <div class="text-xs sm:text-sm font-semibold text-white truncate">
                {{ rec.session?.course?.name || 'Class Session' }}
              </div>
              <div class="text-[11px] text-slate-400 mt-0.5 flex items-center space-x-2 truncate">
                <span>{{ formatDate(rec.checked_in_at) }}</span>
                <span>•</span>
                <span class="capitalize">{{ rec.method }}</span>
              </div>
            </div>

            <span
              class="px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider flex-shrink-0"
              :class="getStatusBadgeClass(rec.status)"
            >
              {{ rec.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuth } from '../../stores/auth';
import api from '../../services/api';

const { state } = useAuth();

const enrolledCourses = ref([]);
const records = ref([]);
const stats = ref({
  total_sessions: 0,
  present: 0,
  late: 0,
  excused: 0,
  absent: 0,
  attendance_rate: 0,
});

const loadStudentData = async () => {
  if (!state.user?.id) return;
  try {
    const { data } = await api.get(`/attendance/students/${state.user.id}/history`);
    records.value = data.records || [];
    enrolledCourses.value = data.enrolled_courses || [];
    stats.value = data.stats || stats.value;
  } catch (err) {
    console.error('Failed to load student history:', err);
  }
};

onMounted(() => {
  loadStudentData();
});

const formatDate = (isoString) => {
  if (!isoString) return '';
  const d = new Date(isoString);
  return d.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'present':
      return 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
    case 'late':
      return 'bg-amber-500/15 text-amber-400 border border-amber-500/30';
    case 'excused':
      return 'bg-indigo-500/15 text-indigo-400 border border-indigo-500/30';
    default:
      return 'bg-rose-500/15 text-rose-400 border border-rose-500/30';
  }
};
</script>
