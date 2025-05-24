<template>
  <v-container class="py-10">
    <v-row justify="center">
      <v-col cols="12" md="8">
        <v-card class="pa-6" elevation="4">
          <v-card-title class="text-h4 mb-4">
            Оставить отзыв исполнителю
          </v-card-title>

          <v-card-text>
            <v-form @submit.prevent="submitFeedback" ref="form">
              <!-- Оценка -->
              <v-rating
                v-model="feedback.estimation"
                color="amber"
                density="comfortable"
                hover
                size="large"
                class="mb-4"
              ></v-rating>

              <!-- Текст отзыва -->
              <v-textarea
                v-model="feedback.text"
                label="Ваш отзыв"
                rows="4"
                :rules="[v => !!v || 'Обязательное поле']"
                required
              ></v-textarea>

              <!-- Кнопки -->
              <div class="d-flex justify-space-between mt-6">
                <v-btn
                  color="grey"
                  variant="outlined"
                  @click="$router.back()"
                >
                  Назад
                </v-btn>

                <v-btn
                  color="primary"
                  type="submit"
                  :loading="loading"
                  :disabled="!feedback.estimation || !feedback.text"
                >
                  Отправить отзыв
                </v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Snackbar для уведомлений -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
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
const form = ref(null)
const loading = ref(false)

const feedback = ref({
  text: '',
  estimation: 0
})

const snackbar = ref({
  show: false,
  text: '',
  color: 'success'
})

const showNotification = (text, color = 'success') => {
  snackbar.value = {
    show: true,
    text,
    color
  }
}

const submitFeedback = async () => {
  if (!form.value.validate()) return

  loading.value = true
  const token = localStorage.getItem('token')

  try {
    // Получаем информацию о заказе и утвержденном исполнителе
    const orderRes = await axios.get(`/api/orders/${route.params.id}/approved-contractor`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const approvedContractor = orderRes.data

    // Получаем информацию о текущем пользователе
    const userRes = await axios.get('/api/profile', {
      headers: { Authorization: `Bearer ${token}` }
    })

    const user = userRes.data

    // Отправляем отзыв
    await axios.post('/api/feedbacks/', {
      text: feedback.value.text,
      estimation: feedback.value.estimation,
      contractor_id: approvedContractor.contractorId,
      customer_id: user.id
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    showNotification('Отзыв успешно отправлен!')

    // Перенаправляем на профиль исполнителя
    router.push(`/contractor/${approvedContractor.contractorId}`)
  } catch (error) {
    console.error('Ошибка при отправке отзыва:', error)
    showNotification(
      error.response?.data?.message || 'Произошла ошибка при отправке отзыва',
      'error'
    )
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.v-rating {
  display: flex;
  justify-content: center;
}
</style>
