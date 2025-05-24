<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <span class="text-h4">Мои отзывы</span>
        <v-chip color="primary" class="text-h6">
          {{ averageRating.toFixed(1) }} ⭐
        </v-chip>
      </v-card-title>

      <!-- Сортировка -->
      <v-card-text>
        <v-row align="center" class="mb-4">
          <v-col cols="12" sm="4">
            <v-select
              v-model="sortBy"
              :items="sortOptions"
              label="Сортировать по"
              variant="outlined"
              density="comfortable"
            ></v-select>
          </v-col>
          <v-col cols="12" sm="4">
            <v-btn-toggle
              v-model="sortOrder"
              mandatory
              color="primary"
              rounded="lg"
            >
              <v-btn value="asc">
                <v-icon>mdi-sort-ascending</v-icon>
              </v-btn>
              <v-btn value="desc">
                <v-icon>mdi-sort-descending</v-icon>
              </v-btn>
            </v-btn-toggle>
          </v-col>
        </v-row>

        <!-- Список отзывов -->
        <v-row v-if="feedbacks.length">
          <v-col v-for="feedback in sortedFeedbacks" :key="feedback.id" cols="12">
            <v-card variant="outlined" class="pa-4">
              <v-card-text>
                <div class="d-flex justify-space-between align-center mb-2">
                  <div class="d-flex align-center">
                    <v-avatar color="primary" size="40" class="mr-3">
                      <span class="text-h6 white--text">
                        {{ feedback.customer_name ? feedback.customer_name.charAt(0).toUpperCase() : '' }}
                      </span>
                    </v-avatar>
                    <div>
                      <div class="text-subtitle-1 font-weight-bold">
                        {{ feedback.customer_name }} {{ feedback.customer_surname }}
                      </div>
                      <div class="text-caption text-grey">
                        {{ formatDate(feedback.timestamp) }}
                      </div>
                    </div>
                  </div>
                  <v-rating
                    :model-value="feedback.estimation"
                    readonly
                    color="amber"
                    density="comfortable"
                    size="small"
                  ></v-rating>
                </div>
                <p class="text-body-1 mt-2">{{ feedback.text }}</p>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Сообщение, если нет отзывов -->
        <v-alert
          v-else
          type="info"
          variant="tonal"
          class="mt-4"
        >
          У вас пока нет отзывов
        </v-alert>
      </v-card-text>

      <!-- Кнопка Назад -->
      <v-card-actions>
        <v-btn variant="text" @click="goBack">Назад</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const feedbacks = ref([])
const sortBy = ref('date')
const sortOrder = ref('desc')

const sortOptions = [
  { title: 'По дате', value: 'date' },
  { title: 'По оценке', value: 'rating' }
]

// Вычисляемое свойство для сортировки отзывов
const sortedFeedbacks = computed(() => {
  return [...feedbacks.value].sort((a, b) => {
    let comparison = 0
    if (sortBy.value === 'date') {
      comparison = new Date(a.timestamp) - new Date(b.timestamp)
    } else if (sortBy.value === 'rating') {
      comparison = a.estimation - b.estimation
    }
    return sortOrder.value === 'asc' ? comparison : -comparison
  })
})

// Вычисляемое свойство для средней оценки
const averageRating = computed(() => {
  if (!feedbacks.value.length) return 0
  const sum = feedbacks.value.reduce((acc, feedback) => acc + feedback.estimation, 0)
  return sum / feedbacks.value.length
})

// Форматирование даты
const formatDate = (dateString) => {
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }
  return new Date(dateString).toLocaleDateString('ru-RU', options)
}

// Загрузка отзывов
const loadFeedbacks = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    const response = await axios.get('/api/contractor/reviews', {
      headers: { Authorization: `Bearer ${token}` }
    })

    feedbacks.value = response.data
  } catch (error) {
    console.error('Ошибка при загрузке отзывов:', error)
  }
}

// Функция для возврата на предыдущую страницу
const goBack = () => {
  router.back()
}

onMounted(() => {
  loadFeedbacks()
})
</script>

<style scoped>
.v-card {
  transition: transform 0.2s;
}

.v-card:hover {
  transform: translateY(-2px);
}
</style>
