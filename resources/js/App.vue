<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-['Plus_Jakarta_Sans',sans-serif]">
    <Navbar />

    <main class="flex-1 pb-24 sm:pb-20 md:pb-8">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import Navbar from './components/Navbar.vue';
import { useAuth } from './stores/auth';

const { fetchUser } = useAuth();

onMounted(() => {
  fetchUser();
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
