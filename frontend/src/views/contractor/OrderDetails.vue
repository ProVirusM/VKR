<template>
  <v-container class="py-10">
    <v-card
      v-if="order"
      class="mx-auto pa-6"
      max-width="800"
      elevation="10"
      rounded="2xl"
    >
      <v-card-title class="d-flex justify-space-between align-center">
        <span class="text-h5 font-weight-bold">{{ order.ord_title }}</span>
        <v-chip color="blue lighten-2" text-color="white">{{ order.ord_status }}</v-chip>
      </v-card-title>

      <v-card-text>
        <p class="mb-3 text-body-1">
          <strong>Описание:</strong><br /> {{ order.ord_text }}
        </p>

        <v-row class="mb-4">
          <v-col cols="12" sm="6">
            <v-icon class="mr-1" color="green">mdi-currency-rub</v-icon>
            <strong>Цена:</strong> {{ order.ord_price }} ₽
          </v-col>
          <v-col cols="12" sm="6">
            <v-icon class="mr-1" color="orange">mdi-clock-outline</v-icon>
            <strong>Срок:</strong> {{ order.ord_time }}
          </v-col>
        </v-row>

        <v-divider class="my-4" />

        <div class="mb-3">
          <strong>Технологии:</strong>
          <div class="mt-2 d-flex flex-wrap gap-2">
            <v-chip
              v-for="stack in stacks"
              :key="stack.id"
              color="deep-purple accent-4"
              text-color="white"
              class="ma-1"
              label
              pill
            >
              {{ stack.title }}
            </v-chip>
          </div>
        </div>

        <v-divider class="my-4" />

        <div class="mb-3">
          <strong>Статус участия:</strong>
          <v-chip
            :color="getStatusColor(order.order_contractor_status)"
            class="ml-2"
            size="small"
          >
            {{ order.order_contractor_status }}
          </v-chip>
        </div>
      </v-card-text>

      <v-card-actions class="d-flex justify-space-between">
        <v-btn color="grey" variant="outlined" @click="$router.back()">Назад</v-btn>
      </v-card-actions>
    </v-card>

    <div v-else class="text-center mt-10">
      <v-progress-circular indeterminate color="primary" size="50" />
      <p class="mt-3">Загрузка заказа...</p>
    </div>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const order = ref(null)
const stacks = ref([])
const isLoading = ref(true)
const error = ref(null)

// Получаем токен безопасным способом
const token = ref('')
onMounted(() => {
  token.value = localStorage.getItem('token') || ''
  if (!token.value) {
    router.push('/login')
    return
  }
  loadData()
})

const loadData = async () => {
  try {
    const id = route.params.id
    const response = await axios.get(`/api/orders/${id}/full`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })

    order.value = response.data?.order || null
    stacks.value = response.data?.stacks || []
  } catch (err) {
    error.value = err.response?.data?.message || err.message
    console.error('Ошибка:', err)
    if (err.response?.status === 401) {
      router.push('/login')
    }
  } finally {
    isLoading.value = false
  }
}

const getStatusColor = (status) => {
  switch (status) {
    case 'Принят':
      return 'success'
    case 'Отклонен':
      return 'error'
    case 'В ожидании':
      return 'warning'
    default:
      return 'grey'
  }
}
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}
</style> 