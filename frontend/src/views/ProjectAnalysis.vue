<template>
  <v-container class="py-10">
    <v-card v-if="analysis" class="mx-auto pa-6" max-width="1200" elevation="10" rounded="2xl">
      <v-card-title class="d-flex justify-space-between align-center mb-4">
        <span class="text-h5 font-weight-bold">Анализ репозитория</span>
        <v-btn color="grey" variant="outlined" @click="$router.back()">Назад</v-btn>
      </v-card-title>

      <!-- Основная информация о репозитории -->
      <v-card class="mb-6 pa-4" elevation="2">
        <v-row>
          <v-col cols="12" md="6">
            <h3 class="text-h6 mb-4">Информация о репозитории</h3>
            <v-list>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-star</v-icon>
                </template>
                <v-list-item-title>Звезды: {{ analysis.repository.stars }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-source-fork</v-icon>
                </template>
                <v-list-item-title>Форки: {{ analysis.repository.forks }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-alert-circle</v-icon>
                </template>
                <v-list-item-title>Открытые issues: {{ analysis.repository.open_issues }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon color="primary">mdi-clock-outline</v-icon>
                </template>
                <v-list-item-title>Последнее обновление: {{ formatDate(analysis.repository.last_update) }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>
          <v-col cols="12" md="6">
            <h3 class="text-h6 mb-4">Технологии</h3>
            <v-list>
              <v-list-item v-for="(value, key) in foundTechnologies" :key="key">
                <template v-slot:prepend>
                  <v-icon color="success">mdi-check-circle</v-icon>
                </template>
                <v-list-item-title>{{ formatTechnologyName(key) }}: {{ value }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
      </v-card>

      <!-- График языков программирования -->
      <v-card class="mb-6 pa-4" elevation="2">
        <h3 class="text-h6 mb-4">Распределение языков программирования</h3>
        <v-chart class="chart" :option="languagesChartOption" autoresize />
      </v-card>

      <!-- CI/CD статус -->
      <v-card class="pa-4" elevation="2">
        <h3 class="text-h6 mb-4">CI/CD статус</h3>
        <v-alert
          :type="analysis.ci_cd === 'Enabled' ? 'success' : 'warning'"
          :icon="analysis.ci_cd === 'Enabled' ? 'mdi-check-circle' : 'mdi-alert'"
          class="mb-0"
        >
          {{ analysis.ci_cd === 'Enabled' ? 'CI/CD настроен' : 'CI/CD не настроен' }}
        </v-alert>
      </v-card>
    </v-card>

    <div v-else class="text-center mt-10">
      <v-progress-circular indeterminate color="primary" size="50" />
      <p class="mt-3">Загрузка анализа...</p>
    </div>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { PieChart } from 'echarts/charts'
import { TitleComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import VChart from 'vue-echarts'

use([CanvasRenderer, PieChart, TitleComponent, TooltipComponent, LegendComponent])

const route = useRoute()
const router = useRouter()
const analysis = ref(null)
const isLoading = ref(true)
const error = ref(null)
const project = ref(null)

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
  const projectId = route.params.id
  isLoading.value = true
  error.value = null
  
  try {
    // Сначала получаем информацию о проекте
    const projectResponse = await axios.get(`/api/projects/${projectId}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    
    project.value = projectResponse.data

    if (!project.value?.repository) {
      error.value = 'Для анализа необходим репозиторий GitHub'
      return
    }
    
    // Извлекаем owner и repo из URL репозитория
    const repoUrl = project.value.repository
    const match = repoUrl.match(/github\.com\/([^\/]+)\/([^\/]+)/)
    
    if (!match) {
      error.value = 'Некорректная ссылка на GitHub репозиторий'
      return
    }
    
    const [_, owner, repo] = match
    
    try {
      // Получаем анализ репозитория
      const analysisResponse = await axios.get(`/api/repo/${owner}/${repo}`, {
        headers: { Authorization: `Bearer ${token.value}` }
      })
      
      if (analysisResponse.data.error) {
        router.push({
          path: `/projects/${projectId}`,
          query: { error: 'Некорректная ссылка на GitHub репозиторий' }
        })
        return
      }
      
      analysis.value = analysisResponse.data
    } catch (analysisError) {
      console.error('Ошибка анализа:', analysisError)
      router.push({
        path: `/projects/${projectId}`,
        query: { error: 'Некорректная ссылка на GitHub репозиторий' }
      })
      return
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

const languagesChartOption = computed(() => ({
  tooltip: {
    trigger: 'item',
    formatter: '{b}: {c}%'
  },
  legend: {
    orient: 'vertical',
    left: 'left'
  },
  series: [
    {
      type: 'pie',
      radius: '50%',
      data: Object.entries(analysis.value?.languages || {}).map(([name, value]) => ({
        name,
        value
      })),
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: 'rgba(0, 0, 0, 0.5)'
        }
      }
    }
  ]
}))

const foundTechnologies = computed(() => {
  if (!analysis.value?.technologies) return {};
  return Object.entries(analysis.value.technologies)
    .filter(([_, value]) => value !== 'Not Found')
    .reduce((acc, [key, value]) => {
      acc[key] = value;
      return acc;
    }, {});
});

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatTechnologyName = (name) => {
  const names = {
    'symfony': 'Symfony',
    'nodejs': 'Node.js',
    'docker': 'Docker',
    'php': 'PHP',
    'composer': 'Composer',
    'npm': 'NPM',
    'yarn': 'Yarn',
    'webpack': 'Webpack',
    'vue': 'Vue.js',
    'react': 'React',
    'angular': 'Angular',
    'typescript': 'TypeScript',
    'python': 'Python',
    'java': 'Java',
    'gradle': 'Gradle',
    'maven': 'Maven'
  };
  return names[name] || name;
}
</script>

<style scoped>
.chart {
  height: 400px;
}
</style> 