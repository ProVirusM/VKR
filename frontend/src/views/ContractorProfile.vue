<template>
  <v-container class="py-10">
    <v-row justify="center">
      <v-col cols="12" md="10" lg="8">
        <!-- Профиль -->
        <v-card class="profile-card mb-8" elevation="8">
          <v-row no-gutters align="center">
            <v-col cols="auto" class="d-flex flex-column align-center px-8 py-8 profile-avatar-col">
              <v-avatar size="110" color="primary" class="mb-4 elevation-6">
                <span class="text-h3 white--text">
                  {{ contractor?.user?.name?.charAt(0) || '?' }}
                </span>
              </v-avatar>
              <v-chip color="primary" class="mt-2" prepend-icon="mdi-account-badge">Исполнитель</v-chip>
            </v-col>
            <v-col>
              <v-card-title class="text-h4 font-weight-bold mb-2">
                {{ contractor?.user?.surname }} {{ contractor?.user?.name }} {{ contractor?.user?.patronymic }}
              </v-card-title>
              <v-card-subtitle class="mb-2">
                <v-icon size="20" color="grey-darken-1" class="mr-1">mdi-email</v-icon>
                <span class="text-grey-darken-1">{{ contractor?.user?.email }}</span>
              </v-card-subtitle>
              <v-card-text class="text-body-1 mt-2">
                {{ contractor?.description || 'Нет описания' }}
              </v-card-text>
            </v-col>
          </v-row>
        </v-card>

        <!-- Проекты -->
        <v-card class="mb-8 pa-6" elevation="4">
          <div class="d-flex align-center mb-4">
            <v-icon color="indigo" class="mr-2">mdi-github</v-icon>
            <h3 class="text-h5 font-weight-bold mb-0">Проекты</h3>
          </div>
          <v-row v-if="contractor?.projects?.length" class="project-list">
            <v-col v-for="project in contractor.projects" :key="project.id" cols="12" md="6">
              <v-card class="mb-4 project-card" elevation="2" @click="goToProject(project.id)" style="cursor: pointer">
                <v-card-title class="font-weight-bold text-primary">{{ project.name }}</v-card-title>
                <v-card-subtitle>
                  <v-icon size="18" color="grey-darken-1" class="mr-1">mdi-link-variant</v-icon>
                  <a :href="project.repository" target="_blank" @click.stop>{{ project.repository }}</a>
                </v-card-subtitle>
                <v-card-text>{{ project.text }}</v-card-text>
                <v-row v-if="project.photos && project.photos.length" class="mt-2">
                  <v-col cols="12">
                    <v-carousel
                      hide-delimiter-background
                      show-arrows="hover"
                      height="200"
                      @click.stop
                    >
                      <v-carousel-item
                        v-for="photo in project.photos"
                        :key="photo.id"
                        :src="photo.link"
                        cover
                      ></v-carousel-item>
                    </v-carousel>
                  </v-col>
                </v-row>
              </v-card>
            </v-col>
          </v-row>
          <div v-else class="text-grey">Нет проектов</div>
        </v-card>

        <!-- Заказы -->
        <v-card class="mb-8 pa-6" elevation="4">
          <div class="d-flex align-center mb-4">
            <v-icon color="teal-darken-2" class="mr-2">mdi-briefcase-outline</v-icon>
            <h3 class="text-h5 font-weight-bold mb-0">Заказы</h3>
          </div>
          <v-table v-if="contractor?.orders?.length" class="orders-table">
            <thead>
              <tr>
                <th>Название</th>
                <th>Статус</th>
                <th>Цена</th>
                <th>Срок</th>
                <th>Статус участия</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in contractor.orders" :key="order.id" @click="goToOrder(order.id)" style="cursor: pointer">
                <td>{{ order.title }}</td>
                <td>
                  <v-chip :color="order.status === 'Завершен' ? 'success' : order.status === 'В работе' ? 'primary' : 'grey'" size="small">
                    {{ order.status }}
                  </v-chip>
                </td>
                <td>{{ order.price }}</td>
                <td>{{ order.time }}</td>
                <td>{{ order.order_contractor_status }}</td>
              </tr>
            </tbody>
          </v-table>
          <div v-else class="text-grey">Нет заказов</div>
        </v-card>

        <!-- Отзывы -->
        <v-card class="pa-6" elevation="4">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="d-flex align-center">
              <v-icon color="amber-darken-2" class="mr-2">mdi-star-outline</v-icon>
              <h3 class="text-h5 font-weight-bold mb-0">Отзывы</h3>
            </div>
            <v-select
              v-model="feedbackSort"
              :items="feedbackSortOptions"
              label="Сортировка"
              density="compact"
              variant="outlined"
              class="feedback-sort"
              hide-details
            ></v-select>
          </div>
          <v-row v-if="sortedFeedbacks.length">
            <v-col v-for="feedback in sortedFeedbacks" :key="feedback.id" cols="12">
              <v-alert :type="feedback.estimation >= 4 ? 'success' : feedback.estimation >= 3 ? 'warning' : 'error'" class="mb-2">
                <div class="font-weight-bold mb-1">
                  <v-icon left size="18" color="amber">mdi-star</v-icon>
                  Оценка: {{ feedback.estimation }}
                </div>
                <div>{{ feedback.text }}</div>
                <div class="text-caption mt-1">{{ feedback.timestamp }}</div>
              </v-alert>
            </v-col>
          </v-row>
          <div v-else class="text-grey">Нет отзывов</div>
        </v-card>
      </v-col>
    </v-row>
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false">Закрыть</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const contractor = ref(null)
const snackbar = ref({ show: false, text: '', color: 'success' })
const feedbackSort = ref('highToLow')

const feedbackSortOptions = [
  { title: 'Сначала с высокой оценкой', value: 'highToLow' },
  { title: 'Сначала с низкой оценкой', value: 'lowToHigh' },
  { title: 'По дате (сначала новые)', value: 'newToOld' },
  { title: 'По дате (сначала старые)', value: 'oldToNew' }
]

const sortedFeedbacks = computed(() => {
  if (!contractor.value?.feedbacks) return []
  
  const feedbacks = [...contractor.value.feedbacks]
  
  switch (feedbackSort.value) {
    case 'highToLow':
      return feedbacks.sort((a, b) => b.estimation - a.estimation)
    case 'lowToHigh':
      return feedbacks.sort((a, b) => a.estimation - b.estimation)
    case 'newToOld':
      return feedbacks.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
    case 'oldToNew':
      return feedbacks.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp))
    default:
      return feedbacks
  }
})

const loadContractor = async () => {
  try {
    const res = await axios.get(`/api/contractors/${route.params.id}/full-profile`)
    contractor.value = res.data
  } catch (e) {
    snackbar.value = { show: true, text: 'Ошибка загрузки профиля', color: 'error' }
  }
}

const goToProject = (projectId) => {
  router.push(`/projects/${projectId}`)
}

const goToOrder = (orderId) => {
  router.push(`/contractor/orders/${orderId}`)
}

onMounted(loadContractor)
</script>

<style scoped>
.profile-card {
  background: linear-gradient(120deg, #e3f2fd 0%, #f8fafc 100%);
  border-radius: 24px;
}
.profile-avatar-col {
  min-width: 160px;
}
.project-card {
  border-radius: 18px;
}
.orders-table th, .orders-table td {
  padding: 8px 12px;
}
.feedback-sort {
  max-width: 250px;
}
</style>
