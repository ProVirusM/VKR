<template>
  <v-container fluid class="py-10">
    <v-row justify="center">
      <v-col cols="12" md="8">
        <h2 class="text-center mb-6">История заказов</h2>

        <!-- Проверка, чтобы избежать ошибки на стадии рендеринга -->
        <v-card
          v-for="order in orders"
          :key="order.id"
          class="mb-4 pa-6"
          elevation="10"
          rounded="xl"
        >
          <v-card-title class="text-h5 text-primary">
            {{ order.ord_title }}
          </v-card-title>

          <v-card-text class="text-body-1 text-grey-darken-2">
            {{ order.ord_text }}
          </v-card-text>

          <!-- Технологии -->
          <v-card-text v-if="order.ord_stacks && order.ord_stacks.length">
            <div class="text-subtitle-2 mb-2">Технологии:</div>
            <v-chip-group>
              <v-chip
                v-for="stack in order.ord_stacks"
                :key="stack.id"
                color="primary"
                variant="outlined"
                class="ma-1"
              >
                {{ stack.title }}
              </v-chip>
            </v-chip-group>
          </v-card-text>

          <v-divider class="my-4" />

          <v-row justify="space-between">
            <v-col cols="6">
              <div class="text-caption text-grey">Статус:</div>
              <div :class="getStatusClass(order.ord_status)" class="text-subtitle-2">
                {{ order.ord_status }}
              </div>
            </v-col>
            <v-col cols="6" class="text-right">
              <div class="text-caption text-grey">Цена:</div>
              <div class="text-subtitle-2 text-error">
                {{ order.ord_price }} ₽
              </div>
            </v-col>
          </v-row>

          <div class="text-caption text-right text-grey mt-2">
            Срок: {{ order.ord_time }}
          </div>

          <v-card-actions class="justify-end">
            <v-btn
              color="primary"
              variant="elevated"
              rounded
              @click="viewDetails(order.id)"
            >
              Подробнее
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Сообщение, если нет заказов -->
        <p v-if="!orders.length" class="text-center">История заказов пуста.</p>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const orders = ref([])
const router = useRouter()

const viewDetails = (orderId) => {
  router.push(`/order/${orderId}`)
}

const getStatusClass = (status) => {
  switch (status) {
    case 'В процессе':
      return 'text-orange'
    case 'Завершен':
      return 'text-green'
    case 'Отменен':
      return 'text-red'
    default:
      return 'text-grey'
  }
}

onMounted(async () => {
  const token = localStorage.getItem('token')
  try {
    const res = await axios.get('/api/customer/completed-orders', {
      headers: { Authorization: `Bearer ${token}` }
    })
    orders.value = Object.values(res.data)
  } catch (error) {
    console.error('Ошибка при загрузке заказов', error)
  }
})
</script>

<style scoped>
/* Стиль для визуальных эффектов */
.v-card {
  transition: box-shadow 0.3s ease;
}

.v-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.text-grey {
  color: #9e9e9e;
}

.text-orange {
  color: #ffa500;
}

.text-green {
  color: #4caf50;
}

.text-red {
  color: #f44336;
}
</style>
