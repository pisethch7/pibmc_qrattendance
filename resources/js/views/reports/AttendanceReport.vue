<template>
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex flex-wrap items-center gap-2.5">
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white font-['Outfit']">
            Attendance Reports
          </h1>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center space-x-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
            <span>Audit & Analytics</span>
          </span>
        </div>
        <p class="mt-1 text-xs sm:text-sm text-slate-400 max-w-2xl">
          Real-time verification audit trail, device geolocation logs, and class roster statistics
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <button
          @click="exportCSV"
          :disabled="roster.length === 0"
          class="px-4 py-2.5 rounded-xl bg-slate-900/90 hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-700/80 shadow-sm flex items-center space-x-2 active:scale-95 transition-all disabled:opacity-50 disabled:pointer-events-none"
        >
          <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <span>Export CSV</span>
        </button>
      </div>
    </div>

    <!-- Filter & Session Selector Bar -->
    <div class="glass-panel p-4 sm:p-5 rounded-3xl border border-slate-800/80 shadow-lg space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        <!-- Course Selector -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            Course
          </label>
          <div class="relative">
            <select
              v-model="selectedCourseId"
              @change="onCourseChange"
              class="w-full pl-3.5 pr-9 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/50 appearance-none transition-colors"
            >
              <option value="">-- All Courses --</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </div>
        </div>

        <!-- Session Selector -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            Class Session
          </label>
          <div class="relative">
            <select
              v-model="selectedSessionId"
              @change="onSessionChange"
              class="w-full pl-3.5 pr-9 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/50 appearance-none transition-colors"
            >
              <option value="">-- Select Session --</option>
              <option v-for="s in courseSessions" :key="s.id" :value="s.id">
                {{ formatDateTime(s.started_at) }} ({{ s.status }})
              </option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </div>
        </div>

        <!-- Search Student Input -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            Search Student
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name or email..."
              class="w-full pl-9 pr-9 py-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-colors"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-white"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Status Filter Pills -->
      <div class="pt-2 border-t border-slate-800/80 flex flex-wrap items-center gap-2">
        <span class="text-[11px] font-semibold text-slate-500 mr-1">Status:</span>
        <button
          v-for="pill in statusPills"
          :key="pill.id"
          type="button"
          @click="selectedStatusFilter = pill.id"
          class="px-2.5 py-1 rounded-xl text-xs font-semibold transition-all flex items-center space-x-1.5 active:scale-95"
          :class="selectedStatusFilter === pill.id
            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
            : 'bg-slate-900/70 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800'"
        >
          <span>{{ pill.label }}</span>
          <span
            class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
            :class="selectedStatusFilter === pill.id ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400'"
          >
            {{ pill.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- Summary KPI Cards Strip -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
      <!-- Total Enrolled -->
      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Enrolled</span>
          <div class="w-7 h-7 rounded-lg bg-slate-800/80 text-slate-400 flex items-center justify-center">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
        </div>
        <div class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-white font-['Outfit']">
          {{ counts.total_enrolled }}
        </div>
        <div class="mt-1 text-[11px] text-slate-500">Students in roster</div>
      </div>

      <!-- Present -->
      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">Present</span>
          <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          </div>
        </div>
        <div class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-emerald-400 font-['Outfit']">
          {{ counts.present }}
        </div>
        <div class="mt-1 text-[11px] text-emerald-400/70">On-time checks</div>
      </div>

      <!-- Late -->
      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-semibold text-amber-400 uppercase tracking-wider">Late</span>
          <div class="w-7 h-7 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
        </div>
        <div class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-amber-400 font-['Outfit']">
          {{ counts.late }}
        </div>
        <div class="mt-1 text-[11px] text-amber-400/70">After start buffer</div>
      </div>

      <!-- Absent -->
      <div class="glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-semibold text-rose-400 uppercase tracking-wider">Absent</span>
          <div class="w-7 h-7 rounded-lg bg-rose-500/15 text-rose-400 flex items-center justify-center">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
          </div>
        </div>
        <div class="mt-2.5 text-2xl sm:text-3xl font-extrabold text-rose-400 font-['Outfit']">
          {{ counts.absent }}
        </div>
        <div class="mt-1 text-[11px] text-rose-400/70">{{ counts.excused }} excused</div>
      </div>

      <!-- Turnout Rate Card -->
      <div class="col-span-2 lg:col-span-1 glass-panel p-4 sm:p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-semibold text-indigo-300 uppercase tracking-wider">Attendance Rate</span>
          <span class="w-2 h-2 rounded-full" :class="attendanceRate >= 80 ? 'bg-emerald-400' : 'bg-amber-400'"></span>
        </div>
        <div class="mt-2 flex items-baseline space-x-2">
          <span class="text-2xl sm:text-3xl font-extrabold font-['Outfit'] text-white">{{ attendanceRate }}%</span>
          <span class="text-xs text-slate-400">target 80%</span>
        </div>
        <!-- Segmented progress bar -->
        <div class="mt-2.5 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden flex">
          <div :style="{ width: presentRate + '%' }" class="bg-emerald-400 h-full"></div>
          <div :style="{ width: lateRate + '%' }" class="bg-amber-400 h-full"></div>
          <div :style="{ width: absentRate + '%' }" class="bg-rose-400 h-full"></div>
        </div>
      </div>
    </div>

    <!-- Roster Details Panel: Desktop Table & Mobile Cards -->
    <div class="glass-panel rounded-3xl border border-slate-800/80 overflow-hidden shadow-xl">
      <!-- Table Header Bar -->
      <div class="p-4 sm:p-5 border-b border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
        <div>
          <h2 class="text-base font-bold text-white font-['Outfit'] flex items-center space-x-2">
            <span>Roster Attendance Audit</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-800 text-slate-400">
              {{ filteredRoster.length }} of {{ roster.length }}
            </span>
          </h2>
          <p class="text-xs text-slate-400 mt-0.5" v-if="currentSession">
            Session started: <strong class="text-slate-300">{{ formatDateTime(currentSession.started_at) }}</strong> •
            Mode: <span class="capitalize text-slate-300">{{ currentSession.verification_mode === 'wifi' ? 'Campus WiFi Subnet' : `Classroom GPS (±${currentSession.radius_meters}m)` }}</span>
          </p>
        </div>

        <div v-if="selectedStatusFilter !== 'all' || searchQuery" class="flex items-center space-x-2">
          <button
            @click="resetFilters"
            class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold"
          >
            Clear filters
          </button>
        </div>
      </div>

      <!-- Mobile Cards View (sm:hidden) -->
      <div class="block sm:hidden divide-y divide-slate-800/70">
        <div
          v-for="st in filteredRoster"
          :key="st.student_id"
          class="p-4 space-y-3 bg-slate-950/30 hover:bg-slate-900/40 transition-colors"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="flex items-center space-x-3 min-w-0">
              <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 text-slate-200 flex items-center justify-center font-bold text-xs shadow-inner flex-shrink-0">
                {{ st.name?.charAt(0) || 'S' }}
              </div>
              <div class="min-w-0">
                <div class="font-semibold text-sm text-white truncate">{{ st.name }}</div>
                <div class="text-[11px] text-slate-400 truncate">{{ st.email }}</div>
              </div>
            </div>

            <span
              class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center space-x-1.5 flex-shrink-0"
              :class="getStatusBadgeClass(st.status)"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(st.status)"></span>
              <span>{{ st.status }}</span>
            </span>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs text-slate-400 bg-slate-900/50 p-2.5 rounded-xl border border-slate-800/60">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-500 block">Check-in Time</span>
              <span class="text-slate-200 font-mono text-[11px]">{{ st.checked_in_at ? formatTime(st.checked_in_at) : 'Not checked in' }}</span>
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-500 block">Verification</span>
              <span v-if="st.method" class="capitalize font-semibold text-[11px] text-indigo-300">{{ st.method }}</span>
              <span v-else class="text-slate-500">—</span>
            </div>
            <div v-if="st.latitude && st.longitude" class="col-span-2 pt-1 border-t border-slate-800/60 text-[10px] font-mono text-slate-400">
              GPS: {{ st.latitude.toFixed(5) }}°, {{ st.longitude.toFixed(5) }}°
            </div>
          </div>

          <div v-if="isTeacher" class="flex justify-end pt-1">
            <button
              @click="openOverrideModal(st)"
              class="px-3 py-1.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/20 text-xs font-semibold active:scale-95 transition-all"
            >
              Change Status
            </button>
          </div>
        </div>

        <div v-if="filteredRoster.length === 0" class="py-14 text-center space-y-2">
          <div class="w-12 h-12 mx-auto rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          </div>
          <p class="text-xs font-semibold text-slate-300">No student records found</p>
          <p class="text-[11px] text-slate-500">Try adjusting your search query or status filter.</p>
        </div>
      </div>

      <!-- Desktop & Tablet Table (sm:block) -->
      <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
            <tr>
              <th class="py-3.5 px-5">Student</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4">Timestamp</th>
              <th class="py-3.5 px-4">Verification</th>
              <th class="py-3.5 px-4">GPS Audit Coordinates</th>
              <th class="py-3.5 px-5 text-right" v-if="isTeacher">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <tr
              v-for="st in filteredRoster"
              :key="st.student_id"
              class="hover:bg-slate-900/50 transition-colors group"
            >
              <td class="py-3.5 px-5">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 text-slate-200 flex items-center justify-center font-bold text-xs shadow-inner flex-shrink-0">
                    {{ st.name?.charAt(0) || 'S' }}
                  </div>
                  <div>
                    <div class="font-semibold text-white group-hover:text-indigo-300 transition-colors">{{ st.name }}</div>
                    <div class="text-[11px] text-slate-400">{{ st.email }}</div>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-4">
                <span
                  class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="getStatusBadgeClass(st.status)"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(st.status)"></span>
                  <span>{{ st.status }}</span>
                </span>
              </td>
              <td class="py-3.5 px-4 text-slate-300 font-mono text-[11px]">
                {{ st.checked_in_at ? formatTime(st.checked_in_at) : '—' }}
              </td>
              <td class="py-3.5 px-4">
                <span
                  v-if="st.method"
                  class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-slate-800/90 text-slate-300 text-[11px] font-medium uppercase border border-slate-700/60"
                >
                  <span class="capitalize">{{ st.method }}</span>
                </span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td class="py-3.5 px-4 font-mono text-[11px] text-slate-400">
                <span v-if="st.latitude && st.longitude" class="flex items-center space-x-1.5">
                  <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  </svg>
                  <span>{{ st.latitude.toFixed(5) }}°, {{ st.longitude.toFixed(5) }}°</span>
                </span>
                <span v-else class="text-slate-500">None</span>
              </td>
              <td class="py-3.5 px-5 text-right" v-if="isTeacher">
                <button
                  @click="openOverrideModal(st)"
                  class="px-3 py-1.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/20 text-xs font-semibold active:scale-95 transition-all"
                >
                  Override
                </button>
              </td>
            </tr>

            <tr v-if="filteredRoster.length === 0">
              <td colspan="6" class="py-16 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500 mb-2">
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-300">No student attendance records match your filter</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Clear search or choose a different session.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Manual Status Override Modal Dialog -->
    <div v-if="showOverrideModal && selectedStudent" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-sm p-6 rounded-3xl border border-slate-800 shadow-2xl relative space-y-4">
        <button @click="showOverrideModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div>
          <h3 class="text-lg font-bold text-white font-['Outfit']">
            Override Attendance
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">
            Adjusting attendance record for <strong class="text-white">{{ selectedStudent.name }}</strong>
          </p>
        </div>

        <div class="space-y-2 pt-1">
          <button
            v-for="opt in ['present', 'late', 'excused', 'absent']"
            :key="opt"
            type="button"
            @click="submitOverride(opt)"
            class="w-full py-2.5 px-4 rounded-xl border text-xs font-bold uppercase tracking-wider transition-all text-left flex items-center justify-between active:scale-95"
            :class="selectedStudent.status === opt ? 'bg-indigo-600/30 border-indigo-500 text-white shadow-md' : 'bg-slate-900 border-slate-800 text-slate-300 hover:bg-slate-800'"
          >
            <div class="flex items-center space-x-2">
              <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(opt)"></span>
              <span>{{ opt }}</span>
            </div>
            <span v-if="selectedStudent.status === opt" class="text-[10px] font-bold text-indigo-400">Current</span>
          </button>
        </div>

        <div class="pt-2">
          <button
            type="button"
            @click="showOverrideModal = false"
            class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-slate-400 hover:text-white"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../../stores/auth';
import { useToast } from '../../composables/useToast';
import api from '../../services/api';

const { isTeacher } = useAuth();
const { showToast } = useToast();

const courses = ref([]);
const selectedCourseId = ref('');
const courseSessions = ref([]);
const selectedSessionId = ref('');
const currentSession = ref(null);

const counts = ref({ total_enrolled: 0, present: 0, late: 0, excused: 0, absent: 0 });
const roster = ref([]);
const searchQuery = ref('');
const selectedStatusFilter = ref('all');

const showOverrideModal = ref(false);
const selectedStudent = ref(null);

const attendanceRate = computed(() => {
  if (!counts.value.total_enrolled) return 0;
  return Math.round(((counts.value.present + counts.value.late) / counts.value.total_enrolled) * 100);
});

const presentRate = computed(() => {
  if (!counts.value.total_enrolled) return 0;
  return Math.round((counts.value.present / counts.value.total_enrolled) * 100);
});

const lateRate = computed(() => {
  if (!counts.value.total_enrolled) return 0;
  return Math.round((counts.value.late / counts.value.total_enrolled) * 100);
});

const absentRate = computed(() => {
  if (!counts.value.total_enrolled) return 0;
  return Math.round((counts.value.absent / counts.value.total_enrolled) * 100);
});

const statusPills = computed(() => [
  { id: 'all', label: 'All', count: roster.value.length },
  { id: 'present', label: 'Present', count: counts.value.present },
  { id: 'late', label: 'Late', count: counts.value.late },
  { id: 'absent', label: 'Absent', count: counts.value.absent },
  { id: 'excused', label: 'Excused', count: counts.value.excused },
]);

const filteredRoster = computed(() => {
  let list = roster.value;

  if (selectedStatusFilter.value !== 'all') {
    list = list.filter((s) => s.status === selectedStatusFilter.value);
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(
      (s) => s.name?.toLowerCase().includes(q) || s.email?.toLowerCase().includes(q)
    );
  }

  return list;
});

const resetFilters = () => {
  searchQuery.value = '';
  selectedStatusFilter.value = 'all';
};

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
    counts.value = data.counts || { total_enrolled: 0, present: 0, late: 0, excused: 0, absent: 0 };
    roster.value = data.roster || [];
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
    showToast(`Updated ${selectedStudent.value.name}'s status to ${newStatus}`, 'success');
    await onSessionChange();
  } catch (err) {
    showToast('Failed to override status: ' + (err.response?.data?.message || err.message), 'error');
  }
};

const exportCSV = () => {
  if (roster.value.length === 0) {
    showToast('No records to export.', 'warning');
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
  showToast('Downloaded attendance CSV export', 'success');
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

const getStatusDotClass = (status) => {
  switch (status) {
    case 'present': return 'bg-emerald-400';
    case 'late': return 'bg-amber-400';
    case 'excused': return 'bg-indigo-400';
    default: return 'bg-rose-400';
  }
};

onMounted(() => {
  loadInitial();
});
</script>
