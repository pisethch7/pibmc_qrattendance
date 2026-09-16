<template>
  <div class="min-h-[calc(100vh-4rem)] p-4 sm:p-6 lg:p-8 flex flex-col space-y-6">
    <!-- Top Bar -->
    <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center space-x-3">
        <router-link to="/teacher" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </router-link>

        <div>
          <div class="flex items-center space-x-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full" :class="isSessionOpen ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
              {{ isSessionOpen ? 'Live Rotating QR Session' : 'Session Ended' }}
            </span>
          </div>
          <h1 class="text-xl sm:text-2xl font-extrabold text-white font-['Outfit']">
            {{ session?.course?.name || 'Loading Course...' }}
          </h1>
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <!-- Verification Mode Badge -->
        <div v-if="session?.verification_mode === 'wifi'" class="hidden sm:flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-teal-500/10 border border-teal-500/30 text-xs text-teal-300">
          <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
          <span>Option 2: <strong>School WiFi</strong></span>
        </div>
        <div v-else class="hidden sm:flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-xs text-indigo-300">
          <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Option 1: <strong>Location ({{ session?.radius_meters || 100 }}m)</strong></span>
        </div>

        <!-- Export QR to JPG Button -->
        <button
          v-if="isSessionOpen"
          @click="exportQrToJpg"
          :disabled="exportingJpg"
          class="flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700 text-xs font-semibold shadow-sm hover:border-indigo-500 transition-colors"
          title="Export QR Code as JPG image"
        >
          <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>Export JPG</span>
        </button>

        <!-- Fullscreen Toggle -->
        <button
          @click="toggleFullscreen"
          class="p-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 transition-colors"
          title="Toggle Fullscreen Projector"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
          </svg>
        </button>

        <!-- Close Session Button -->
        <button
          v-if="isSessionOpen"
          @click="closeSession"
          class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold shadow-md shadow-rose-600/30 transition-colors"
        >
          Close Session
        </button>
      </div>
    </div>

    <!-- Main Grid: Left Rotating QR & Countdown, Right Live Roster -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
      <!-- QR Code Projector Card (7 Cols) -->
      <div class="lg:col-span-7 glass-panel p-6 sm:p-10 rounded-3xl border border-slate-800 flex flex-col items-center justify-center text-center relative overflow-hidden">
        <div v-if="!isSessionOpen" class="text-center py-16 space-y-4">
          <div class="w-20 h-20 mx-auto rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
          <h2 class="text-2xl font-bold text-white font-['Outfit']">This Session is Closed</h2>
          <p class="text-sm text-slate-400 max-w-sm">No new scans will be accepted. You can still view or adjust the final attendance report.</p>
          <router-link to="/reports" class="inline-block px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25">
            View Final Report
          </router-link>
        </div>

        <div v-else class="w-full flex flex-col items-center">
          <!-- Instruction -->
          <div class="mb-6 text-center">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
              Scan with phone camera or PIBMC app
            </span>
            <p class="mt-2 text-xs text-slate-400">
              QR refreshes dynamically every 1h • Anti-screenshot protection active
            </p>
          </div>

          <!-- QR Canvas Container with Glow -->
          <div class="relative p-6 rounded-3xl bg-white shadow-2xl shadow-indigo-500/20 flex items-center justify-center">
            <canvas ref="qrCanvas" class="rounded-xl w-64 h-64 sm:w-80 sm:h-80"></canvas>
          </div>

          <!-- Export to JPG Quick Action -->
          <div class="mt-4 flex items-center justify-center">
            <button
              type="button"
              @click="exportQrToJpg"
              class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-slate-900/90 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 hover:border-slate-700 text-xs font-medium transition-all shadow-sm"
            >
              <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>Export QR as JPG</span>
            </button>
          </div>

          <!-- Rotating Countdown Bar -->
          <div class="mt-6 w-full max-w-xs flex flex-col items-center space-y-2">
            <div class="flex items-center justify-between w-full text-xs font-semibold">
              <span class="text-slate-400 flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Token expires in:</span>
              </span>
              <span class="font-mono text-indigo-400 font-bold text-sm">
                {{ formattedCountdown }}
              </span>
            </div>

            <!-- Progress line -->
            <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 transition-all duration-1000 ease-linear rounded-full"
                :style="{ width: `${(secondsRemaining / totalValidity) * 100}%` }"
              ></div>
            </div>

            <div class="text-[11px] text-slate-500 font-mono tracking-wider pt-1">
              Token: {{ currentTokenString.slice(0, 8) }}••••••••{{ currentTokenString.slice(-6) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Live Roster & Attendance Feed (5 Cols) -->
      <div class="lg:col-span-5 glass-panel p-6 rounded-3xl border border-slate-800 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
              <h3 class="text-base font-bold text-white font-['Outfit']">
                Live Attendance Feed
              </h3>
              <p class="text-xs text-slate-400">
                Real-time check-in updates
              </p>
            </div>

            <!-- Present Counter Badge -->
            <div class="flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-bold">
              <span>{{ counts.present + counts.late }}</span>
              <span class="text-slate-500 font-normal">/</span>
              <span>{{ counts.total_enrolled }}</span>
              <span class="text-[10px] font-normal text-emerald-400">checked in</span>
            </div>
          </div>

          <!-- Roster list -->
          <div class="mt-4 space-y-2.5 max-h-[500px] overflow-y-auto pr-1">
            <div
              v-for="st in roster"
              :key="st.student_id"
              class="p-3 rounded-2xl border transition-all flex items-center justify-between"
              :class="getStatusCardClass(st.status)"
            >
              <div class="flex items-center space-x-3">
                <div
                  class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs"
                  :class="getStatusAvatarClass(st.status)"
                >
                  {{ st.name.charAt(0) }}
                </div>
                <div>
                  <div class="text-sm font-semibold text-white leading-tight">
                    {{ st.name }}
                  </div>
                  <div class="text-[11px] text-slate-400 flex items-center space-x-1.5 mt-0.5">
                    <span v-if="st.checked_in_at" class="text-slate-300">
                      {{ formatTime(st.checked_in_at) }}
                    </span>
                    <span v-if="st.method" class="text-[10px] uppercase font-bold px-1.5 py-0.2 rounded bg-slate-800/80 text-slate-400">
                      {{ st.method }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Status Badge & Action -->
              <div class="flex items-center space-x-2">
                <span
                  class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider"
                  :class="getStatusBadgeClass(st.status)"
                >
                  {{ st.status }}
                </span>

                <button
                  @click="openOverrideModal(st)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                  title="Manual Override"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </button>
              </div>
            </div>

            <div v-if="roster.length === 0" class="text-center py-10 text-xs text-slate-400">
              No students enrolled in this course yet.
            </div>
          </div>
        </div>

        <!-- Roster Summary Footer -->
        <div class="pt-4 border-t border-slate-800/80 mt-4 flex items-center justify-between text-xs text-slate-400">
          <div class="flex items-center space-x-4">
            <span class="flex items-center space-x-1">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              <span>Present: {{ counts.present }}</span>
            </span>
            <span class="flex items-center space-x-1">
              <span class="w-2 h-2 rounded-full bg-amber-400"></span>
              <span>Late: {{ counts.late }}</span>
            </span>
            <span class="flex items-center space-x-1">
              <span class="w-2 h-2 rounded-full bg-rose-400"></span>
              <span>Absent: {{ counts.absent }}</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Manual Override Modal -->
    <div v-if="showOverrideModal && selectedStudent" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
      <div class="glass-panel w-full max-w-sm p-6 rounded-2xl border border-slate-800 shadow-2xl relative">
        <button @click="showOverrideModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h3 class="text-lg font-bold text-white font-['Outfit'] mb-1">
          Manual Attendance Override
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import QRCode from 'qrcode';
import api from '../../services/api';

const route = useRoute();
const sessionId = route.params.id;

const session = ref(null);
const isSessionOpen = ref(true);
const currentTokenString = ref('');
const secondsRemaining = ref(3600);
const totalValidity = ref(3600);
const qrCanvas = ref(null);

const counts = ref({ total_enrolled: 0, present: 0, late: 0, excused: 0, absent: 0 });
const roster = ref([]);

const showOverrideModal = ref(false);
const selectedStudent = ref(null);

let countdownInterval = null;
let tokenPollInterval = null;
let rosterPollInterval = null;

const renderQRCode = (token) => {
  if (!qrCanvas.value || !token) return;

  // The QR code encodes a full student check-in URL + token query param
  const checkInUrl = `${window.location.origin}/student/scan?token=${encodeURIComponent(token)}`;

  QRCode.toCanvas(
    qrCanvas.value,
    checkInUrl,
    {
      width: 320,
      margin: 2,
      color: {
        dark: '#090d16',
        light: '#ffffff',
      },
      errorCorrectionLevel: 'H',
    },
    (err) => {
      if (err) console.error('QR Render Error:', err);
    }
  );
};

const fetchToken = async () => {
  try {
    const { data } = await api.get(`/attendance/sessions/${sessionId}/token`);
    currentTokenString.value = data.token;
    secondsRemaining.value = data.seconds_remaining || 3600;
    isSessionOpen.value = data.status === 'open';
    renderQRCode(data.token);
  } catch (err) {
    if (err.response?.status === 410 || err.response?.data?.status === 'closed') {
      isSessionOpen.value = false;
    }
  }
};

const fetchReport = async () => {
  try {
    const { data } = await api.get(`/attendance/sessions/${sessionId}/report`);
    session.value = data.session;
    counts.value = data.counts;
    roster.value = data.roster;
    isSessionOpen.value = data.session.status === 'open';
  } catch (err) {
    console.error('Error fetching session report:', err);
  }
};

const startTimers = () => {
  // Local second-by-second smooth countdown
  countdownInterval = setInterval(() => {
    if (!isSessionOpen.value) return;
    if (secondsRemaining.value > 1) {
      secondsRemaining.value -= 1;
    } else {
      // Time to refresh token!
      fetchToken();
    }
  }, 1000);

  // Periodic token sync with server (every 60 seconds — token is valid for 1 hour)
  tokenPollInterval = setInterval(() => {
    if (isSessionOpen.value) {
      fetchToken();
    }
  }, 60000);

  // Periodic live roster polling (every 3 seconds for fast updates as students scan)
  rosterPollInterval = setInterval(() => {
    fetchReport();
  }, 3000);
};

onMounted(async () => {
  await fetchReport();
  await fetchToken();
  startTimers();
});

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval);
  if (tokenPollInterval) clearInterval(tokenPollInterval);
  if (rosterPollInterval) clearInterval(rosterPollInterval);
});

