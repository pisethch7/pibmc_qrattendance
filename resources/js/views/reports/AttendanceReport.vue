<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-3">
          <h1 class="text-3xl font-extrabold tracking-tight text-white font-['Outfit']">
            Attendance Reports
          </h1>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            Analytics & Audits
          </span>
        </div>
        <p class="mt-1 text-sm text-slate-400">
          Per-session verification logs, GPS audit coordinates, and student attendance statistics
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <button
          @click="exportCSV"
          class="px-4 py-2 rounded-xl glass-card hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-700/80 transition-colors flex items-center space-x-1.5"
        >
          <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span>Export CSV</span>
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="glass-panel p-4 rounded-2xl border border-slate-800 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Course Selector -->
      <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
          Filter by Course
        </label>
        <select
          v-model="selectedCourseId"
          @change="onCourseChange"
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
        >
          <option value="">-- All Courses --</option>
          <option v-for="c in courses" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
        </select>
      </div>

      <!-- Session Selector -->
      <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
          Select Session
        </label>
        <select
          v-model="selectedSessionId"
          @change="onSessionChange"
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
        >
          <option value="">-- Latest Active/Recent Session --</option>
          <option v-for="s in courseSessions" :key="s.id" :value="s.id">
            {{ formatDateTime(s.started_at) }} ({{ s.status }})
          </option>
        </select>
      </div>

      <!-- Search Student -->
      <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
          Search Student
        </label>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Filter by student name or email..."
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
        />
      </div>
    </div>

    <!-- Summary Stats Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div class="glass-panel p-4 rounded-2xl border border-slate-800">
        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Enrolled</span>
        <div class="mt-2 text-2xl font-extrabold text-white font-['Outfit']">
          {{ counts.total_enrolled }}
        </div>
      </div>
      <div class="glass-panel p-4 rounded-2xl border border-slate-800">
        <span class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">Present</span>
        <div class="mt-2 text-2xl font-extrabold text-emerald-400 font-['Outfit']">
          {{ counts.present }}
        </div>
      </div>
      <div class="glass-panel p-4 rounded-2xl border border-slate-800">
        <span class="text-[11px] font-semibold text-amber-400 uppercase tracking-wider">Late</span>
        <div class="mt-2 text-2xl font-extrabold text-amber-400 font-['Outfit']">
          {{ counts.late }}
        </div>
      </div>
      <div class="glass-panel p-4 rounded-2xl border border-slate-800">
        <span class="text-[11px] font-semibold text-rose-400 uppercase tracking-wider">Absent</span>
        <div class="mt-2 text-2xl font-extrabold text-rose-400 font-['Outfit']">
          {{ counts.absent }}
        </div>
      </div>
    </div>

    <!-- Roster Details: Mobile Cards & Desktop Table -->
    <div class="glass-panel rounded-3xl border border-slate-800 overflow-hidden">
      <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-white font-['Outfit']">
            Session Attendance & Audit Trail
          </h3>
          <p class="text-xs text-slate-400 mt-0.5" v-if="currentSession">
            Session: {{ formatDateTime(currentSession.started_at) }} • {{ currentSession.verification_mode === 'wifi' ? 'WiFi Subnet' : `GPS Geofence (±${currentSession.radius_meters}m)` }}
          </p>
        </div>
      </div>

      <!-- Phone View (App Cards) -->
      <div class="block sm:hidden divide-y divide-slate-800/80">
        <div
          v-for="st in filteredRoster"
          :key="st.student_id"
          class="p-4 space-y-2.5 bg-slate-950/40"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="flex items-center space-x-2.5 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-200 flex items-center justify-center font-bold text-xs flex-shrink-0">
                {{ st.name?.charAt(0) || 'S' }}
              </div>
              <div class="min-w-0">
                <div class="font-semibold text-sm text-white truncate">{{ st.name }}</div>
                <div class="text-[11px] text-slate-400 truncate">{{ st.email }}</div>
              </div>
            </div>

            <span
              class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider flex-shrink-0"
              :class="getStatusBadgeClass(st.status)"
            >
              {{ st.status }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400 pt-1">
            <div class="flex items-center space-x-1">
              <span class="text-slate-500">Time:</span>
              <span class="text-slate-200 font-mono">{{ st.checked_in_at ? formatTime(st.checked_in_at) : '—' }}</span>
            </div>
            <div class="flex items-center space-x-1">
              <span class="text-slate-500">Method:</span>
              <span v-if="st.method" class="uppercase font-bold text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300">{{ st.method }}</span>
              <span v-else>—</span>
            </div>
          </div>

          <div v-if="st.latitude && st.longitude" class="text-[10px] font-mono text-slate-500">
            GPS: {{ st.latitude.toFixed(5) }}°, {{ st.longitude.toFixed(5) }}°
          </div>

          <div v-if="isTeacher" class="pt-2 flex justify-end">
            <button
              @click="openOverrideModal(st)"
              class="px-3 py-1.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/20 text-xs font-semibold active:scale-95 transition-all"
            >
              Override Status
            </button>
          </div>
        </div>

        <div v-if="filteredRoster.length === 0" class="py-12 text-center text-slate-400 text-xs">
          No matching student attendance records found.
        </div>
      </div>

      <!-- Tablet & Desktop View (Full Table) -->
      <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
            <tr>
              <th class="py-3 px-4">Student</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4">Checked In At</th>
              <th class="py-3 px-4">Method</th>
              <th class="py-3 px-4">GPS Coordinates (Audit)</th>
              <th class="py-3 px-4 text-right" v-if="isTeacher">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <tr
              v-for="st in filteredRoster"
              :key="st.student_id"
              class="hover:bg-slate-900/40 transition-colors"
            >
              <td class="py-3 px-4">
                <div class="font-semibold text-white">{{ st.name }}</div>
                <div class="text-[11px] text-slate-400">{{ st.email }}</div>
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="getStatusBadgeClass(st.status)"
                >
                  {{ st.status }}
                </span>
              </td>
              <td class="py-3 px-4 text-slate-300 font-mono text-[11px]">
                {{ st.checked_in_at ? formatTime(st.checked_in_at) : '—' }}
              </td>
              <td class="py-3 px-4">
                <span v-if="st.method" class="uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                  {{ st.method }}
                </span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td class="py-3 px-4 font-mono text-[11px] text-slate-400">
                <span v-if="st.latitude && st.longitude">
                  {{ st.latitude.toFixed(5) }}°, {{ st.longitude.toFixed(5) }}°
                </span>
                <span v-else class="text-slate-500">None</span>
              </td>
              <td class="py-3 px-4 text-right" v-if="isTeacher">
                <button
                  @click="openOverrideModal(st)"
                  class="px-2.5 py-1 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/20 text-[11px] font-semibold transition-colors"
                >
                  Override
                </button>
              </td>
            </tr>

            <tr v-if="filteredRoster.length === 0">
              <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                No matching student attendance records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Manual Override Modal -->
    <div v-if="showOverrideModal && selectedStudent" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
      <div class="glass-panel w-full max-w-sm p-6 rounded-2xl border border-slate-800 shadow-2xl relative">
        <button @click="showOverrideModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h3 class="text-lg font-bold text-white font-['Outfit'] mb-1">
          Override Student Attendance
        </h3>
        <p class="text-xs text-slate-400 mb-4">
          Updating status for <strong>{{ selectedStudent.name }}</strong>
        </p>

        <div class="space-y-2">
          <button
            v-for="opt in ['present', 'late', 'excused', 'absent']"
            :key="opt"
            @click="submitOverride(opt)"
            class="w-full py-2.5 px-4 rounded-xl border text-xs font-bold uppercase tracking-wider transition-all text-left flex items-center justify-between"
            :class="selectedStudent.status === opt ? 'bg-indigo-600/30 border-indigo-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800'"
          >
            <span>{{ opt }}</span>
            <span v-if="selectedStudent.status === opt">✓ Current</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../../stores/auth';
import api from '../../services/api';

const { isTeacher } = useAuth();

const courses = ref([]);
const selectedCourseId = ref('');
const courseSessions = ref([]);
const selectedSessionId = ref('');
const currentSession = ref(null);

const counts = ref({ total_enrolled: 0, present: 0, late: 0, excused: 0, absent: 0 });
const roster = ref([]);
const searchQuery = ref('');

const showOverrideModal = ref(false);
const selectedStudent = ref(null);

const filteredRoster = computed(() => {
  if (!searchQuery.value.trim()) return roster.value;
  const q = searchQuery.value.toLowerCase();
  return roster.value.filter(
    (s) => s.name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q)
  );
});

