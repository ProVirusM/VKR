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

        <!-- Информация об утвержденном исполнителе -->
        <v-card v-if="order.ord_status === 'Завершен' && approvedContractor" class="mb-4 pa-4" variant="outlined">
          <div class="d-flex align-center mb-2">
            <v-icon color="success" class="mr-2">mdi-account-check</v-icon>
            <strong>Утвержденный исполнитель:</strong>
          </div>
          <v-card class="contractor-card pa-3" @click="goToContractorProfile(approvedContractor.contractorId)">
            <div class="d-flex align-center">
              <v-avatar size="40" color="primary" class="mr-3">
                <span class="text-h6 white--text">
                  {{ approvedContractor.userName ? approvedContractor.userName.charAt(0).toUpperCase() : '' }}
                </span>
              </v-avatar>
              <div>
                <div class="text-subtitle-1 font-weight-bold">
                  {{ approvedContractor.userSurname }} {{ approvedContractor.userName }} {{ approvedContractor.userPatronymic }}
                </div>
                <div class="text-caption text-grey">Нажмите, чтобы просмотреть профиль</div>
              </div>
            </div>
          </v-card>
        </v-card>

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
      </v-card-text>

      <v-card-actions class="d-flex justify-space-between">
        <v-btn color="grey" variant="outlined" @click="$router.back()">Назад</v-btn>

        <template v-if="Array.isArray(user.roles)">
          <template v-if="user.roles.includes('customer')">
            <v-btn
              v-if="order.ord_status !== 'Завершен'"
              color="primary"
              @click="goToResponders"
            >
              Откликнувшиеся
            </v-btn>
            <v-btn
              v-if="order.ord_status === 'Завершен'"
              color="success"
              @click="goToFeedback"
            >
              Оставить отзыв
            </v-btn>
          </template>
          <v-btn
            v-else-if="user.roles.includes('contractor')"
            :color="hasResponded ? 'error' : 'success'"
            @click="hasResponded ? cancelResponse() : respondToOrder()"
          >
            {{ hasResponded ? 'Отменить отклик' : 'Откликнуться' }}
          </v-btn>
        </template>
        <v-btn
          v-if="user && Array.isArray(user.roles) && user.roles.includes('customer') && order && order.ord_status === 'Новый'"
          color="error"
          @click="deleteOrder"
        >
          Удалить заказ
        </v-btn>
      </v-card-actions>
    </v-card>

    <div v-else class="text-center mt-10">
      <v-progress-circular indeterminate color="primary" size="50" />
      <p class="mt-3">Загрузка заказа...</p>
    </div>

    <!-- Диалог подтверждения удаления -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5">
          Подтверждение удаления
        </v-card-title>

        <v-card-text>
          Вы уверены, что хотите удалить этот заказ? Это действие нельзя будет отменить.
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey-darken-1"
            variant="text"
            @click="deleteDialog = false"
          >
            Отмена
          </v-btn>
          <v-btn
            color="error"
            variant="text"
            @click="confirmDelete"
          >
            Удалить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const order = ref(null)
const stacks = ref([])
const user = ref(null)
const isLoading = ref(true)
const error = ref(null)
const approvedContractor = ref(null)
const deleteDialog = ref(false)
const hasResponded = ref(false)

// Состояние для Snackbar
const snackbar = ref({
  show: false,
  text: '',
  color: 'success'
})

// Функция для показа уведомлений
const showNotification = (text, color = 'success') => {
  snackbar.value = {
    show: true,
    text,
    color
  }
}

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
    const [orderRes, userRes] = await Promise.all([
      axios.get(`/api/orders/${id}/full`, {
        headers: { Authorization: `Bearer ${token.value}` }
      }),
      axios.get('/api/profile', {
        headers: { Authorization: `Bearer ${token.value}` }
      })
    ])

    order.value = orderRes.data?.order || null
    stacks.value = orderRes.data?.stacks || []
    user.value = userRes.data || null

    // Если пользователь - исполнитель, проверяем его отклик
    if (user.value && Array.isArray(user.value.roles) && user.value.roles.includes('contractor')) {
      try {
        const responseCheck = await axios.get(`/api/orders/${id}/check-response`, {
          headers: { Authorization: `Bearer ${token.value}` }
        })
        hasResponded.value = responseCheck.data.hasResponded
      } catch (err) {
        console.error('Ошибка при проверке отклика:', err)
      }
    }

    // Если заказ завершен, загружаем информацию об утвержденном исполнителе
    if (order.value?.ord_status === 'Завершен') {
      try {
        const approvedContractorRes = await axios.get(`/api/orders/${id}/approved-contractor`, {
          headers: { Authorization: `Bearer ${token.value}` }
        })
        approvedContractor.value = approvedContractorRes.data
      } catch (err) {
        console.error('Ошибка при загрузке утвержденного исполнителя:', err)
      }
    }
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

const respondToOrder = async () => {
  try {
    if (!token.value) {
      router.push('/login')
      return
    }

    const response = await axios.post(
      `/api/orders/${route.params.id}/respond`,
      {},
      {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        }
      }
    )

    if (response.status === 201) {
      showNotification('Отклик успешно отправлен!', 'success')
      hasResponded.value = true
    }
  } catch (err) {
    console.error('Ошибка при отклике:', err)
    showNotification(err.response?.data?.message || 'Ошибка при отправке отклика', 'error')
  }
}

const cancelResponse = async () => {
  try {
    if (!token.value) {
      router.push('/login')
      return
    }

    const response = await axios.post(
      `/api/orders/${route.params.id}/cancel-response`,
      {},
      {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        }
      }
    )

    if (response.status === 200) {
      showNotification('Отклик успешно отменен!', 'success')
      hasResponded.value = false
    }
  } catch (err) {
    console.error('Ошибка при отмене отклика:', err)
    showNotification(err.response?.data?.message || 'Ошибка при отмене отклика', 'error')
  }
}

const goToResponders = () => {
  router.push(`/customer/orders/${route.params.id}/responders`)
}

const goToContractorProfile = (contractorId) => {
  router.push(`/contractor/${contractorId}`)
}

const goToFeedback = () => {
  router.push(`/order/${route.params.id}/feedback`)
}

const deleteOrder = () => {
  deleteDialog.value = true
}

const confirmDelete = async () => {
  deleteDialog.value = false; // Закрываем диалог сразу
  try {
    if (!token.value) {
      router.push('/login');
      return;
    }

    const response = await axios.delete(`/api/orders/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });

    if (response.status === 200) {
      showNotification('Заказ успешно удален!', 'success');
      setTimeout(() => {
        router.push('/customer/active-orders');
      }, 1500);
    }
  } catch (err) {
    console.error('Ошибка при удалении заказа:', err);
    showNotification(err.response?.data?.message || 'Произошла ошибка при удалении заказа', 'error');
  }
}
</script>

<style scoped>
.gap-2 {
  gap: 8px;
}

.contractor-card {
  cursor: pointer;
  transition: all 0.3s ease;
}

.contractor-card:hover {
  background-color: #f5f5f5;
  transform: translateY(-2px);
}
</style>
