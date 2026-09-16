<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Top Greeting & CTA -->
    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-emerald-950/60 via-teal-950/40 to-slate-900 border border-emerald-500/30 glow-emerald flex flex-col md:flex-row md:items-center md:justify-between gap-6">
      <div class="space-y-2">
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          Student Attendance Portal
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-['Outfit']">
          Hello, {{ state.user?.name }}!
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
          Keep your attendance record high. When you arrive in class, tap below to scan the rotating QR code on the projector.
        </p>
      </div>

      <router-link
        to="/student/scan"
        class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold text-sm shadow-xl shadow-emerald-500/30 flex items-center justify-center space-x-2 transition-all hover:scale-105 flex-shrink-0"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
        </svg>
        <span>Scan Classroom QR</span>
      </router-link>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
      <div class="glass-panel p-5 rounded-2xl border border-slate-800">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Attendance Rate</span>
        <div class="mt-3 text-3xl font-extrabold font-['Outfit']" :class="stats.attendance_rate >= 80 ? 'text-emerald-400' : 'text-amber-400'">
          {{ stats.attendance_rate }}%
        </div>
        <div class="mt-1 text-[11px] text-slate-400">Target: 80%+</div>
      </div>

      <div class="glass-panel p-5 rounded-2xl border border-slate-800">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Present</span>
        <div class="mt-3 text-3xl font-extrabold text-emerald-400 font-['Outfit']">
          {{ stats.present }}
        </div>
        <div class="mt-1 text-[11px] text-slate-400">On-time attendances</div>
      </div>

      <div class="glass-panel p-5 rounded-2xl border border-slate-800">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Late</span>
        <div class="mt-3 text-3xl font-extrabold text-amber-400 font-['Outfit']">
          {{ stats.late }}
        </div>
        <div class="mt-1 text-[11px] text-slate-400">Past start time</div>
      </div>

      <div class="glass-panel p-5 rounded-2xl border border-slate-800">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Absent / Excused</span>
        <div class="mt-3 text-3xl font-extrabold text-slate-200 font-['Outfit']">
          {{ stats.absent }} <span class="text-xs font-normal text-slate-400">/ {{ stats.excused }} exc</span>
        </div>
        <div class="mt-1 text-[11px] text-slate-400">Total missed classes</div>
      </div>
    </div>

    <!-- Enrolled Courses & History -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Enrolled Courses (5 Cols) -->
      <div class="lg:col-span-5 space-y-4">
        <h2 class="text-lg font-bold text-white font-['Outfit']">
          Enrolled Courses
        </h2>

        <div v-if="enrolledCourses.length === 0" class="glass-panel p-8 rounded-2xl text-center border border-slate-800 text-xs text-slate-400">
          You are not currently enrolled in any courses.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="c in enrolledCourses"
            :key="c.id"
            class="glass-panel p-5 rounded-2xl border border-slate-800 space-y-2"
          >
            <div class="flex items-start justify-between">
              <h3 class="text-sm font-bold text-white">
                {{ c.name }}
              </h3>
              <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-800 text-slate-300">
                {{ c.sessions_count || 0 }} Sessions
              </span>
            </div>
            <p class="text-xs text-slate-400 flex items-center space-x-1.5">
              <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              <span>{{ c.teacher?.name || 'Faculty Member' }}</span>
            </p>
            <p class="text-xs text-slate-400 flex items-center space-x-1.5">
              <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>{{ c.schedule_info || 'Standard Schedule' }}</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Attendance History Timeline (7 Cols) -->
      <div class="lg:col-span-7 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-white font-['Outfit']">
            Recent Attendance History
          </h2>
          <router-link to="/reports" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
            View All Reports →
          </router-link>
        </div>

        <div v-if="records.length === 0" class="glass-panel p-8 rounded-2xl text-center border border-slate-800 text-xs text-slate-400">
          No attendance records recorded yet.
        </div>

        <div v-else class="glass-panel p-4 sm:p-6 rounded-2xl border border-slate-800 space-y-3">
          <div
            v-for="rec in records.slice(0, 10)"
            :key="rec.id"
            class="p-3.5 rounded-xl bg-slate-900/50 border border-slate-800/80 flex items-center justify-between"
          >
            <div>
              <div class="text-sm font-semibold text-white">
                {{ rec.session?.course?.name || 'Class Session' }}
              </div>
              <div class="text-[11px] text-slate-400 mt-0.5 flex items-center space-x-2">
                <span>{{ formatDate(rec.checked_in_at) }}</span>
                <span>•</span>
                <span class="capitalize">{{ rec.method }} check-in</span>
              </div>
            </div>

            <span
              class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
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
