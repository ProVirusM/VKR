<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}

const goToLogin = () => {
  router.push('/login')
}
</script>

<template>
  <header class="navbar">
    <div class="navbar-logo">
      <img alt="Vue logo" src="@/assets/logo4.svg" width="60" height="60" />
      <span class="brand-name">IT-заказы</span>
    </div>
    <nav class="navbar-links">
      <RouterLink to="/" exact>Home</RouterLink>
      <RouterLink to="/about">About</RouterLink>
      <RouterLink to="/customer/orders">Заказы</RouterLink>

      <template v-if="!auth.isAuthenticated">
        <v-btn color="primary" class="navbar-btn" @click="goToLogin">Войти</v-btn>
      </template>
      <template v-else>
        <v-btn color="error" class="navbar-btn" @click="handleLogout">Выйти</v-btn>
      </template>
    </nav>
  </header>
</template>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #2c3e50;
  padding: 0.75rem 1.5rem;
  color: #fff;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  z-index: 1000;
  height: 80px;
}

.navbar-logo {
  display: flex;
  align-items: center;
}

.navbar-logo img {
  margin-right: 0.5rem;
}

.brand-name {
  font-size: 1.25rem;
  font-weight: bold;
}

.navbar-links {
  display: flex;
  gap: 1rem;
}

.navbar-links a,
.navbar-btn {
  color: #ecf0f1;
  text-decoration: none;
  padding: 0.5rem 0.75rem;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.navbar-links a:hover,
.navbar-btn:hover {
  background-color: #34495e;
}

.router-link-exact-active {
  background-color: #1abc9c;
  color: white;
}
</style>
