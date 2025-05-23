<template>
  <v-container class="py-10">
    <v-card
      v-if="project"
      class="mx-auto pa-6"
      max-width="800"
      elevation="10"
      rounded="2xl"
    >
      <v-card-title class="d-flex justify-space-between align-center mb-4">
        <span class="text-h5 font-weight-bold">{{ project.name }}</span>
        <div>
          <v-btn
            color="primary"
            class="me-2"
            :href="project.repository"
            target="_blank"
            prepend-icon="mdi-github"
          >
            Репозиторий
          </v-btn>
          <v-btn
            color="info"
            @click="$router.push(`/projects/${project.id}/analysis`)"
            prepend-icon="mdi-chart-bar"
          >
            Анализ
          </v-btn>
        </div>
      </v-card-title>

      <v-card-text>
        <div class="mb-4">
          <v-icon class="mr-1" color="blue">mdi-link-variant</v-icon>
          <strong>Репозиторий:</strong>
          <a :href="project.repository" target="_blank" class="ml-2 text-decoration-none">
            {{ project.repository }}
          </a>
        </div>

        <p class="mb-3 text-body-1">
          <strong>Описание:</strong><br /> {{ project.text }}
        </p>

        <v-divider class="my-4" />

        <div v-if="project.photos && project.photos.length" class="mb-4">
          <strong>Фотографии:</strong>
          <v-row class="mt-2">
            <v-col v-for="photo in project.photos" :key="photo.id" cols="6" md="4">
              <v-img
                :src="photo.link"
                aspect-ratio="1"
                class="rounded-lg"
                cover
                @click="openImage(photo.link)"
                style="cursor: pointer"
              />
            </v-col>
          </v-row>
        </div>
      </v-card-text>

      <v-card-actions class="d-flex justify-space-between">
        <v-btn color="grey" variant="outlined" @click="$router.back()">Назад</v-btn>
      </v-card-actions>
    </v-card>

    <div v-else class="text-center mt-10">
      <v-progress-circular indeterminate color="primary" size="50" />
      <p class="mt-3">Загрузка проекта...</p>
    </div>

    <!-- Диалог для просмотра изображения -->
    <v-dialog v-model="dialog" max-width="90vw">
      <v-card>
        <v-img :src="selectedImage" max-height="90vh" contain />
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="dialog = false">Закрыть</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const project = ref(null)
const isLoading = ref(true)
const error = ref(null)
const dialog = ref(false)
const selectedImage = ref('')

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
    const response = await axios.get(`/api/projects/${id}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })

    project.value = response.data
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

const openImage = (imageUrl) => {
  selectedImage.value = imageUrl
  dialog.value = true
}
</script>

<style scoped>
.v-card {
  transition: transform 0.2s;
}
.v-card:hover {
  transform: translateY(-2px);
}
</style> 