const loadInitial = async () => {
  try {
    const { data } = await api.get('/courses');
    courses.value = data.courses || [];
    if (courses.value.length > 0) {
      selectedCourseId.value = courses.value[0].id;
      await onCourseChange();
    }
  } catch (err) {
    console.error('Failed to load courses:', err);
  }
};

const onCourseChange = async () => {
  if (!selectedCourseId.value) {
    courseSessions.value = [];
    selectedSessionId.value = '';
    return;
  }

  try {
    const { data } = await api.get(`/courses/${selectedCourseId.value}`);
    courseSessions.value = data.course.sessions || [];
    if (courseSessions.value.length > 0) {
      selectedSessionId.value = courseSessions.value[0].id;
      await onSessionChange();
    } else {
      currentSession.value = null;
      roster.value = [];
      counts.value = { total_enrolled: 0, present: 0, late: 0, excused: 0, absent: 0 };
    }
  } catch (err) {
    console.error('Failed to load course details:', err);
  }
};

const onSessionChange = async () => {
  if (!selectedSessionId.value) return;

  try {
    const { data } = await api.get(`/attendance/sessions/${selectedSessionId.value}/report`);
    currentSession.value = data.session;
    counts.value = data.counts;
    roster.value = data.roster;
  } catch (err) {
    console.error('Failed to load session report:', err);
  }
};

const openOverrideModal = (student) => {
  selectedStudent.value = student;
  showOverrideModal.value = true;
};

const submitOverride = async (newStatus) => {
  if (!selectedStudent.value || !selectedSessionId.value) return;

  try {
    await api.post(`/attendance/sessions/${selectedSessionId.value}/manual-record`, {
      student_id: selectedStudent.value.student_id,
      status: newStatus,
    });
    showOverrideModal.value = false;
    await onSessionChange();
  } catch (err) {
    alert('Failed to override status: ' + (err.response?.data?.message || err.message));
  }
};

const exportCSV = () => {
  if (roster.value.length === 0) {
    alert('No records to export.');
    return;
  }

  let csvContent = 'data:text/csv;charset=utf-8,';
  csvContent += 'Student Name,Email,Status,Checked In At,Method,Latitude,Longitude\n';

  roster.value.forEach((s) => {
    csvContent += `"${s.name}","${s.email}","${s.status}","${s.checked_in_at || ''}","${s.method || ''}","${s.latitude || ''}","${s.longitude || ''}"\n`;
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `attendance_report_${selectedSessionId.value || 'export'}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const formatTime = (isoString) => {
  if (!isoString) return '';
  const d = new Date(isoString);
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

const formatDateTime = (isoString) => {
  if (!isoString) return '';
  const d = new Date(isoString);
  return d.toLocaleDateString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
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

onMounted(() => {
  loadInitial();
});
</script>
