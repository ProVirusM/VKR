<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="d-flex justify-space-between align-center">
        <span class="text-h4">Мои проекты</span>
        <v-btn color="primary" @click="goToCreateProject">Создать проект</v-btn>
      </v-card-title>

      <v-card-text>
        <v-row v-if="projects.length">
          <v-col v-for="project in projects" :key="project.id" cols="12" md="6">
            <v-card variant="outlined" class="h-100">
              <v-img
                v-if="project.photos && project.photos.length"
                :src="project.photos[0].link"
                height="200"
                cover
              ></v-img>
              <v-card-title>{{ project.title }}</v-card-title>
              <v-card-text>
                <p class="text-body-1">{{ project.description }}</p>
                <v-chip
                  class="mt-2"
                  color="primary"
                  variant="outlined"
                  :href="project.github_link"
                  target="_blank"
                >
                  <v-icon start>mdi-github</v-icon>
                  GitHub
                </v-chip>
              </v-card-text>
              <v-card-actions>
                <v-btn variant="text" @click="viewProject(project.id)">
                  Подробнее
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>

        <v-alert
          v-else
          type="info"
          variant="tonal"
          class="mt-4"
        >
          У вас пока нет проектов
        </v-alert>
      </v-card-text>

      <v-card-actions>
        <v-btn variant="text" @click="goBack">Назад</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const projects = ref([])

const loadProjects = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    const response = await axios.get('/api/contractor/projects', {
      headers: { Authorization: `Bearer ${token}` }
    })

    projects.value = response.data
  } catch (error) {
    console.error('Ошибка при загрузке проектов:', error)
  }
}

const viewProject = (projectId) => {
  router.push(`/contractor/projects/${projectId}`)
}

const goToCreateProject = () => {
  router.push('/contractor/create-project')
}

const goBack = () => {
  router.back()
}

onMounted(() => {
  loadProjects()
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