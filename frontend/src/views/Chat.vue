<template>
  <v-container class="py-10">
    <v-row justify="center">
      <v-col cols="12" md="10" lg="8">
        <v-card class="chat-card" elevation="4">
          <!-- Chat Header -->
          <v-card-title class="d-flex align-center">
            <v-avatar size="40" color="primary" class="mr-3">
              <span class="text-h6 white--text">
                {{ chat?.contractor?.user?.name?.charAt(0) || '?' }}
              </span>
            </v-avatar>
            <div>
              <div class="text-h6">
                {{ chat?.contractor?.user?.surname }} {{ chat?.contractor?.user?.name }}
              </div>
              <div class="text-caption text-grey">
                {{ chat?.order?.title || 'Общий чат' }}
              </div>
            </div>
          </v-card-title>

          <!-- Messages Area -->
          <v-card-text class="chat-messages" ref="messagesContainer">
            <div v-if="messages.length === 0" class="text-center text-grey py-8">
              Нет сообщений. Начните общение!
            </div>
            <div v-else class="messages-list">
              <div
                v-for="message in messages"
                :key="message.id"
                :class="['message', isCurrentUserMessage(message) ? 'message-own' : 'message-other']"
              >
                <div class="message-content">
                  <div class="message-text">{{ message.msg_text }}</div>
                  <div class="message-time text-caption">
                    {{ formatTime(message.msg_timestamp) }}
                  </div>
                </div>
              </div>
            </div>
          </v-card-text>

          <!-- Message Input -->
          <v-card-actions class="chat-input">
            <v-text-field
              v-model="newMessage"
              placeholder="Введите сообщение..."
              variant="outlined"
              density="comfortable"
              hide-details
              @keyup.enter="sendMessage"
            >
              <template v-slot:append>
                <v-btn
                  icon="mdi-send"
                  color="primary"
                  variant="text"
                  @click="sendMessage"
                  :disabled="!newMessage.trim()"
                ></v-btn>
              </template>
            </v-text-field>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const chat = ref(null)
const messages = ref([])
const newMessage = ref('')
const messagesContainer = ref(null)
const currentUser = ref(null)
let messagePollingInterval = null

const loadCurrentUser = async () => {
  try {
    const response = await axios.get('/api/profile', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    currentUser.value = response.data
  } catch (error) {
    console.error('Error loading user profile:', error)
  }
}

const loadChat = async () => {
  try {
    console.log('Route params:', route.params)
    console.log('Chat ID from route:', route.params.id)
    const chatId = parseInt(route.params.id, 10)
    console.log('Parsed chat ID:', chatId)
    if (isNaN(chatId)) {
      console.error('Invalid chat ID:', route.params.id)
      return
    }
    const response = await axios.get(`/api/chats/${chatId}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    console.log('Chat response:', response.data)
    chat.value = response.data
    await loadMessages()
  } catch (error) {
    console.error('Error loading chat:', error)
  }
}

const loadMessages = async () => {
  try {
    console.log('Loading messages for chat ID:', route.params.id)
    const chatId = parseInt(route.params.id, 10)
    console.log('Parsed chat ID for messages:', chatId)
    if (isNaN(chatId)) {
      console.error('Invalid chat ID:', route.params.id)
      return
    }
    const response = await axios.get(`/api/chats/${chatId}/messages`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    console.log('Messages response:', response.data)
    messages.value = response.data
    await scrollToBottom()
  } catch (error) {
    console.error('Error loading messages:', error)
  }
}

const isCurrentUserMessage = (message) => {
  if (!currentUser.value) return false
  
  // Если текущий пользователь - заказчик
  if (currentUser.value.customerId) {
    return message.cst_id === currentUser.value.customerId
  }
  // Если текущий пользователь - исполнитель
  if (currentUser.value.contractorId) {
    return message.cnt_id === currentUser.value.contractorId
  }
  
  return false
}

const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  try {
    const chatId = parseInt(route.params.id, 10)
    if (isNaN(chatId)) {
      console.error('Invalid chat ID:', route.params.id)
      return
    }
    await axios.post(`/api/chats/${chatId}/messages`, 
      { text: newMessage.value },
      {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      }
    )
    newMessage.value = ''
    await loadMessages()
  } catch (error) {
    console.error('Error sending message:', error)
  }
}

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const startPolling = () => {
  messagePollingInterval = setInterval(loadMessages, 5000) // Poll every 5 seconds
}

onMounted(async () => {
  await loadCurrentUser()
  await loadChat()
  startPolling()
})

onUnmounted(() => {
  if (messagePollingInterval) {
    clearInterval(messagePollingInterval)
  }
})
</script>

<style scoped>
.chat-card {
  height: calc(100vh - 200px);
  display: flex;
  flex-direction: column;
}

.chat-messages {
  flex-grow: 1;
  overflow-y: auto;
  padding: 16px;
  background-color: #f5f5f5;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.message {
  max-width: 70%;
  display: flex;
  flex-direction: column;
}

.message-own {
  align-self: flex-end;
}

.message-other {
  align-self: flex-start;
}

.message-content {
  padding: 8px 12px;
  border-radius: 12px;
  background-color: white;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-own .message-content {
  background-color: #e3f2fd;
}

.message-text {
  margin-bottom: 4px;
}

.message-time {
  color: #666;
  font-size: 0.75rem;
}

.chat-input {
  padding: 16px;
  background-color: white;
  border-top: 1px solid #e0e0e0;
}
</style> 