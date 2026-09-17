<template>
  <div class="min-h-screen bg-[#050811] text-slate-100 flex flex-col font-sans relative overflow-x-hidden">
    <!-- Ambient Background Lighting Orbs & Dot Grid -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
      <!-- Dot grid pattern overlay -->
      <div class="absolute inset-0 bg-grid-pattern opacity-60"></div>

      <!-- Top-left glowing indigo/purple orb -->
      <div class="orb-1 absolute -top-40 -left-40 w-96 h-96 sm:w-[540px] sm:h-[540px] bg-indigo-600/18 rounded-full blur-[120px]"></div>

      <!-- Center-right glowing violet/pink orb -->
      <div class="orb-2 absolute top-1/3 -right-40 w-80 h-80 sm:w-[500px] sm:h-[500px] bg-purple-600/15 rounded-full blur-[130px]"></div>

      <!-- Bottom-left glowing teal/emerald orb -->
      <div class="absolute -bottom-32 left-1/4 w-80 h-80 sm:w-[480px] sm:h-[480px] bg-emerald-500/12 rounded-full blur-[120px]"></div>
    </div>

    <!-- Application Chrome & Pages (Above ambient layer) -->
    <div class="relative z-10 flex flex-col min-h-screen">
      <Navbar />

      <main class="flex-1 pb-24 sm:pb-20 md:pb-8">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

      <!-- Global Toast Notifications -->
      <ToastNotification />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import Navbar from './components/Navbar.vue';
import ToastNotification from './components/ToastNotification.vue';
import { useAuth } from './stores/auth';

const { fetchUser } = useAuth();

onMounted(() => {
  fetchUser();
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(6px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
