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

      <!-- Описание исполнителя -->
      <template v-if="Array.isArray(user.roles) && user.roles.includes('contractor')">
        <v-card class="mb-6 pa-4" variant="outlined">
          <div class="d-flex justify-space-between align-center mb-2">
            <div class="text-h6">О себе</div>
            <v-btn
              v-if="!isEditing"
              icon="mdi-pencil"
              variant="text"
              @click="startEditing"
            ></v-btn>
          </div>

          <div v-if="!isEditing" class="text-body-1">
            {{ contractorDescription || 'Нет описания' }}
          </div>

          <v-form v-else @submit.prevent="saveDescription">
            <v-textarea
              v-model="editedDescription"
              label="Опишите себя и свой опыт"
              rows="4"
              auto-grow
              hide-details
              class="mb-2"
            ></v-textarea>
            <div class="d-flex justify-end">
              <v-btn
                color="error"
                variant="text"
                class="mr-2"
                @click="cancelEditing"
              >
                Отмена
              </v-btn>
              <v-btn
                color="primary"
                type="submit"
                :loading="isSaving"
              >
                Сохранить
              </v-btn>
            </div>
          </v-form>
        </v-card>
      </template>

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
            <v-col cols="12" md="4">
              <v-btn block color="primary" @click="goToMyProjects" prepend-icon="mdi-folder-multiple-outline">
                Мои проекты
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="primary" @click="goToCreateProject" prepend-icon="mdi-plus-box-outline">
                Создать проект
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="blue-grey" @click="goToMyOrders" prepend-icon="mdi-briefcase">
                Заказы
              </v-btn>
            </v-col>
          </template>

          <!-- Администратор -->
          <template v-if="user.roles.includes('admin')">
            <v-col cols="12" md="4">
              <v-btn block color="purple-darken-2" @click="goToLanguages" prepend-icon="mdi-code-braces">
                Языки программирования
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="indigo-darken-2" @click="goToDirections" prepend-icon="mdi-arrow-decision">
                Направления
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="blue-darken-2" @click="goToTechnologies" prepend-icon="mdi-cog">
                Технологии
              </v-btn>
            </v-col>
            <v-col cols="12" md="4">
              <v-btn block color="cyan-darken-2" @click="goToReports" prepend-icon="mdi-chart-bar">
                Отчеты
              </v-btn>
            </v-col>
          </template>
        </template>
      </v-row>

      <!-- Переместили кнопку "Выйти" вниз -->
      <v-divider class="my-6" />
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const user = ref({})
const router = useRouter()
const contractorDescription = ref('')
const isEditing = ref(false)
const editedDescription = ref('')
const isSaving = ref(false)

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

onMounted(async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    const [userRes, contractorRes] = await Promise.all([
      axios.get('/api/profile', {
        headers: { Authorization: `Bearer ${token}` }
      }),
      axios.get('/api/contractors/me', {
        headers: { Authorization: `Bearer ${token}` }
      }).catch(err => {
        console.error('Ошибка при загрузке данных исполнителя:', err)
        return { data: { description: '' } }
      })
    ])

    user.value = userRes.data
    if (userRes.data.roles.includes('contractor')) {
      contractorDescription.value = contractorRes.data.description || ''
    }
  } catch (err) {
    console.error('Ошибка при загрузке данных:', err)
    if (err.response?.status === 401) {
      router.push('/login')
    }
  }
})

const startEditing = () => {
  editedDescription.value = contractorDescription.value
  isEditing.value = true
}

const cancelEditing = () => {
  isEditing.value = false
  editedDescription.value = ''
}

const saveDescription = async () => {
  try {
    isSaving.value = true
    const token = localStorage.getItem('token')

    await axios.put('/api/contractors/me', {
      text: editedDescription.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    contractorDescription.value = editedDescription.value
    isEditing.value = false
    showNotification('Описание успешно обновлено')
  } catch (err) {
    console.error('Ошибка при сохранении описания:', err)
    showNotification('Ошибка при сохранении описания', 'error')
  } finally {
    isSaving.value = false
  }
}

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
const goToMyProjects = () => router.push('/contractor/projects')
const goToCreateProject = () => router.push('/contractor/create-project')

// Новые функции для администратора
const goToLanguages = () => router.push('/admin/languages')
const goToDirections = () => router.push('/admin/directions')
const goToTechnologies = () => router.push('/admin/technologies')
const goToReports = () => router.push('/admin/reports')
</script>


