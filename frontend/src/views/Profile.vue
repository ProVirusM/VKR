<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h5">Личный кабинет</v-card-title>
      <v-card-text>
        Добро пожаловать, <strong>{{ user.name }}</strong>!

        <template v-if="Array.isArray(user.roles) && user.roles.includes('customer')">
          <v-btn class="mt-3" color="primary" @click="goToCreateOrder">Создать заказ</v-btn>
          <v-btn class="mt-3" color="primary" @click="goToCreateOrder">Создать заказ</v-btn>
          <v-btn class="mt-3" @click="goToActiveOrders">Активные заказы</v-btn>
          <v-btn class="mt-3" @click="goToCompletedOrders">История заказов</v-btn>
        </template>

        <template v-if="Array.isArray(user.roles) && user.roles.includes('contractor')">
          <v-btn class="mt-3" color="primary" @click="goToReviews">Мои отзывы</v-btn>
          <v-btn class="mt-3" @click="goToApprovedOrders">Утвержденные заказы</v-btn>
          <v-btn class="mt-3" @click="goToRespondedOrders">Отозвавшиеся заказы</v-btn>
          <v-btn class="mt-3" color="primary" @click="goToMyProjects">Мои проекты</v-btn>
          <v-btn class="mt-3" color="primary" @click="goToCreateProject">Создать проект</v-btn>
        </template>
      </v-card-text>

      <v-btn class="mt-4" color="error" @click="logout">Выйти</v-btn>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const user = ref({})
const router = useRouter()

onMounted(async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/profile', {
      headers: { Authorization: `Bearer ${token}` },
    })
    user.value = response.data
  } catch {
    router.push('/login')
  }
})

const logout = () => {
  localStorage.removeItem('token')
  router.push('/login')
}

// Навигация по кнопкам
const goToCreateOrder = () => router.push('/customer/create-order')
const goToMyOrders = () => router.push('/customer/orders')
const goToReviews = () => router.push('/contractor/reviews')
const goToApprovedOrders = () => router.push('/contractor/approved-orders')
const goToRespondedOrders = () => router.push('/contractor/responded-orders')
const goToActiveOrders = () => router.push('/customer/active-orders')
const goToCompletedOrders = () => router.push('/customer/completed-orders')
const goToMyProjects = () => router.push('/contractor/projects')
const goToCreateProject = () => router.push('/contractor/create-project')
</script>
