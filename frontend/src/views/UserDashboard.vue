<!--<template>-->
<!--  <v-container>-->
<!--    <v-card class="pa-6">-->
<!--      <v-card-title class="text-h5">Личный кабинет</v-card-title>-->
<!--      <v-card-text>-->
<!--        Добро пожаловать, <strong>{{ user.name }}</strong>!-->
<!--      </v-card-text>-->

<!--      <v-btn color="error" @click="logout">Выйти</v-btn>-->
<!--    </v-card>-->
<!--  </v-container>-->
<!--</template>-->

<!--<script setup>-->
<!--import { ref, onMounted } from 'vue'-->
<!--import axios from 'axios'-->
<!--import { useRouter } from 'vue-router'-->

<!--const user = ref({})-->
<!--const router = useRouter()-->

<!--onMounted(async () => {-->
<!--  try {-->
<!--    const token = localStorage.getItem('token')-->
<!--    const response = await axios.get('/api/profile', {-->
<!--      headers: { Authorization: `Bearer ${token}` },-->
<!--    })-->
<!--    user.value = response.data-->
<!--  } catch {-->
<!--    router.push('/login')-->
<!--  }-->
<!--})-->

<!--const logout = () => {-->
<!--  localStorage.removeItem('token')-->
<!--  router.push('/login')-->
<!--}-->
<!--</script>-->
<template>
  <v-container class="py-8">
    <v-card elevation="10" rounded="xl" class="pa-6 bg-grey-lighten-5">
      <v-row justify="center">
        <v-col cols="12" md="6" class="text-center">
          <!-- Сделаем аватар больше и по центру -->
          <v-avatar size="120" class="mb-4 mx-auto">
            <!-- Используем изображение профиля -->
            <v-img src="https://www.w3schools.com/w3images/avatar2.png" alt="Avatar" />
          </v-avatar>
          <!-- Приветственное сообщение -->
          <h2 class="text-h5 font-weight-bold mb-1">Добро пожаловать, {{ user.name }}!</h2>
          <p class="text-subtitle-1 text-grey-darken-1">Ваш личный кабинет</p>
        </v-col>
      </v-row>

      <v-divider class="my-6" />

      <v-row dense justify="center">
        <template v-if="Array.isArray(user.roles)">
          <!-- Заказчик -->
          <template v-if="user.roles.includes('customer')">
            <v-col cols="12" md="4">
              <v-btn block color="primary" @click="goToCreateOrder" prepend-icon="mdi-plus-box">
                Создать заказ
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="blue-darken-1" @click="goToActiveOrders" prepend-icon="mdi-briefcase-clock-outline">
                Активные заказы
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="indigo" @click="goToCompletedOrders" prepend-icon="mdi-history">
                История заказов
              </v-btn>
            </v-col>
          </template>

          <!-- Подрядчик -->
          <template v-if="user.roles.includes('contractor')">
            <v-col cols="12" md="4">
              <v-btn block color="green-darken-2" @click="goToReviews" prepend-icon="mdi-star-outline">
                Мои отзывы
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="teal-darken-2" @click="goToApprovedOrders" prepend-icon="mdi-check-bold">
                Утвержденные заказы
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="amber-darken-2" @click="goToRespondedOrders" prepend-icon="mdi-message-reply-text-outline">
                Отозвавшиеся заказы
              </v-btn>
            </v-col>
          </template>
        </template>
      </v-row>

      <!-- Переместили кнопку "Выйти" вниз -->
      <v-divider class="my-6" />
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

const goToCreateOrder = () => router.push('/customer/create-order')
const goToMyOrders = () => router.push('/customer/orders')
const goToReviews = () => router.push('/contractor/reviews')
const goToApprovedOrders = () => router.push('/contractor/approved-orders')
const goToRespondedOrders = () => router.push('/contractor/responded-orders')
const goToActiveOrders = () => router.push('/customer/active-orders')
const goToCompletedOrders = () => router.push('/customer/completed-orders')
</script>


