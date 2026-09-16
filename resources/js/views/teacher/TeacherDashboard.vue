<template>
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2.5">
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white font-['Outfit']">
            Teacher Dashboard
          </h1>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
            Instructor
          </span>
        </div>
        <p class="mt-1 text-xs sm:text-sm text-slate-400">
          Manage courses, enroll students, and launch live rotating QR attendance sessions
        </p>
      </div>

      <div class="grid grid-cols-2 sm:flex sm:items-center gap-2.5 sm:gap-3">
        <button
          @click="openTelegramModal"
          class="px-3.5 sm:px-4 py-2.5 rounded-xl glass-card hover:bg-slate-800 text-slate-200 text-xs sm:text-sm font-semibold border border-slate-700/80 active:scale-95 transition-all flex items-center justify-center space-x-2"
          title="Telegram Bot status and test message"
        >
          <span class="relative flex h-2.5 w-2.5">
            <span v-if="telegramStatus?.configured && telegramStatus?.api_reachable" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="telegramStatus?.configured && telegramStatus?.api_reachable ? 'bg-emerald-500' : 'bg-amber-500'"></span>
          </span>
          <span>Telegram</span>
        </button>

        <button
          @click="showCreateCourseModal = true"
          class="px-3.5 sm:px-4 py-2.5 rounded-xl glass-card hover:bg-slate-800 text-slate-200 text-xs sm:text-sm font-semibold border border-slate-700/80 active:scale-95 transition-all flex items-center justify-center space-x-1.5 sm:space-x-2"
        >
          <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>New Course</span>
        </button>

        <button
          @click="openStartSessionModal()"
          class="px-4 sm:px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-indigo-500/25 active:scale-95 transition-all flex items-center justify-center space-x-1.5 sm:space-x-2"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Start Session</span>
        </button>
      </div>
    </div>

    <!-- Active Sessions Banner (iOS Live Activity Style) -->
    <div v-if="activeSessions.length > 0" class="p-4 sm:p-5 rounded-3xl bg-gradient-to-br from-indigo-950/80 via-purple-950/50 to-slate-900 border border-indigo-500/40 glow-indigo">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3.5">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
          </span>
          <div>
            <div class="text-[11px] font-bold text-indigo-300 uppercase tracking-wider">
              Live Attendance Session Active
            </div>
            <div class="text-sm sm:text-base font-bold text-white">
              {{ activeSessions[0].course?.name }}
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:space-x-3">
          <router-link
            :to="`/teacher/session/${activeSessions[0].id}`"
            class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition-all flex items-center justify-center space-x-1.5 shadow-md shadow-indigo-600/30 active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>Open Screen</span>
          </router-link>

          <button
            @click="closeSession(activeSessions[0].id)"
            class="px-3 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-semibold active:scale-95 transition-all text-center"
          >
            End Session
          </button>
        </div>
      </div>
    </div>

    <!-- Metrics Cards (Responsive on Phone and Tablet) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-5">
      <div class="glass-panel p-6 rounded-2xl border border-slate-800">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Courses Taught</span>
          <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
        <div class="mt-4 text-3xl font-extrabold text-white font-['Outfit']">
          {{ courses.length }}
        </div>
        <div class="mt-1 text-xs text-slate-400">Active class sections</div>
      </div>

      <div class="glass-panel p-6 rounded-2xl border border-slate-800">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Enrolled</span>
          <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 text-3xl font-extrabold text-white font-['Outfit']">
          {{ totalEnrolledStudents }}
        </div>
        <div class="mt-1 text-xs text-slate-400">Total student enrollments</div>
      </div>

      <div class="glass-panel p-6 rounded-2xl border border-slate-800">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Sessions Held</span>
          <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 text-3xl font-extrabold text-white font-['Outfit']">
          {{ totalSessions }}
        </div>
        <div class="mt-1 text-xs text-slate-400">All-time attendance checks</div>
      </div>
    </div>

    <!-- Courses Management Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-white font-['Outfit']">
          Your Courses
        </h2>
      </div>

      <div v-if="loading" class="text-center py-12 text-slate-400 text-sm">
        Loading courses...
      </div>

      <div v-else-if="courses.length === 0" class="glass-panel p-12 rounded-2xl text-center border border-slate-800">
        <div class="w-12 h-12 mx-auto rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-3">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        </div>
        <h3 class="text-base font-semibold text-white">No courses yet</h3>
        <p class="text-sm text-slate-400 mt-1">Create your first course to start taking attendance</p>
        <button
          @click="showCreateCourseModal = true"
          class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold"
        >
          Create Course
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div
          v-for="c in courses"
          :key="c.id"
          class="glass-panel p-6 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all flex flex-col justify-between"
        >
          <div>
            <div class="flex items-start justify-between gap-2">
              <h3 class="text-lg font-bold text-white font-['Outfit']">
                {{ c.name }}
              </h3>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 whitespace-nowrap">
                {{ c.students_count || 0 }} Students
              </span>
            </div>
            <p class="mt-2 text-xs text-slate-400 flex items-center space-x-1.5">
              <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ c.schedule_info || 'Schedule unassigned' }}</span>
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between gap-3">
            <button
              @click="openEnrollModal(c)"
              class="text-xs font-medium text-slate-300 hover:text-white px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors flex items-center space-x-1.5"
            >
              <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
              <span>Manage Students</span>
            </button>

            <button
              @click="openStartSessionModal(c.id)"
              class="text-xs font-semibold px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white shadow-md shadow-indigo-600/20 transition-all flex items-center space-x-1.5"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
              <span>Start QR Session</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Start Session -->
    <div v-if="showStartSessionModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-lg p-5 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button @click="showStartSessionModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h3 class="text-xl font-bold text-white font-['Outfit'] mb-1">
          Start Attendance Session
        </h3>
        <p class="text-xs text-slate-400 mb-5">
          Set up geofence boundary coordinates and launch rotating QR projector screen
        </p>

        <form @submit.prevent="handleStartSession" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
              Select Course
            </label>
            <select
              v-model="sessionForm.course_id"
              required
              class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
            >
              <option disabled value="">-- Choose a course --</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
          </div>

          <!-- Verification Method: Option 1 (Location) vs Option 2 (School WiFi) -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
              Presence Verification Method
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
              <!-- Option 1: Location -->
              <button
                type="button"
                @click="sessionForm.verification_mode = 'location'"
                class="p-3.5 rounded-xl border text-left transition-all"
                :class="sessionForm.verification_mode === 'location'
                  ? 'bg-indigo-600/20 border-indigo-500/60 ring-1 ring-indigo-500/40'
                  : 'bg-slate-900 border-slate-800 hover:border-slate-700 opacity-70 hover:opacity-100'"
              >
                <div class="flex items-center space-x-2.5 mb-1.5">
                  <div
                    class="p-1.5 rounded-lg"
                    :class="sessionForm.verification_mode === 'location' ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-400'"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-white block">Option 1</span>
                    <span class="text-[10px] text-indigo-300 font-medium">Classroom Location</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-400 leading-tight">
                  GPS geofence check. Requires students to be physically within radius.
                </p>
              </button>

              <!-- Option 2: School WiFi -->
              <button
                type="button"
                @click="sessionForm.verification_mode = 'wifi'"
                class="p-3.5 rounded-xl border text-left transition-all"
                :class="sessionForm.verification_mode === 'wifi'
                  ? 'bg-teal-600/20 border-teal-500/60 ring-1 ring-teal-500/40'
                  : 'bg-slate-900 border-slate-800 hover:border-slate-700 opacity-70 hover:opacity-100'"
              >
                <div class="flex items-center space-x-2.5 mb-1.5">
                  <div
                    class="p-1.5 rounded-lg"
                    :class="sessionForm.verification_mode === 'wifi' ? 'bg-teal-500 text-white' : 'bg-slate-800 text-slate-400'"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-white block">Option 2</span>
                    <span class="text-[10px] text-teal-300 font-medium">School WiFi</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-400 leading-tight">
                  School subnet check. Requires students to be on campus WiFi network.
                </p>
              </button>
            </div>
          </div>

          <!-- OPTION 1 FIELDS: Geofence GPS Coordinates & Radius -->
          <div v-if="sessionForm.verification_mode === 'location'" class="space-y-4 p-4 rounded-xl bg-slate-900/60 border border-slate-800 animate-in fade-in duration-200">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                  Classroom Coordinates (Lat / Long)
                </label>
                <button
                  type="button"
                  @click="detectLocation"
                  class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 flex items-center space-x-1"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <span>Detect My Location</span>
                </button>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <input
                  v-model.number="sessionForm.latitude"
                  type="number"
                  step="0.0000001"
                  :required="sessionForm.verification_mode === 'location'"
                  placeholder="Latitude (e.g. 11.5564)"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm"
                />
                <input
                  v-model.number="sessionForm.longitude"
                  type="number"
                  step="0.0000001"
                  :required="sessionForm.verification_mode === 'location'"
                  placeholder="Longitude (e.g. 104.9282)"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm"
                />
              </div>
              <div class="mt-2 flex items-center space-x-2 text-[11px] text-slate-400">
                <button
                  type="button"
                  @click="setCampusPreset"
                  class="underline hover:text-indigo-300"
                >
                  Use Campus Preset (11.55640, 104.92820)
                </button>
              </div>
            </div>

            <!-- Allowed Radius -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                  Allowed Radius
                </label>
                <span class="text-xs font-bold text-indigo-400">
                  {{ sessionForm.radius_meters }} meters
                </span>
              </div>
              <input
                v-model.number="sessionForm.radius_meters"
                type="range"
                min="20"
                max="500"
                step="10"
                class="w-full accent-indigo-500 cursor-pointer"
              />
              <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                <span>20m (Tight)</span>
                <span>100m (Standard Classroom)</span>
                <span>500m (Lecture Hall/Campus)</span>
              </div>
            </div>
          </div>

          <!-- OPTION 2 FIELDS: School WiFi Subnet & Info -->
          <div v-if="sessionForm.verification_mode === 'wifi'" class="p-4 rounded-xl bg-teal-950/20 border border-teal-500/30 space-y-3 animate-in fade-in duration-200">
            <div class="flex items-center space-x-2 text-teal-300 font-semibold text-xs">
              <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>School WiFi Subnet Verification Active</span>
            </div>
            <p class="text-[11px] text-slate-300 leading-relaxed">
              Only scans originating from your school's local WiFi network will be accepted. Remote students on mobile data (4G/5G) or home internet are automatically rejected.
            </p>
            <div>
              <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">
                School Subnet CIDR (Optional — defaults to school LAN)
              </label>
              <input
                v-model="sessionForm.wifi_subnet"
                type="text"
                placeholder="e.g. 192.168.1.0/24 or 10.0.0.0/16"
                class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:ring-1 focus:ring-teal-500"
              />
              <span class="text-[10px] text-slate-500 mt-1 block">
                Leave blank to use system default school network subnets.
              </span>
            </div>
          </div>

          <div v-if="startSessionError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
            {{ startSessionError }}
          </div>

          <div class="pt-4 flex items-center justify-end space-x-3">
            <button
              type="button"
              @click="showStartSessionModal = false"
              class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="startingSession"
              class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 disabled:opacity-50"
            >
              {{ startingSession ? 'Starting Session...' : 'Launch Projector QR' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Create Course -->
    <div v-if="showCreateCourseModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-md p-5 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button @click="showCreateCourseModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h3 class="text-xl font-bold text-white font-['Outfit'] mb-1">
          Create New Course
        </h3>
        <p class="text-xs text-slate-400 mb-5">
          Add a course section to start enrolling students and tracking attendance
        </p>

        <form @submit.prevent="handleCreateCourse" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
              Course Name & Code
            </label>
            <input
              v-model="courseForm.name"
              type="text"
              required
              placeholder="e.g. CS301 - Web & Mobile Cloud Applications"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
              Schedule / Room Info
            </label>
            <input
              v-model="courseForm.schedule_info"
              type="text"
              placeholder="e.g. Mon / Wed 08:30 AM (Room 402)"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm"
            />
          </div>

          <div class="pt-4 flex items-center justify-end space-x-3">
            <button
              type="button"
              @click="showCreateCourseModal = false"
              class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30"
            >
              Create Course
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Manage Students & Enrollment -->
    <div v-if="showEnrollModal && selectedCourse" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-xl p-5 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative max-h-[90vh] flex flex-col">
        <button @click="showEnrollModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div>
          <h3 class="text-xl font-bold text-white font-['Outfit']">
            Enrolled Students
          </h3>
          <p class="text-xs text-slate-400 mt-1">
            {{ selectedCourse.name }}
          </p>
        </div>

        <!-- Enroll New Student Form -->
        <div class="my-4 sm:my-5 p-3.5 sm:p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
          <div class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
            Enroll New Student
          </div>
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <select
              v-model="newStudentId"
              class="flex-1 px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none"
            >
              <option value="">-- Select Student --</option>
              <option
                v-for="s in availableStudents"
                :key="s.id"
                :value="s.id"
                :disabled="isStudentAlreadyEnrolled(s.id)"
              >
                {{ s.name }} ({{ s.email }}) {{ isStudentAlreadyEnrolled(s.id) ? '— [Enrolled]' : '' }}
              </option>
            </select>
            <button
              @click="handleEnrollStudent"
              :disabled="!newStudentId"
              class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-semibold whitespace-nowrap active:scale-95 transition-all text-center"
            >
              Enroll
            </button>
          </div>
        </div>

        <!-- Enrolled List -->
        <div class="flex-1 overflow-y-auto pr-1 space-y-2">
          <div
            v-for="s in enrolledRoster"
            :key="s.id"
            class="p-3 rounded-xl bg-slate-900/50 border border-slate-800/80 flex items-center justify-between"
          >
            <div>
              <div class="text-sm font-semibold text-white">{{ s.name }}</div>
              <div class="text-xs text-slate-400">{{ s.email }}</div>
            </div>
            <button
              @click="handleUnenrollStudent(s.id)"
              class="text-xs text-rose-400 hover:text-rose-300 px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20"
            >
              Remove
            </button>
          </div>

          <div v-if="enrolledRoster.length === 0" class="text-center py-6 text-xs text-slate-400">
            No students currently enrolled in this course.
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Telegram Bot Diagnostics & Test -->
    <div v-if="showTelegramModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-lg p-5 sm:p-7 rounded-3xl border border-slate-800 shadow-2xl relative max-h-[90vh] flex flex-col overflow-y-auto">
        <button @click="showTelegramModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 rounded-2xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center text-sky-400">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-2.02 1.28-5.7 3.77-.54.37-1.03.55-1.47.54-.48-.01-1.41-.27-2.1-.5-.85-.28-1.52-.43-1.46-.91.03-.25.38-.51 1.05-.78 4.12-1.79 6.87-2.98 8.25-3.56 3.93-1.64 4.74-1.92 5.27-1.93.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.18-.03.26z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white font-['Outfit']">
              Telegram Bot Integration
            </h3>
            <p class="text-xs text-slate-400">
              Live notifications for session QR codes and student check-ins
            </p>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="telegramLoading" class="py-10 text-center text-slate-400 text-xs">
          Checking Telegram bot connectivity...
        </div>

        <div v-else class="space-y-4">
          <!-- Status Banner -->
          <div
            class="p-4 rounded-2xl border flex items-start space-x-3"
            :class="telegramStatus?.configured && telegramStatus?.api_reachable ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-amber-500/10 border-amber-500/30'"
          >
            <div class="mt-0.5">
              <span v-if="telegramStatus?.configured && telegramStatus?.api_reachable" class="text-emerald-400 text-lg">✅</span>
              <span v-else class="text-amber-400 text-lg">⚠️</span>
            </div>
            <div class="flex-1 text-xs">
              <div class="font-bold text-white mb-0.5">
                {{ telegramStatus?.configured && telegramStatus?.api_reachable ? 'Telegram Bot Connected & Ready' : 'Configuration Attention Needed' }}
              </div>
              <div class="text-slate-300">
                <span v-if="telegramStatus?.configured && telegramStatus?.api_reachable">
                  Bot <strong>@{{ telegramStatus?.bot_info?.username }}</strong> is connected to chat ID <code>{{ telegramStatus?.chat_id }}</code>.
                </span>
                <span v-else-if="!telegramStatus?.bot_token_set">
                  Bot Token is missing in Environment Variables!
                </span>
                <span v-else-if="!telegramStatus?.api_reachable">
                  Cannot reach Telegram: {{ telegramStatus?.error }}
                </span>
                <span v-else>
                  Chat ID is missing in Environment Variables!
                </span>
              </div>
            </div>
          </div>

          <!-- Configuration Details -->
          <div class="bg-slate-900/70 p-3.5 rounded-2xl border border-slate-800 space-y-2 text-xs">
            <div class="flex justify-between py-1 border-b border-slate-800/60">
              <span class="text-slate-400">Bot Token</span>
              <span class="font-mono text-slate-200">{{ telegramStatus?.bot_token_preview || 'Not set' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-800/60">
              <span class="text-slate-400">Target Chat ID</span>
              <span class="font-mono text-slate-200">{{ telegramStatus?.chat_id || 'Not set' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-800/60">
              <span class="text-slate-400">Bot Username</span>
              <span class="font-semibold text-sky-400">{{ telegramStatus?.bot_info?.username ? '@' + telegramStatus?.bot_info?.username : 'Unknown' }}</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-slate-400">PHP GD Extension</span>
              <span :class="telegramStatus?.gd_installed ? 'text-emerald-400' : 'text-amber-400'">{{ telegramStatus?.gd_installed ? 'Loaded (Local GD)' : 'Using Fallback API' }}</span>
            </div>
          </div>

          <!-- Send Test Button -->
          <div class="pt-1">
            <button
              @click="handleSendTelegramTest"
              :disabled="testSending || !telegramStatus?.configured"
              class="w-full py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 disabled:opacity-50 text-white font-semibold text-xs shadow-lg shadow-sky-500/20 active:scale-98 transition-all flex items-center justify-center space-x-2"
            >
              <svg v-if="testSending" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
              <span>{{ testSending ? 'Sending Test Message...' : 'Send Test Notification to Telegram' }}</span>
            </button>
            <p v-if="testResult" class="mt-2 text-center text-xs" :class="testResult.success ? 'text-emerald-400' : 'text-rose-400'">
              {{ testResult.message }}
            </p>
          </div>

          <!-- Laravel Cloud Instructions -->
          <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-[11px] text-slate-400 space-y-1.5">
            <div class="font-semibold text-slate-300">⚙️ Laravel Cloud Setup Check:</div>
            <div>In your <strong>Laravel Cloud Dashboard → Environment → Variables</strong>, ensure these are configured:</div>
            <div class="font-mono bg-slate-900 p-2 rounded-lg text-[10px] text-slate-300 select-all overflow-x-auto">
              TELEGRAM_ENABLED=true<br/>
              TELEGRAM_BOT_TOKEN=8975196350:AAH-W910saKAb2LCYOu-ATQAShbJJxJ9qYQ<br/>
              TELEGRAM_CHAT_ID=5536919758
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const router = useRouter();

const courses = ref([]);
const activeSessions = ref([]);
const allStudents = ref([]);
const loading = ref(true);

const showCreateCourseModal = ref(false);
const showStartSessionModal = ref(false);
const showEnrollModal = ref(false);
const showTelegramModal = ref(false);

const telegramLoading = ref(false);
const telegramStatus = ref(null);
const testSending = ref(false);
const testResult = ref(null);

const fetchTelegramStatus = async () => {
  try {
    telegramLoading.value = true;
    const { data } = await api.get('/telegram/status');
    telegramStatus.value = data.data;
  } catch (err) {
    console.error('Failed to fetch Telegram status:', err);
  } finally {
    telegramLoading.value = false;
  }
};

const openTelegramModal = () => {
  testResult.value = null;
  showTelegramModal.value = true;
  fetchTelegramStatus();
};

const handleSendTelegramTest = async () => {
  testSending.value = true;
  testResult.value = null;
  try {
    const { data } = await api.post('/telegram/test');
    testResult.value = {
      success: true,
      message: data.message || 'Test message sent successfully to Telegram!',
    };
  } catch (err) {
    testResult.value = {
      success: false,
      message: err.response?.data?.message || err.message || 'Failed to send test message.',
    };
  } finally {
    testSending.value = false;
  }
};

const startingSession = ref(false);
const startSessionError = ref('');

const selectedCourse = ref(null);
const enrolledRoster = ref([]);
const newStudentId = ref('');

const courseForm = reactive({
  name: '',
  schedule_info: '',
});

const sessionForm = reactive({
  course_id: '',
  verification_mode: 'location',
  latitude: 11.5564,
  longitude: 104.9282,
  radius_meters: 100,
  require_wifi: false,
  wifi_subnet: '192.168.1.0/24',
});

const totalEnrolledStudents = computed(() => {
  return courses.value.reduce((acc, c) => acc + (c.students_count || 0), 0);
});

const totalSessions = computed(() => {
  return courses.value.reduce((acc, c) => acc + (c.sessions_count || 0), 0);
});

const availableStudents = computed(() => {
  return allStudents.value;
});

const isStudentAlreadyEnrolled = (studentId) => {
  return enrolledRoster.value.some((s) => s.id === studentId);
};

const fetchData = async () => {
  loading.value = true;
  try {
    const [coursesRes, studentsRes] = await Promise.all([
      api.get('/courses'),
      api.get('/students'),
    ]);
    courses.value = coursesRes.data.courses;
    allStudents.value = studentsRes.data.students;

    // Check for open sessions in courses
    for (const c of courses.value) {
      const courseDetails = await api.get(`/courses/${c.id}`);
      const openSession = courseDetails.data.course.sessions?.find((s) => s.status === 'open');
      if (openSession) {
        openSession.course = c;
        activeSessions.value = [openSession];
        break;
      }
    }
  } catch (err) {
    console.error('Failed to load dashboard data:', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
  fetchTelegramStatus();
});

const setCampusPreset = () => {
  sessionForm.latitude = 11.5564;
  sessionForm.longitude = 104.9282;
};

const detectLocation = () => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        sessionForm.latitude = parseFloat(pos.coords.latitude.toFixed(7));
        sessionForm.longitude = parseFloat(pos.coords.longitude.toFixed(7));
      },
      (err) => {
        alert('Could not acquire current location: ' + err.message + '. Used campus preset instead.');
        setCampusPreset();
      }
    );
  }
};

const openStartSessionModal = (courseId = null) => {
  if (courseId) {
    sessionForm.course_id = courseId;
  } else if (courses.value.length > 0 && !sessionForm.course_id) {
    sessionForm.course_id = courses.value[0].id;
  }
  showStartSessionModal.value = true;
};

const handleStartSession = async () => {
  startingSession.value = true;
  startSessionError.value = '';
  try {
    const payload = {
      course_id: sessionForm.course_id,
      verification_mode: sessionForm.verification_mode,
      radius_meters: sessionForm.radius_meters,
    };
    if (sessionForm.verification_mode === 'location') {
      payload.latitude = sessionForm.latitude;
      payload.longitude = sessionForm.longitude;
      payload.require_wifi = false;
      payload.wifi_subnet = null;
    } else {
      payload.require_wifi = true;
      payload.wifi_subnet = sessionForm.wifi_subnet || null;
      payload.latitude = sessionForm.latitude || null;
      payload.longitude = sessionForm.longitude || null;
    }
    const { data } = await api.post('/attendance/sessions', payload);
    showStartSessionModal.value = false;
    router.push(`/teacher/session/${data.session.id}`);
  } catch (err) {
    startSessionError.value = err.response?.data?.message || 'Failed to start session.';
  } finally {
    startingSession.value = false;
  }
};

const closeSession = async (sessionId) => {
  try {
    await api.post(`/attendance/sessions/${sessionId}/close`);
    activeSessions.value = [];
    fetchData();
  } catch (err) {
    alert('Failed to close session: ' + (err.response?.data?.message || err.message));
  }
};

const handleCreateCourse = async () => {
  try {
    await api.post('/courses', courseForm);
    showCreateCourseModal.value = false;
    courseForm.name = '';
    courseForm.schedule_info = '';
    await fetchData();
  } catch (err) {
    alert('Failed to create course: ' + (err.response?.data?.message || err.message));
  }
};

const openEnrollModal = async (course) => {
  selectedCourse.value = course;
  try {
    const { data } = await api.get(`/courses/${course.id}`);
    enrolledRoster.value = data.course.students || [];
    showEnrollModal.value = true;
  } catch (err) {
    alert('Failed to load roster: ' + err.message);
  }
};

const handleEnrollStudent = async () => {
  if (!newStudentId.value || !selectedCourse.value) return;
  try {
    await api.post('/enrollments', {
      course_id: selectedCourse.value.id,
      student_id: newStudentId.value,
    });
    newStudentId.value = '';
    // Refresh roster
    const { data } = await api.get(`/courses/${selectedCourse.value.id}`);
    enrolledRoster.value = data.course.students || [];
    fetchData();
  } catch (err) {
    alert('Failed to enroll student: ' + (err.response?.data?.message || err.message));
  }
};

const handleUnenrollStudent = async (studentId) => {
  if (!confirm('Remove student from course?')) return;
  try {
    await api.delete(`/enrollments/${selectedCourse.value.id}/${studentId}`);
    enrolledRoster.value = enrolledRoster.value.filter((s) => s.id !== studentId);
    fetchData();
  } catch (err) {
    alert('Failed to remove student: ' + (err.response?.data?.message || err.message));
  }
};
</script>
