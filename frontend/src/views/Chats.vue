<template>
  <v-container class="py-8">
    <v-card elevation="10" rounded="xl" class="pa-6 bg-grey-lighten-5">
      <v-row>
        <v-col cols="12">
          <h2 class="text-h4 font-weight-bold mb-6">Мои чаты</h2>
        </v-col>
      </v-row>

      <!-- Список чатов -->
      <v-row v-if="chats.length > 0">
        <v-col v-for="chat in chats" :key="chat.id" cols="12" md="6" lg="4">
          <v-card
            class="chat-card"
            :to="'/chat/' + chat.id"
            elevation="2"
            hover
          >
            <v-card-item>
              <template v-slot:prepend>
                <v-avatar color="primary" size="40">
                  <v-icon icon="mdi-account"></v-icon>
                </v-avatar>
              </template>
              
              <v-card-title>
                {{ isCustomer ? chat.contractor.user.name + ' ' + chat.contractor.user.surname : chat.customer.user.name + ' ' + chat.customer.user.surname }}
              </v-card-title>
              
              <v-card-subtitle>
                {{ getLastMessage(chat) }}
              </v-card-subtitle>
            </v-card-item>
          </v-card>
        </v-col>
      </v-row>

      <!-- Сообщение, если чатов нет -->
      <v-row v-else>
        <v-col cols="12" class="text-center">
          <v-alert
            type="info"
            variant="tonal"
            class="mx-auto"
            max-width="500"
          >
            У вас пока нет активных чатов
          </v-alert>
        </v-col>
      </v-row>
    </v-card>

    <!-- Snackbar для уведомлений -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
      location="top"
    >
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          @click="snackbar.show = false"
        >
          Закрыть
        </v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

const router = useRouter()
const auth = useAuthStore()
const chats = ref([])
const user = ref({})

// Состояние для Snackbar
const snackbar = ref({
  show: false,
  text: '',
  color: 'success'
})

// Вычисляемые свойства для проверки роли
const isCustomer = computed(() => {
  return Array.isArray(user.value.roles) && user.value.roles.includes('customer')
})

const isContractor = computed(() => {
  return Array.isArray(user.value.roles) && user.value.roles.includes('contractor')
})

// Функция для показа уведомлений
const showNotification = (text, color = 'success') => {
  snackbar.value = {
    show: true,
    text,
    color
  }
}

// Функция для получения последнего сообщения
const getLastMessage = (chat) => {
  if (!chat.lastMessage) return 'Нет сообщений'
  return chat.lastMessage.msg_text.length > 50 
    ? chat.lastMessage.msg_text.substring(0, 50) + '...' 
    : chat.lastMessage.msg_text
}

// Загрузка данных пользователя
const loadUserData = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    const response = await axios.get('/api/profile', {
      headers: { Authorization: `Bearer ${token}` }
    })
    user.value = response.data
  } catch (err) {
    console.error('Ошибка при загрузке данных пользователя:', err)
    if (err.response?.status === 401) {
      router.push('/login')
    }
  }
}

// Загрузка чатов
const loadChats = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    const response = await axios.get('/api/chats', {
      headers: { Authorization: `Bearer ${token}` }
    })

    chats.value = response.data
  } catch (err) {
    console.error('Ошибка при загрузке чатов:', err)
    showNotification('Ошибка при загрузке чатов', 'error')
    if (err.response?.status === 401) {
      router.push('/login')
    }
  }
}

onMounted(async () => {
  await loadUserData()
  
  // Проверяем, имеет ли пользователь доступ к странице
  if (!isCustomer.value && !isContractor.value) {
    router.push('/dashboard')
    return
  }
  
  loadChats()
})
</script>

<style scoped>
.chat-card {
  transition: transform 0.2s;
}

.chat-card:hover {
  transform: translateY(-2px);
}
</style> 