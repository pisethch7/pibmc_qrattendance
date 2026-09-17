<template>
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">

    <!-- ── Confirm Dialog Overlay ──────────────────────────── -->
    <div v-if="confirm.show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-sm p-6 rounded-3xl border border-slate-800 shadow-2xl text-center space-y-4 fade-up">
        <div class="w-12 h-12 rounded-full bg-rose-500/15 border border-rose-500/30 flex items-center justify-center mx-auto text-rose-400">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
        <div>
          <h4 class="text-base font-bold text-white font-['Outfit']">{{ confirm.title }}</h4>
          <p class="text-sm text-slate-400 mt-1">{{ confirm.message }}</p>
        </div>
        <div class="flex items-center justify-center gap-3 pt-1">
          <button @click="confirm.show = false" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition-colors">
            Cancel
          </button>
          <button @click="confirm.onConfirm(); confirm.show = false" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold shadow-lg shadow-rose-600/25 transition-colors">
            {{ confirm.action || 'Confirm' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Top Header ──────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2.5">
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gradient-primary font-['Outfit']">
            Teacher Dashboard
          </h1>
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-gradient-to-r from-indigo-500/20 to-purple-500/20 text-indigo-300 border border-indigo-500/30 shadow-sm">
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
          class="px-3.5 sm:px-4 py-2.5 rounded-xl glass-card text-slate-200 text-xs sm:text-sm font-semibold active:scale-95 transition-all flex items-center justify-center space-x-2"
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
          class="px-3.5 sm:px-4 py-2.5 rounded-xl glass-card text-slate-200 text-xs sm:text-sm font-semibold active:scale-95 transition-all flex items-center justify-center space-x-1.5 sm:space-x-2"
        >
          <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>New Course</span>
        </button>

        <button
          @click="openStartSessionModal()"
          class="col-span-2 sm:col-span-1 px-4 sm:px-5 py-2.5 rounded-xl btn-primary-gradient text-white text-xs sm:text-sm font-bold active:scale-95 transition-all flex items-center justify-center space-x-1.5 sm:space-x-2"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Start Session</span>
        </button>
      </div>
    </div>

    <!-- ── Active Session Banner ───────────────────────────── -->
    <div v-if="activeSessions.length > 0" class="p-5 rounded-3xl bg-gradient-to-r from-indigo-950/90 via-purple-950/70 to-slate-900/90 border border-indigo-500/50 glow-indigo relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/15 rounded-full blur-2xl pointer-events-none"></div>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
        <div class="flex items-center space-x-3.5">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
          </span>
          <div>
            <div class="text-[11px] font-bold text-indigo-300 uppercase tracking-wider">Live Attendance Session Active</div>
            <div class="text-sm sm:text-base font-bold text-white">{{ activeSessions[0].course?.name }}</div>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:space-x-3">
          <router-link
            :to="`/teacher/session/${activeSessions[0].id}`"
            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition-all flex items-center justify-center space-x-1.5 shadow-lg shadow-indigo-600/30 active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span>Open Screen</span>
          </router-link>

          <button
            @click="askCloseSession(activeSessions[0].id)"
            class="px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-semibold active:scale-95 transition-all text-center"
          >
            End Session
          </button>
        </div>
      </div>
    </div>

    <!-- ── Metric Cards ────────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-5">
      <div class="glass-panel p-6 rounded-3xl border border-indigo-500/20 glow-indigo relative overflow-hidden group hover:border-indigo-500/40 transition-all">
        <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-indigo-500 to-transparent"></div>
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Courses Taught</span>
          <div class="p-2.5 rounded-xl bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
        <div class="mt-4 text-3xl font-extrabold text-white font-['Outfit']">{{ courses.length }}</div>
        <div class="mt-1 text-xs text-indigo-300/70">Active class sections</div>
      </div>

      <div class="glass-panel p-6 rounded-3xl border border-purple-500/20 glow-purple relative overflow-hidden group hover:border-purple-500/40 transition-all">
        <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-purple-500 to-transparent"></div>
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Enrolled</span>
          <div class="p-2.5 rounded-xl bg-purple-500/15 text-purple-300 border border-purple-500/30 shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 text-3xl font-extrabold text-white font-['Outfit']">{{ totalEnrolledStudents }}</div>
        <div class="mt-1 text-xs text-purple-300/70">Total student enrollments</div>
      </div>

      <div class="glass-panel p-6 rounded-3xl border border-emerald-500/20 glow-emerald relative overflow-hidden group hover:border-emerald-500/40 transition-all">
        <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-emerald-500 to-transparent"></div>
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sessions Held</span>
          <div class="p-2.5 rounded-xl bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 text-3xl font-extrabold text-white font-['Outfit']">{{ totalSessions }}</div>
        <div class="mt-1 text-xs text-emerald-300/70">All-time attendance checks</div>
      </div>
    </div>

    <!-- ── Tab Navigation ────────────────────────────────────── -->
    <div class="flex items-center space-x-1 p-1 bg-slate-900/60 border border-slate-800 rounded-2xl w-fit">
      <button
        @click="activeTab = 'courses'"
        class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
        :class="activeTab === 'courses' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'"
      >📚 Courses</button>
      <button
        @click="activeTab = 'structure'"
        class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
        :class="activeTab === 'structure' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-400 hover:text-white'"
      >🏛 Departments & Majors</button>
    </div>

    <!-- ── TAB: Courses ──────────────────────────────────────── -->
    <div v-if="activeTab === 'courses'" class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-white font-['Outfit']">Your Courses</h2>
        <span class="text-xs text-slate-500">{{ courses.length }} course{{ courses.length !== 1 ? 's' : '' }}</span>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div v-for="n in 4" :key="n" class="glass-panel p-6 rounded-2xl border border-slate-800 space-y-3 shimmer">
          <div class="h-5 bg-slate-800 rounded-lg w-3/4"></div>
          <div class="h-3 bg-slate-800/70 rounded-lg w-1/2"></div>
          <div class="h-3 bg-slate-800/60 rounded-lg w-2/3 mt-4"></div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="courses.length === 0" class="glass-panel p-14 rounded-2xl text-center border border-slate-800 border-dashed">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13"/>
          </svg>
        </div>
        <h3 class="text-base font-bold text-white">No courses yet</h3>
        <p class="text-sm text-slate-400 mt-1">Create your first course to start taking attendance</p>
        <button @click="showCreateCourseModal = true" class="mt-5 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition-colors">
          Create First Course
        </button>
      </div>

      <!-- Course cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div v-for="c in courses" :key="c.id"
          class="glass-panel p-6 rounded-2xl border border-slate-800 hover:border-slate-700 transition-all flex flex-col justify-between group">
          <div>
            <!-- Major / Dept breadcrumb -->
            <div v-if="c.major" class="flex items-center space-x-1 text-[10px] text-slate-500 mb-2">
              <span>{{ c.major?.department?.name }}</span>
              <span>›</span>
              <span class="text-purple-400 font-semibold">{{ c.major?.name }}</span>
            </div>
            <div class="flex items-start justify-between gap-2">
              <h3 class="text-lg font-bold text-white font-['Outfit'] leading-tight">{{ c.name }}</h3>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 whitespace-nowrap flex-shrink-0">{{ c.students_count || 0 }} Students</span>
            </div>
            <p class="mt-2 text-xs text-slate-400 flex items-center space-x-1.5">
              <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <span>{{ c.schedule_info || 'Schedule unassigned' }}</span>
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2">
              <button @click="openEnrollModal(c)" class="text-xs font-medium text-slate-300 hover:text-white px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Students</span>
              </button>
              <button @click="openEditCourseModal(c)" class="text-xs font-medium text-slate-300 hover:text-white px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit</span>
              </button>
              <button @click="askDeleteCourse(c)" class="text-xs font-medium text-rose-400 hover:text-rose-300 px-3 py-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
            <button @click="openStartSessionModal(c.id)" class="text-xs font-semibold px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white shadow-md shadow-indigo-600/20 transition-all flex items-center space-x-1.5 active:scale-95">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
              <span>Start QR Session</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TAB: Departments & Majors ─────────────────────────── -->
    <div v-if="activeTab === 'structure'" class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-white font-['Outfit']">Academic Structure</h2>
        <button @click="showAddDeptForm = !showAddDeptForm" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold flex items-center space-x-1.5 transition-all active:scale-95">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Add Department</span>
        </button>
      </div>

      <!-- Add Department inline form -->
      <div v-if="showAddDeptForm" class="glass-panel p-5 rounded-2xl border border-purple-500/30 space-y-3">
        <h3 class="text-sm font-bold text-white">New Department</h3>
        <input v-model="deptForm.name" type="text" placeholder="Department name (e.g. Faculty of Engineering)" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/50"/>
        <input v-model="deptForm.description" type="text" placeholder="Description (optional)" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/50"/>
        <div class="flex gap-2">
          <button @click="handleCreateDept" :disabled="!deptForm.name || savingDept" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white text-xs font-semibold">{{ savingDept ? 'Saving...' : 'Create Department' }}</button>
          <button @click="showAddDeptForm = false; deptForm.name = ''; deptForm.description = ''" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Cancel</button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="deptLoading" class="space-y-3">
        <div v-for="n in 3" :key="n" class="glass-panel p-5 rounded-2xl border border-slate-800 animate-pulse h-16"></div>
      </div>

      <!-- Empty -->
      <div v-else-if="departments.length === 0" class="glass-panel p-12 rounded-2xl text-center border border-slate-800 border-dashed">
        <div class="text-4xl mb-3">🏛</div>
        <h3 class="text-sm font-bold text-white">No departments yet</h3>
        <p class="text-xs text-slate-400 mt-1">Add a department to start organizing your academic structure</p>
      </div>

      <!-- Department accordion list -->
      <div v-else class="space-y-4">
        <div v-for="dept in departments" :key="dept.id" class="glass-panel rounded-2xl border border-slate-800 overflow-hidden">
          <!-- Dept header -->
          <div class="p-4 flex items-center justify-between gap-3">
            <div class="flex items-center space-x-3 flex-1 min-w-0 cursor-pointer" @click="toggleDept(dept.id)">
              <div class="w-8 h-8 rounded-xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-purple-400 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              <div class="min-w-0">
                <div v-if="editingDeptId !== dept.id">
                  <div class="text-sm font-bold text-white">{{ dept.name }}</div>
                  <div class="text-[11px] text-slate-400">{{ dept.majors_count || 0 }} major{{ dept.majors_count !== 1 ? 's' : '' }}</div>
                </div>
                <div v-else class="flex items-center gap-2" @click.stop>
                  <input v-model="editDeptForm.name" class="px-3 py-1.5 rounded-lg bg-slate-950 border border-purple-500/50 text-white text-sm focus:outline-none flex-1 min-w-0"/>
                  <button @click="handleUpdateDept(dept)" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold">Save</button>
                  <button @click="editingDeptId = null" class="px-2 py-1.5 rounded-lg bg-slate-800 text-slate-300 text-xs">✕</button>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <button @click.stop="startEditDept(dept)" class="p-1.5 rounded-lg hover:bg-slate-800 text-amber-400 transition-colors"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
              <button @click.stop="askDeleteDept(dept)" class="p-1.5 rounded-lg hover:bg-slate-800 text-rose-400 transition-colors"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
              <button @click.stop="toggleDept(dept.id)" class="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 transition-colors">
                <svg class="w-3.5 h-3.5 transition-transform" :class="expandedDepts.includes(dept.id) ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>
            </div>
          </div>

          <!-- Majors list (collapsible) -->
          <div v-if="expandedDepts.includes(dept.id)" class="border-t border-slate-800/60 bg-slate-950/30 p-4 space-y-3">
            <div v-for="major in dept.majors" :key="major.id" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800">
              <div class="flex items-center space-x-2.5 flex-1 min-w-0">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/15 flex items-center justify-center text-indigo-400 flex-shrink-0">
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg>
                </div>
                <div v-if="editingMajorId !== major.id" class="min-w-0">
                  <div class="text-xs font-semibold text-white">{{ major.name }}</div>
                  <div class="text-[10px] text-slate-500">{{ major.courses_count || 0 }} course{{ major.courses_count !== 1 ? 's' : '' }}</div>
                </div>
                <div v-else class="flex items-center gap-2 flex-1">
                  <input v-model="editMajorForm.name" class="px-3 py-1 rounded-lg bg-slate-950 border border-indigo-500/50 text-white text-xs focus:outline-none flex-1 min-w-0"/>
                  <button @click="handleUpdateMajor(major)" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold">Save</button>
                  <button @click="editingMajorId = null" class="px-2 py-1 rounded-lg bg-slate-800 text-slate-400 text-xs">✕</button>
                </div>
              </div>
              <div class="flex items-center gap-1 flex-shrink-0">
                <button @click="startEditMajor(major)" class="p-1 rounded-lg hover:bg-slate-800 text-amber-400 transition-colors"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                <button @click="askDeleteMajor(major)" class="p-1 rounded-lg hover:bg-slate-800 text-rose-400 transition-colors"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
              </div>
            </div>

            <!-- Add Major inline -->
            <div v-if="addMajorDeptId === dept.id" class="flex items-center gap-2 mt-2">
              <input v-model="newMajorName" type="text" placeholder="Major name (e.g. Computer Science)" class="flex-1 px-3 py-2 rounded-xl bg-slate-900 border border-indigo-500/40 text-white text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500"/>
              <button @click="handleCreateMajor(dept.id)" :disabled="!newMajorName || savingMajor" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold">{{ savingMajor ? '...' : 'Add' }}</button>
              <button @click="addMajorDeptId = null; newMajorName = ''" class="px-2 py-2 rounded-xl bg-slate-800 text-slate-400 text-xs">✕</button>
            </div>
            <button v-else @click="addMajorDeptId = dept.id; newMajorName = ''" class="w-full py-2 rounded-xl border border-dashed border-slate-700 text-slate-400 hover:text-white hover:border-slate-600 text-xs font-medium transition-colors flex items-center justify-center space-x-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              <span>Add Major</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Modal: Start Session ────────────────────────────── -->
    <div v-if="showStartSessionModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-lg p-5 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button @click="showStartSessionModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform p-1">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h3 class="text-xl font-bold text-white font-['Outfit'] mb-1">Start Attendance Session</h3>
        <p class="text-xs text-slate-400 mb-5">Set up verification and launch rotating QR projector screen</p>

        <form @submit.prevent="handleStartSession" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Select Course</label>
            <select
              v-model="sessionForm.course_id"
              required
              class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
            >
              <option disabled value="">-- Choose a course --</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Verification method -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Presence Verification Method</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              <button type="button" @click="sessionForm.verification_mode = 'location'"
                class="p-3.5 rounded-xl border text-left transition-all"
                :class="sessionForm.verification_mode === 'location' ? 'bg-indigo-600/20 border-indigo-500/60 ring-1 ring-indigo-500/40' : 'bg-slate-900 border-slate-800 hover:border-slate-700 opacity-70 hover:opacity-100'">
                <div class="flex items-center space-x-2.5 mb-1.5">
                  <div class="p-1.5 rounded-lg" :class="sessionForm.verification_mode === 'location' ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-400'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-white block">GPS Location</span>
                    <span class="text-[10px] text-indigo-300 font-medium">Classroom Geofence</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-400 leading-tight">Requires students to be within radius of classroom.</p>
              </button>

              <button type="button" @click="sessionForm.verification_mode = 'wifi'"
                class="p-3.5 rounded-xl border text-left transition-all"
                :class="sessionForm.verification_mode === 'wifi' ? 'bg-teal-600/20 border-teal-500/60 ring-1 ring-teal-500/40' : 'bg-slate-900 border-slate-800 hover:border-slate-700 opacity-70 hover:opacity-100'">
                <div class="flex items-center space-x-2.5 mb-1.5">
                  <div class="p-1.5 rounded-lg" :class="sessionForm.verification_mode === 'wifi' ? 'bg-teal-500 text-white' : 'bg-slate-800 text-slate-400'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-white block">School WiFi</span>
                    <span class="text-[10px] text-teal-300 font-medium">Campus Network</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-400 leading-tight">Requires students to be on school WiFi network.</p>
              </button>
            </div>
          </div>

          <!-- GPS fields -->
          <div v-if="sessionForm.verification_mode === 'location'" class="space-y-4 p-4 rounded-xl bg-slate-900/60 border border-slate-800">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Classroom Coordinates</label>
                <button type="button" @click="detectLocation" class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 flex items-center space-x-1">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <span>Detect My Location</span>
                </button>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <input v-model.number="sessionForm.latitude" type="number" step="0.0000001" :required="sessionForm.verification_mode === 'location'" placeholder="Latitude (13.5875483)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500/50"/>
                <input v-model.number="sessionForm.longitude" type="number" step="0.0000001" :required="sessionForm.verification_mode === 'location'" placeholder="Longitude (102.942477)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500/50"/>
              </div>
              <button type="button" @click="setCampusPreset" class="mt-2 text-[11px] text-indigo-400 hover:text-indigo-300 underline">
                Use Campus Preset (13.5875483, 102.942477)
              </button>
            </div>

            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Allowed Radius</label>
                <span class="text-xs font-bold text-indigo-400">{{ sessionForm.radius_meters }} meters</span>
              </div>
              <input v-model.number="sessionForm.radius_meters" type="range" min="20" max="500" step="10" class="w-full accent-indigo-500 cursor-pointer"/>
              <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                <span>20m (Tight)</span><span>100m (Standard)</span><span>500m (Campus)</span>
              </div>
            </div>
          </div>

          <!-- WiFi fields -->
          <div v-if="sessionForm.verification_mode === 'wifi'" class="p-4 rounded-xl bg-teal-950/20 border border-teal-500/30 space-y-3">
            <div class="flex items-center space-x-2 text-teal-300 font-semibold text-xs">
              <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>School WiFi Subnet Verification</span>
            </div>
            <p class="text-[11px] text-slate-300 leading-relaxed">Only scans from your school's local WiFi will be accepted. Remote students on mobile data are automatically rejected.</p>
            <div>
              <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">School Subnet CIDR (Optional)</label>
              <input v-model="sessionForm.wifi_subnet" type="text" placeholder="e.g. 192.168.1.0/24" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:ring-1 focus:ring-teal-500"/>
            </div>
          </div>

          <div v-if="startSessionError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs">
            {{ startSessionError }}
          </div>

          <div class="pt-4 flex items-center justify-end space-x-3">
            <button type="button" @click="showStartSessionModal = false" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Cancel</button>
            <button type="submit" :disabled="startingSession" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 disabled:opacity-50 flex items-center space-x-2">
              <svg v-if="startingSession" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
              <span>{{ startingSession ? 'Launching...' : 'Launch Projector QR' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Modal: Create/Edit Course ───────────────────────── -->
    <div v-if="showCreateCourseModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-md p-5 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative">
        <button @click="closeCourseModal" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform p-1">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h3 class="text-xl font-bold text-white font-['Outfit'] mb-1">{{ editingCourse ? 'Edit Course' : 'Create New Course' }}</h3>
        <p class="text-xs text-slate-400 mb-5">{{ editingCourse ? 'Update course details' : 'Add a course section to start enrolling students and tracking attendance' }}</p>

        <form @submit.prevent="editingCourse ? handleUpdateCourse() : handleCreateCourse()" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Course Name & Code</label>
            <input v-model="courseForm.name" type="text" required placeholder="e.g. CS301 - Web & Mobile Cloud Applications" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50"/>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Schedule / Room Info</label>
            <input v-model="courseForm.schedule_info" type="text" placeholder="e.g. Mon / Wed 08:30 AM (Room 402)" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50"/>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Major <span class="text-slate-500 normal-case">(optional)</span></label>
            <select v-model="courseForm.major_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
              <option value="">-- No Major --</option>
              <optgroup v-for="dept in departments" :key="dept.id" :label="dept.name">
                <option v-for="m in dept.majors" :key="m.id" :value="m.id">{{ m.name }}</option>
              </optgroup>
            </select>
          </div>
          <div class="pt-4 flex items-center justify-end space-x-3">
            <button type="button" @click="closeCourseModal" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Cancel</button>
            <button type="submit" :disabled="creatingCourse" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 disabled:opacity-50 flex items-center space-x-2">
              <svg v-if="creatingCourse" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
              <span>{{ creatingCourse ? 'Saving...' : (editingCourse ? 'Save Changes' : 'Create Course') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Modal: Manage Students ──────────────────────────── -->
    <div v-if="showEnrollModal && selectedCourse" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-xl p-5 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative max-h-[90vh] flex flex-col">
        <button @click="showEnrollModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform p-1">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="mb-4">
          <h3 class="text-xl font-bold text-white font-['Outfit']">Enrolled Students</h3>
          <p class="text-xs text-slate-400 mt-1">{{ selectedCourse.name }}</p>
        </div>

        <!-- Enroll form -->
        <div class="mb-4 p-3.5 sm:p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
          <div class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Enroll New Student</div>
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <select v-model="newStudentId" class="flex-1 px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none">
              <option value="">-- Select Student --</option>
              <option v-for="s in availableStudents" :key="s.id" :value="s.id" :disabled="isStudentAlreadyEnrolled(s.id)">
                {{ s.name }} (@{{ s.username }}) {{ isStudentAlreadyEnrolled(s.id) ? '— [Enrolled]' : '' }}
              </option>
            </select>
            <button @click="handleEnrollStudent" :disabled="!newStudentId || enrolling" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-semibold whitespace-nowrap active:scale-95 transition-all flex items-center justify-center gap-1.5">
              <svg v-if="enrolling" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
              <span>{{ enrolling ? 'Enrolling...' : 'Enroll' }}</span>
            </button>
          </div>
        </div>

        <!-- Enrolled list -->
        <div class="flex-1 overflow-y-auto pr-1 space-y-2">
          <div v-for="s in enrolledRoster" :key="s.id"
            class="p-3 rounded-xl bg-slate-900/50 border border-slate-800/80 flex items-start justify-between gap-3 hover:border-slate-700/80 transition-colors">
            <div class="min-w-0 flex-1">
              <div class="text-sm font-semibold text-white">{{ s.name }}</div>
              <div class="text-xs text-slate-400">@{{ s.username }}</div>
              <!-- Sex + Device Badges row -->
              <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                <!-- Sex badge -->
                <span v-if="s.sex === 'male'"
                  class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/15 text-sky-300 border border-sky-500/30">
                  <span>♂</span><span>Male</span>
                </span>
                <span v-else-if="s.sex === 'female'"
                  class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-300 border border-rose-500/30">
                  <span>♀</span><span>Female</span>
                </span>
                <!-- Device badge -->
                <span v-if="s.device_id"
                  class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                  <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                  <span>{{ s.device_name || 'Locked device' }}</span>
                </span>
                <span v-else
                  class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-700/50 text-slate-400 border border-slate-700/50">
                  <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                  <span>No device yet</span>
                </span>
              </div>
            </div>
            <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
              <button v-if="s.device_id" @click="askResetDevice(s)"
                class="text-[10px] text-amber-400 hover:text-amber-300 px-2 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 transition-colors whitespace-nowrap flex items-center space-x-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reset Device</span>
              </button>
              <button @click="askUnenroll(s)" class="text-[10px] text-rose-400 hover:text-rose-300 px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 transition-colors whitespace-nowrap">
                Remove
              </button>
            </div>
          </div>

          <div v-if="enrolledRoster.length === 0" class="text-center py-8 space-y-2">
            <div class="w-10 h-10 mx-auto rounded-xl bg-slate-800 flex items-center justify-center text-slate-500">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-xs text-slate-400">No students enrolled yet.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Modal: Telegram Diagnostics ────────────────────── -->
    <div v-if="showTelegramModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-panel w-full max-w-lg p-5 sm:p-7 rounded-3xl border border-slate-800 shadow-2xl relative max-h-[90vh] flex flex-col overflow-y-auto">
        <button @click="showTelegramModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-white active:scale-90 transition-transform p-1">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 rounded-2xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center text-sky-400">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-2.02 1.28-5.7 3.77-.54.37-1.03.55-1.47.54-.48-.01-1.41-.27-2.1-.5-.85-.28-1.52-.43-1.46-.91.03-.25.38-.51 1.05-.78 4.12-1.79 6.87-2.98 8.25-3.56 3.93-1.64 4.74-1.92 5.27-1.93.12 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.18-.03.26z"/></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white font-['Outfit']">Telegram Bot Integration</h3>
            <p class="text-xs text-slate-400">Live notifications for session QR codes and student check-ins</p>
          </div>
        </div>

        <div v-if="telegramLoading" class="py-10 text-center text-slate-400 text-xs">Checking Telegram bot connectivity...</div>

        <div v-else class="space-y-4">
          <div class="p-4 rounded-2xl border flex items-start space-x-3"
            :class="telegramStatus?.configured && telegramStatus?.api_reachable ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-amber-500/10 border-amber-500/30'">
            <div class="mt-0.5">
              <span v-if="telegramStatus?.configured && telegramStatus?.api_reachable" class="text-emerald-400 text-lg">✅</span>
              <span v-else class="text-amber-400 text-lg">⚠️</span>
            </div>
            <div class="flex-1 text-xs">
              <div class="font-bold text-white mb-0.5">{{ telegramStatus?.configured && telegramStatus?.api_reachable ? 'Telegram Bot Connected & Ready' : 'Configuration Attention Needed' }}</div>
              <div class="text-slate-300">
                <span v-if="telegramStatus?.configured && telegramStatus?.api_reachable">Bot <strong>@{{ telegramStatus?.bot_info?.username }}</strong> is connected to chat ID <code>{{ telegramStatus?.chat_id }}</code>.</span>
                <span v-else-if="!telegramStatus?.bot_token_set">Bot Token is missing in Environment Variables!</span>
                <span v-else-if="!telegramStatus?.api_reachable">Cannot reach Telegram: {{ telegramStatus?.error }}</span>
                <span v-else>Chat ID is missing in Environment Variables!</span>
              </div>
            </div>
          </div>

          <div class="bg-slate-900/70 p-3.5 rounded-2xl border border-slate-800 space-y-2 text-xs">
            <div class="flex justify-between py-1 border-b border-slate-800/60"><span class="text-slate-400">Bot Token</span><span class="font-mono text-slate-200">{{ telegramStatus?.bot_token_preview || 'Not set' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-800/60"><span class="text-slate-400">Target Chat ID</span><span class="font-mono text-slate-200">{{ telegramStatus?.chat_id || 'Not set' }}</span></div>
            <div class="flex justify-between py-1 border-b border-slate-800/60"><span class="text-slate-400">Bot Username</span><span class="font-semibold text-sky-400">{{ telegramStatus?.bot_info?.username ? '@' + telegramStatus?.bot_info?.username : 'Unknown' }}</span></div>
            <div class="flex justify-between py-1"><span class="text-slate-400">PHP GD Extension</span><span :class="telegramStatus?.gd_installed ? 'text-emerald-400' : 'text-amber-400'">{{ telegramStatus?.gd_installed ? 'Loaded (Local GD)' : 'Using Fallback API' }}</span></div>
          </div>

          <button @click="handleSendTelegramTest" :disabled="testSending || !telegramStatus?.configured"
            class="w-full py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 disabled:opacity-50 text-white font-semibold text-xs shadow-lg shadow-sky-500/20 active:scale-98 transition-all flex items-center justify-center space-x-2">
            <svg v-if="testSending" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span>{{ testSending ? 'Sending Test Message...' : 'Send Test Notification to Telegram' }}</span>
          </button>
          <p v-if="testResult" class="text-center text-xs" :class="testResult.success ? 'text-emerald-400' : 'text-rose-400'">{{ testResult.message }}</p>

          <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-[11px] text-slate-400 space-y-1.5">
            <div class="font-semibold text-slate-300">⚙️ Laravel Cloud Setup:</div>
            <div>In <strong>Laravel Cloud Dashboard → Environment → Variables</strong>, ensure:</div>
            <div class="font-mono bg-slate-900 p-2 rounded-lg text-[10px] text-slate-300 select-all overflow-x-auto">
              TELEGRAM_ENABLED=true<br/>
              TELEGRAM_BOT_TOKEN=your_bot_token<br/>
              TELEGRAM_CHAT_ID=your_chat_id
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
import { useToast } from '../../composables/useToast';

const router = useRouter();
const { showToast } = useToast();

const courses = ref([]);
const activeSessions = ref([]);
const allStudents = ref([]);
const loading = ref(true);
const creatingCourse = ref(false);
const enrolling = ref(false);

const showCreateCourseModal = ref(false);
const showStartSessionModal = ref(false);
const showEnrollModal = ref(false);
const showTelegramModal = ref(false);

const telegramLoading = ref(false);
const telegramStatus = ref(null);
const testSending = ref(false);
const testResult = ref(null);

// ── Confirm dialog state ───────────────────────────────────────
const confirm = reactive({
  show: false,
  title: '',
  message: '',
  action: 'Confirm',
  onConfirm: () => {},
});

const showConfirm = (title, message, action, onConfirm) => {
  confirm.title = title;
  confirm.message = message;
  confirm.action = action;
  confirm.onConfirm = onConfirm;
  confirm.show = true;
};

// ── Telegram ──────────────────────────────────────────────────
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
    testResult.value = { success: true, message: data.message || 'Test message sent successfully!' };
    showToast('Test message sent to Telegram! ✅', 'success');
  } catch (err) {
    testResult.value = { success: false, message: err.response?.data?.message || 'Failed to send test message.' };
    showToast('Failed to send Telegram test message.', 'error');
  } finally {
    testSending.value = false;
  }
};

// ── Session ───────────────────────────────────────────────────
const startingSession = ref(false);
const startSessionError = ref('');
const selectedCourse = ref(null);
const enrolledRoster = ref([]);
const newStudentId = ref('');

const courseForm = reactive({ name: '', schedule_info: '', major_id: '' });
const editingCourse = ref(null);

// Departments & Majors state
const departments = ref([]);
const deptLoading = ref(false);
const activeTab = ref('courses');
const expandedDepts = ref([]);
const showAddDeptForm = ref(false);
const deptForm = reactive({ name: '', description: '' });
const savingDept = ref(false);
const editingDeptId = ref(null);
const editDeptForm = reactive({ name: '', description: '' });
const addMajorDeptId = ref(null);
const newMajorName = ref('');
const savingMajor = ref(false);
const editingMajorId = ref(null);
const editMajorForm = reactive({ name: '' });

const sessionForm = reactive({
  course_id: '',
  verification_mode: 'location',
  latitude: 13.5875483,
  longitude: 102.942477,
  radius_meters: 100,
  require_wifi: false,
  wifi_subnet: '192.168.1.0/24',
});

const totalEnrolledStudents = computed(() => courses.value.reduce((acc, c) => acc + (c.students_count || 0), 0));
const totalSessions = computed(() => courses.value.reduce((acc, c) => acc + (c.sessions_count || 0), 0));
const availableStudents = computed(() => allStudents.value);
const isStudentAlreadyEnrolled = (studentId) => enrolledRoster.value.some((s) => s.id === studentId);

// ── Departments CRUD ──────────────────────────────────────────
const fetchDepartments = async () => {
  deptLoading.value = true;
  try {
    const { data } = await api.get('/departments');
    departments.value = data.departments;
  } catch (err) {
    console.error('Failed to load departments:', err);
  } finally {
    deptLoading.value = false;
  }
};

const toggleDept = (id) => {
  const idx = expandedDepts.value.indexOf(id);
  if (idx === -1) expandedDepts.value.push(id);
  else expandedDepts.value.splice(idx, 1);
};

const handleCreateDept = async () => {
  if (!deptForm.name) return;
  savingDept.value = true;
  try {
    const { data } = await api.post('/departments', { name: deptForm.name, description: deptForm.description });
    departments.value.push({ ...data.department, majors: [] });
    deptForm.name = '';
    deptForm.description = '';
    showAddDeptForm.value = false;
    showToast('Department created!', 'success');
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to create department.', 'error');
  } finally {
    savingDept.value = false;
  }
};

const startEditDept = (dept) => {
  editingDeptId.value = dept.id;
  editDeptForm.name = dept.name;
  editDeptForm.description = dept.description || '';
};

const handleUpdateDept = async (dept) => {
  try {
    const { data } = await api.put(`/departments/${dept.id}`, editDeptForm);
    const idx = departments.value.findIndex((d) => d.id === dept.id);
    if (idx !== -1) departments.value[idx] = { ...departments.value[idx], ...data.department };
    editingDeptId.value = null;
    showToast('Department updated!', 'success');
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to update department.', 'error');
  }
};

const askDeleteDept = (dept) => {
  showConfirm('Delete Department?', `Delete "${dept.name}" and all its majors? Courses in this department will become unassigned.`, 'Delete', () => handleDeleteDept(dept.id));
};

const handleDeleteDept = async (id) => {
  try {
    await api.delete(`/departments/${id}`);
    departments.value = departments.value.filter((d) => d.id !== id);
    showToast('Department deleted.', 'success');
    fetchData(); // refresh courses (their major may be gone)
  } catch (err) {
    showToast('Failed to delete department.', 'error');
  }
};

// ── Majors CRUD ────────────────────────────────────────────────
const handleCreateMajor = async (deptId) => {
  if (!newMajorName.value) return;
  savingMajor.value = true;
  try {
    const { data } = await api.post('/majors', { department_id: deptId, name: newMajorName.value });
    const dept = departments.value.find((d) => d.id === deptId);
    if (dept) dept.majors.push({ ...data.major, courses_count: 0 });
    newMajorName.value = '';
    addMajorDeptId.value = null;
    showToast('Major created!', 'success');
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to create major.', 'error');
  } finally {
    savingMajor.value = false;
  }
};

const startEditMajor = (major) => {
  editingMajorId.value = major.id;
  editMajorForm.name = major.name;
};

const handleUpdateMajor = async (major) => {
  try {
    const { data } = await api.put(`/majors/${major.id}`, { name: editMajorForm.name, department_id: major.department_id });
    for (const dept of departments.value) {
      const idx = dept.majors.findIndex((m) => m.id === major.id);
      if (idx !== -1) { dept.majors[idx] = { ...dept.majors[idx], ...data.major }; break; }
    }
    editingMajorId.value = null;
    showToast('Major updated!', 'success');
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to update major.', 'error');
  }
};

const askDeleteMajor = (major) => {
  showConfirm('Delete Major?', `Delete "${major.name}"? Courses in this major will become unassigned.`, 'Delete', () => handleDeleteMajor(major));
};

const handleDeleteMajor = async (major) => {
  try {
    await api.delete(`/majors/${major.id}`);
    for (const dept of departments.value) {
      dept.majors = dept.majors.filter((m) => m.id !== major.id);
    }
    showToast('Major deleted.', 'success');
    fetchData();
  } catch (err) {
    showToast('Failed to delete major.', 'error');
  }
};

// ── Course Edit/Delete ─────────────────────────────────────────
const openEditCourseModal = (course) => {
  editingCourse.value = course;
  courseForm.name = course.name;
  courseForm.schedule_info = course.schedule_info || '';
  courseForm.major_id = course.major_id || '';
  showCreateCourseModal.value = true;
};

const closeCourseModal = () => {
  showCreateCourseModal.value = false;
  editingCourse.value = null;
  courseForm.name = '';
  courseForm.schedule_info = '';
  courseForm.major_id = '';
};

const handleUpdateCourse = async () => {
  creatingCourse.value = true;
  try {
    const { data } = await api.put(`/courses/${editingCourse.value.id}`, {
      name: courseForm.name,
      schedule_info: courseForm.schedule_info,
      major_id: courseForm.major_id || null,
    });
    const idx = courses.value.findIndex((c) => c.id === editingCourse.value.id);
    if (idx !== -1) courses.value[idx] = { ...courses.value[idx], ...data.course };
    closeCourseModal();
    showToast('Course updated!', 'success');
    fetchDepartments();
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to update course.', 'error');
  } finally {
    creatingCourse.value = false;
  }
};

const askDeleteCourse = (course) => {
  showConfirm('Delete Course?', `Delete "${course.name}"? All sessions and enrollment data will be lost.`, 'Delete', () => handleDeleteCourse(course.id));
};

const handleDeleteCourse = async (id) => {
  try {
    await api.delete(`/courses/${id}`);
    courses.value = courses.value.filter((c) => c.id !== id);
    showToast('Course deleted.', 'success');
    fetchDepartments();
  } catch (err) {
    showToast('Failed to delete course.', 'error');
  }
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
    showToast('Failed to load dashboard data. Please refresh.', 'error');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
  fetchDepartments();
  fetchTelegramStatus();
});

const setCampusPreset = () => {
  sessionForm.latitude = 13.5875483;
  sessionForm.longitude = 102.942477;
  showToast('Campus preset coordinates applied.', 'info');
};

const detectLocation = () => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        sessionForm.latitude = parseFloat(pos.coords.latitude.toFixed(7));
        sessionForm.longitude = parseFloat(pos.coords.longitude.toFixed(7));
        showToast('Location detected successfully!', 'success');
      },
      (err) => {
        showToast('Could not detect location. Using campus preset.', 'warning');
        setCampusPreset();
      }
    );
  } else {
    showToast('Geolocation not supported. Using campus preset.', 'warning');
    setCampusPreset();
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
    showToast('Session started! Launching QR screen...', 'success');
    router.push(`/teacher/session/${data.session.id}`);
  } catch (err) {
    startSessionError.value = err.response?.data?.message || 'Failed to start session.';
  } finally {
    startingSession.value = false;
  }
};

const askCloseSession = (sessionId) => {
  showConfirm(
    'End Attendance Session?',
    'No new scans will be accepted after closing. This cannot be undone.',
    'End Session',
    () => closeSession(sessionId)
  );
};

const closeSession = async (sessionId) => {
  try {
    await api.post(`/attendance/sessions/${sessionId}/close`);
    activeSessions.value = [];
    showToast('Session closed successfully.', 'success');
    fetchData();
  } catch (err) {
    showToast('Failed to close session: ' + (err.response?.data?.message || err.message), 'error');
  }
};

const handleCreateCourse = async () => {
  creatingCourse.value = true;
  try {
    await api.post('/courses', courseForm);
    showCreateCourseModal.value = false;
    courseForm.name = '';
    courseForm.schedule_info = '';
    courseForm.major_id = '';
    showToast('Course created successfully!', 'success');
    await fetchData();
    fetchDepartments();
  } catch (err) {
    showToast('Failed to create course: ' + (err.response?.data?.message || err.message), 'error');
  } finally {
    creatingCourse.value = false;
  }
};

const openEnrollModal = async (course) => {
  selectedCourse.value = course;
  try {
    const { data } = await api.get(`/courses/${course.id}`);
    enrolledRoster.value = data.course.students || [];
    showEnrollModal.value = true;
  } catch (err) {
    showToast('Failed to load roster: ' + err.message, 'error');
  }
};

const handleEnrollStudent = async () => {
  if (!newStudentId.value || !selectedCourse.value) return;
  enrolling.value = true;
  try {
    await api.post('/enrollments', {
      course_id: selectedCourse.value.id,
      student_id: newStudentId.value,
    });
    newStudentId.value = '';
    const { data } = await api.get(`/courses/${selectedCourse.value.id}`);
    enrolledRoster.value = data.course.students || [];
    showToast('Student enrolled successfully!', 'success');
    fetchData();
  } catch (err) {
    showToast('Failed to enroll student: ' + (err.response?.data?.message || err.message), 'error');
  } finally {
    enrolling.value = false;
  }
};

const askUnenroll = (student) => {
  showConfirm(
    'Remove Student?',
    `Remove ${student.name} from ${selectedCourse.value?.name}?`,
    'Remove',
    () => handleUnenrollStudent(student.id)
  );
};

const handleUnenrollStudent = async (studentId) => {
  try {
    await api.delete(`/enrollments/${selectedCourse.value.id}/${studentId}`);
    enrolledRoster.value = enrolledRoster.value.filter((s) => s.id !== studentId);
    showToast('Student removed from course.', 'success');
    fetchData();
  } catch (err) {
    showToast('Failed to remove student: ' + (err.response?.data?.message || err.message), 'error');
  }
};

const askResetDevice = (student) => {
  showConfirm(
    'Reset Device Lock?',
    `Remove the device lock for ${student.name}? They will be able to log in from a new device next time.`,
    'Reset',
    () => handleResetDevice(student)
  );
};

const handleResetDevice = async (student) => {
  try {
    const { data } = await api.post(`/students/${student.id}/reset-device`);
    // Update the student entry in the roster
    const idx = enrolledRoster.value.findIndex((s) => s.id === student.id);
    if (idx !== -1) {
      enrolledRoster.value[idx] = { ...enrolledRoster.value[idx], device_id: null, device_name: null, device_registered_at: null };
    }
    // Also update allStudents list
    const globalIdx = allStudents.value.findIndex((s) => s.id === student.id);
    if (globalIdx !== -1) {
      allStudents.value[globalIdx] = { ...allStudents.value[globalIdx], device_id: null, device_name: null, device_registered_at: null };
    }
    showToast(data.message || `Device lock for ${student.name} has been reset.`, 'success');
  } catch (err) {
    showToast('Failed to reset device: ' + (err.response?.data?.message || err.message), 'error');
  }
};
</script>
