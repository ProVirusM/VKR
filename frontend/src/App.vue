<template>
  <div>
    <Navbar />
    <main class="main-content">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import Navbar from './views/Navbar.vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
</script>

<style scoped>
.main-content {
  padding: 2rem;
  margin-top: 80px; /* Оставляем только этот отступ, равный высоте навигации */
}
</style>
