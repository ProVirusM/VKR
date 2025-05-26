<template>
  <v-container>
    <h2 class="text-center mb-6">Отозвавшиеся заказы</h2>

    <!-- Проверка, чтобы избежать ошибки на стадии рендеринга -->
    <v-row v-if="loading" class="fill-height align-center justify-center">
      <v-col cols="12" class="text-center">
        <v-progress-circular
          indeterminate
          color="primary"
          size="64"
        ></v-progress-circular>
        <p class="mt-4">Загрузка заказов...</p>
      </v-col>
    </v-row>

    <v-row v-else-if="approvedOrders && approvedOrders.length" justify="center">
      <v-col cols="12" md="8">
        <v-card
          v-for="order in approvedOrders"
          :key="order.id"
          class="mb-4 pa-6"
          elevation="10"
          rounded="xl"
        >
          <v-card-title class="text-h5 text-primary">
            {{ order.title }} <!-- Используем 'title' как возвращает бэкенд -->
          </v-card-title>

          <v-card-text class="text-body-1 text-grey-darken-2">
            {{ order.text }} <!-- Используем 'text' как возвращает бэкенд -->
          </v-card-text>

          <!-- Технологии -->
          <v-card-text v-if="order.stacks && order.stacks.length">
            <div class="text-subtitle-2 mb-2">Технологии:</div>
            <v-chip-group>
              <v-chip
                v-for="stack in order.stacks"
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
              <div class="text-caption text-grey">Статус заказа:</div>
              <div :class="getStatusClass(order.status)" class="text-subtitle-2">
                {{ order.status }} <!-- Статус самого заказа -->
              </div>
            </v-col>
            <v-col cols="6" class="text-right">
              <div class="text-caption text-grey">Ваш статус:</div>
              <div class="text-subtitle-2 text-green">
                {{ order.contractor_status }} <!-- Статус связи с подрядчиком -->
              </div>
            </v-col>
          </v-row>

          <v-row justify="space-between" class="mt-2">
            <v-col cols="6">
              <div class="text-caption text-grey">Цена:</div>
              <div class="text-subtitle-2 text-error">
                {{ order.price }} ₽
              </div>
            </v-col>
            <v-col cols="6" class="text-right">
              <div class="text-caption text-grey">Срок:</div>
              <div class="text-subtitle-2">
                {{ order.time }}
              </div>
            </v-col>
          </v-row>

          <v-card-actions class="justify-end">
            <v-btn
              color="primary"
              variant="elevated"
              rounded
              @click="viewDetails(order.id)"
            >
              Подробнее
            </v-btn>
            <v-btn
              v-if="order.status === 'Завершен'"
              :color="existingChats[order.id] ? 'primary' : 'success'"
              :prepend-icon="existingChats[order.id] ? 'mdi-chat' : 'mdi-chat-plus'"
              @click="handleChat(order.id)"
            >
              {{ existingChats[order.id] ? 'Открыть чат' : 'Начать чат' }}
            </v-btn>
            <v-btn variant="text" @click="goBack">Назад</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Сообщение, если нет заказов -->
    <v-alert
      v-else
      type="info"
      variant="tonal"
      class="mt-4"
    >
      Вы еще не отозвались на заказы.
    </v-alert>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const approvedOrders = ref([])
const loading = ref(true)
const router = useRouter()
const existingChats = ref({})

const fetchApprovedOrders = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    const response = await axios.get('/api/contractor/responded-orders', {
      headers: { Authorization: `Bearer ${token}` }
    })

    approvedOrders.value = response.data

    // Check for existing chats for each order
    for (const order of approvedOrders.value) {
      if (order.status === 'Завершен') {
        try {
          const chatRes = await axios.get(`/api/chats/check-order/${order.id}`, {
            headers: { Authorization: `Bearer ${token}` }
          })
          if (chatRes.data.exists && chatRes.data.chatId) {
            existingChats.value[order.id] = chatRes.data.chatId
          }
        } catch (error) {
          console.error('Error checking chat:', error)
        }
      }
    }
  } catch (error) {
    console.error('Ошибка при загрузке отозвавшихся заказов:', error)
  } finally {
    loading.value = false
  }
}

const viewDetails = (id) => {
  router.push(`/order/${id}`)
}

const handleChat = async (orderId) => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    if (existingChats.value[orderId]) {
      router.push(`/chat/${existingChats.value[orderId]}`)
    } else {
      const res = await axios.post('/api/chats/create-order',
        { orderId },
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      )
      if (res.data.id) {
        existingChats.value[orderId] = res.data.id
        router.push(`/chat/${res.data.id}`)
      }
    }
  } catch (error) {
    console.error('Error handling chat:', error)
  }
}

// Функция для определения класса статуса заказа
const getStatusClass = (status) => {
  switch (status) {
    case 'Новый':
      return 'text-info'; // Или другой цвет для нового
    case 'В работе':
      return 'text-orange-darken-2';
    case 'Завершен':
      return 'text-green-darken-2';
    case 'Отменен':
      return 'text-red-darken-2';
    default:
      return 'text-grey';
  }
};

const goBack = () => {
  router.back()
}

onMounted(() => {
  fetchApprovedOrders()
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

/* Добавьте или измените цвета статусов, если нужно */
.text-info {
  color: #2196F3; /* Пример синего для 'Новый' */
}

.text-orange-darken-2 {
  color: #EF6C00; /* Темно-оранжевый для 'В работе' */
}

.text-green-darken-2 {
  color: #2E7D32; /* Темно-зеленый для 'Завершен' */
}

.text-red-darken-2 {
  color: #C62828; /* Темно-красный для 'Отменен' */
}
</style>
