<template>
  <!-- Fixed bottom-right toast stack (above mobile nav) -->
  <teleport to="body">
    <div class="fixed bottom-24 sm:bottom-6 right-4 z-[9999] flex flex-col items-end gap-2.5 pointer-events-none">
      <transition-group name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-2xl shadow-2xl border max-w-sm w-full sm:max-w-xs"
          :class="toastClass(toast.type)"
        >
          <!-- Icon -->
          <div class="flex-shrink-0 mt-0.5">
            <svg v-if="toast.type === 'success'" class="w-4.5 h-4.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <svg v-else-if="toast.type === 'error'" class="w-4.5 h-4.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <svg v-else-if="toast.type === 'warning'" class="w-4.5 h-4.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <svg v-else class="w-4.5 h-4.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>

          <!-- Message -->
          <p class="text-xs sm:text-sm font-medium text-slate-100 leading-snug flex-1">
            {{ toast.message }}
          </p>

          <!-- Dismiss -->
          <button
            @click="dismiss(toast.id)"
            class="flex-shrink-0 text-slate-400 hover:text-white transition-colors"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup>
import { useToast } from '../composables/useToast';

const { toasts, dismiss } = useToast();

const toastClass = (type) => {
  switch (type) {
    case 'success': return 'bg-slate-900/95 border-emerald-500/40 backdrop-blur-xl';
    case 'error':   return 'bg-slate-900/95 border-rose-500/40 backdrop-blur-xl';
    case 'warning': return 'bg-slate-900/95 border-amber-500/40 backdrop-blur-xl';
    default:        return 'bg-slate-900/95 border-sky-500/40 backdrop-blur-xl';
  }
};
</script>

<style scoped>
.toast-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from   { opacity: 0; transform: translateX(48px) scale(0.92); }
.toast-leave-to     { opacity: 0; transform: translateX(48px) scale(0.96); }
</style>
