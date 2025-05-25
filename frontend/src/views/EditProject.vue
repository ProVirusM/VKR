<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h4">Редактировать проект</v-card-title>

      <v-card-text>
        <v-form @submit.prevent="updateProject" ref="form">
          <v-text-field
            v-model="project.name"
            label="Название проекта"
            :rules="[v => !!v || 'Название обязательно']"
            required
          ></v-text-field>

          <v-textarea
            v-model="project.text"
            label="Описание проекта"
            rows="4"
          ></v-textarea>

          <v-text-field
            v-model="project.repository"
            label="Ссылка на GitHub репозиторий"
            :rules="[
              v => !v || /^https:\/\/github\.com\/.+/.test(v) || 'Некорректная ссылка на GitHub'
            ]"
            hint="Необязательное поле"
            persistent-hint
          ></v-text-field>

          <div v-if="project.photos && project.photos.length" class="mb-4">
            <h3 class="text-h6 mb-2">Текущие фотографии</h3>
            <v-row>
              <v-col v-for="photo in project.photos" :key="photo.id" cols="6" md="4">
                <v-card>
                  <v-img :src="photo.link" aspect-ratio="1" cover />
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                      color="error"
                      variant="text"
                      @click="deletePhoto(photo.id)"
                      :loading="isDeletingPhoto === photo.id"
                    >
                      Удалить
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <v-file-input
            v-model="newPhotos"
            label="Добавить фотографии"
            multiple
            accept="image/*"
            :rules="[
              v => v.length <= 10 || 'Максимум 10 фотографий',
              v => !v.some(file => file.size > 10 * 1024 * 1024) || 'Каждое изображение не должно превышать 10 МБ'
            ]"
            prepend-icon="mdi-camera"
            hint="Максимум 10 фотографий, каждая до 10 МБ"
            persistent-hint
          ></v-file-input>

          <v-alert
            v-if="error"
            type="error"
            class="mt-4"
          >
            {{ error }}
          </v-alert>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-btn color="primary" @click="updateProject" :loading="loading">
          Сохранить изменения
        </v-btn>
        <v-btn variant="text" @click="goBack">Отмена</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const form = ref(null)
const loading = ref(false)
const error = ref('')
const newPhotos = ref([])
const isDeletingPhoto = ref(null)

const project = ref({
  name: '',
  text: '',
  repository: '',
  photos: []
})

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await axios.get(`/api/projects/${route.params.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    project.value = response.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка при загрузке проекта'
    console.error('Ошибка:', err)
  }
})

const updateProject = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) return

  loading.value = true
  error.value = ''

  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    // Обновляем проект
    await axios.put(`/api/projects/${route.params.id}`, {
      name: project.value.name,
      text: project.value.text,
      repository: project.value.repository
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    // Загружаем новые фотографии
    for (const photo of newPhotos.value) {
      const formData = new FormData()
      formData.append('photo', photo)

      await axios.post(`/api/project-photos/upload/${route.params.id}`, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      })
    }

    router.push(`/projects/${route.params.id}`)
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка при обновлении проекта'
    console.error('Ошибка:', err)
  } finally {
    loading.value = false
  }
}

const deletePhoto = async (photoId) => {
  isDeletingPhoto.value = photoId
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`/api/project-photos/${photoId}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    project.value.photos = project.value.photos.filter(p => p.id !== photoId)
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка при удалении фотографии'
    console.error('Ошибка:', err)
  } finally {
    isDeletingPhoto.value = null
  }
}

const goBack = () => {
  router.back()
}
</script> 