const closeSession = async () => {
  if (!confirm('Are you sure you want to end this attendance session?')) return;
  try {
    await api.post(`/attendance/sessions/${sessionId}/close`);
    isSessionOpen.value = false;
    await fetchReport();
  } catch (err) {
    alert('Failed to close session: ' + (err.response?.data?.message || err.message));
  }
};

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch((err) => {
      alert(`Error attempting to enable fullscreen: ${err.message}`);
    });
  } else {
    document.exitFullscreen();
  }
};

const openOverrideModal = (student) => {
  selectedStudent.value = student;
  showOverrideModal.value = true;
};

const submitOverride = async (newStatus) => {
  if (!selectedStudent.value) return;
  try {
    await api.post(`/attendance/sessions/${sessionId}/manual-record`, {
      student_id: selectedStudent.value.student_id,
      status: newStatus,
    });
    showOverrideModal.value = false;
    await fetchReport();
  } catch (err) {
    alert('Failed to update status: ' + (err.response?.data?.message || err.message));
  }
};

const formatTime = (isoString) => {
  if (!isoString) return '';
  const date = new Date(isoString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

// Format seconds as mm:ss for the 1-hour countdown
const formattedCountdown = computed(() => {
  const s = secondsRemaining.value;
  const m = Math.floor(s / 60);
  const sec = s % 60;
  return `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
});

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'present':
      return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30';
    case 'late':
      return 'bg-amber-500/10 text-amber-400 border border-amber-500/30';
    case 'excused':
      return 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/30';
    default:
      return 'bg-slate-800 text-slate-400 border border-slate-700';
  }
};

const getStatusCardClass = (status) => {
  switch (status) {
    case 'present':
      return 'bg-emerald-950/20 border-emerald-500/30';
    case 'late':
      return 'bg-amber-950/20 border-amber-500/30';
    case 'excused':
      return 'bg-indigo-950/20 border-indigo-500/30';
    default:
      return 'bg-slate-900/40 border-slate-800/80';
  }
};

const getStatusAvatarClass = (status) => {
  switch (status) {
    case 'present':
      return 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30';
    case 'late':
      return 'bg-amber-500 text-white shadow-md shadow-amber-500/30';
    case 'excused':
      return 'bg-indigo-500 text-white shadow-md shadow-indigo-500/30';
    default:
      return 'bg-slate-800 text-slate-400';
  }
};

const exportingJpg = ref(false);

const exportQrToJpg = () => {
  if (!qrCanvas.value) return;

  exportingJpg.value = true;
  try {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    const width = 800;
    const height = 1000;
    canvas.width = width;
    canvas.height = height;

    // Fill white background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // Header banner (dark slate)
    ctx.fillStyle = '#090d16';
    ctx.fillRect(0, 0, width, 160);

    // Header title
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 34px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('PIBMC ATTENDANCE QR', width / 2, 60);

    // Course Name
    ctx.fillStyle = '#818cf8';
    ctx.font = 'bold 22px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    const courseName = session.value?.course?.name || 'Class Session';
    ctx.fillText(courseName, width / 2, 105);

    // Verification Mode text in header
    ctx.fillStyle = '#94a3b8';
    ctx.font = '16px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    const modeBadge = session.value?.verification_mode === 'wifi'
      ? 'Verification Mode: School WiFi Network'
      : `Verification Mode: Classroom GPS Geofence (${session.value?.radius_meters || 100}m radius)`;
    ctx.fillText(modeBadge, width / 2, 138);

    // Draw QR Code centered
    const qrSize = 540;
    const qrX = (width - qrSize) / 2;
    const qrY = 210;

    // Border around QR
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 3;
    ctx.strokeRect(qrX - 12, qrY - 12, qrSize + 24, qrSize + 24);

    ctx.drawImage(qrCanvas.value, qrX, qrY, qrSize, qrSize);

    // Instructions below QR
    ctx.fillStyle = '#0f172a';
    ctx.font = 'bold 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.fillText('Scan with mobile camera or import into PIBMC Student Portal', width / 2, 825);

    // Date & Session details
    const nowStr = new Date().toLocaleString();
    ctx.fillStyle = '#64748b';
    ctx.font = '15px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.fillText(`Generated: ${nowStr} • Session #${sessionId}`, width / 2, 860);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '13px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.fillText('One-time attendance token with rotation security active', width / 2, 895);

    // Convert to JPG
    const jpgUrl = canvas.toDataURL('image/jpeg', 0.95);

    // Trigger download
    const link = document.createElement('a');
    const safeTitle = courseName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
    link.download = `PIBMC_QR_${safeTitle}_${Date.now()}.jpg`;
    link.href = jpgUrl;
    link.click();
  } catch (err) {
    console.error('Failed to export QR to JPG:', err);
    alert('Failed to export QR code image: ' + err.message);
  } finally {
    exportingJpg.value = false;
  }
};
</script>
