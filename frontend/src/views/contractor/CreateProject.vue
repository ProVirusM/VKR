<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h4">Создать проект</v-card-title>

      <v-card-text>
        <v-form @submit.prevent="createProject" ref="form">
          <v-text-field
            v-model="project.title"
            label="Название проекта"
            :rules="[v => !!v || 'Название обязательно']"
            required
          ></v-text-field>

          <v-textarea
            v-model="project.description"
            label="Описание проекта"
            rows="4"
          ></v-textarea>

          <v-text-field
            v-model="project.github_link"
            label="Ссылка на GitHub репозиторий"
            :rules="[
              v => !!v || 'Ссылка обязательна',
              v => /^https:\/\/github\.com\/.+/.test(v) || 'Некорректная ссылка на GitHub'
            ]"
            required
          ></v-text-field>

          <v-file-input
            v-model="photos"
            label="Фотографии проекта"
            multiple
            accept="image/*"
            :rules="[v => v.length <= 5 || 'Максимум 5 фотографий']"
            prepend-icon="mdi-camera"
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
        <v-btn color="primary" @click="createProject" :loading="loading">
          Создать проект
        </v-btn>
        <v-btn variant="text" @click="goBack">Отмена</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = ref(null)
const loading = ref(false)
const error = ref('')
const photos = ref([])

const project = ref({
  title: '',
  description: '',
  github_link: ''
})

const createProject = async () => {
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

    // Создаем проект
    const projectResponse = await axios.post('/api/contractor/projects', project.value, {
      headers: { Authorization: `Bearer ${token}` }
    })

    const projectId = projectResponse.data.id

    // Загружаем фотографии
    for (const photo of photos.value) {
      const formData = new FormData()
      formData.append('photo', photo)

      await axios.post(`/api/project-photos/upload/${projectId}`, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      })
    }

    router.push('/contractor/projects')
  } catch (err) {
    error.value = err.response?.data?.error || 'Произошла ошибка при создании проекта'
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.back()
}
</script> 