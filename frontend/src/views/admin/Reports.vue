<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h5 mb-4 d-flex justify-space-between align-center">
        <span>Отчеты</span>
      </v-card-title>

      <!-- Кнопки отчетов -->
      <v-row class="mb-4">
        <v-col cols="12" sm="6" md="6">
          <v-btn color="primary" @click="generatePDF('contractors')" block>
            <v-icon start>mdi-file-pdf-box</v-icon>
            Отчет по исполнителям
          </v-btn>
        </v-col>
        <v-col cols="12" sm="6" md="6">
          <v-btn color="primary" @click="generatePDF('active-orders')" block>
            <v-icon start>mdi-file-pdf-box</v-icon>
            Отчет по активным заказам
          </v-btn>
        </v-col>
        <v-col cols="12" sm="6" md="6">
          <v-btn color="primary" @click="generatePDF('completed-orders')" block>
            <v-icon start>mdi-file-pdf-box</v-icon>
            Отчет по выполненным заказам
          </v-btn>
        </v-col>
        <v-col cols="12" sm="6" md="6">
          <v-btn color="primary" @click="generatePDF('customers')" block>
            <v-icon start>mdi-file-pdf-box</v-icon>
            Отчет по заказчикам
          </v-btn>
        </v-col>
      </v-row>

      <!-- Общая статистика -->
      <v-row class="mb-6">
        <v-col cols="12" md="4">
          <v-card color="primary" class="pa-4">
            <v-card-title class="text-h6 text-white">Заказы</v-card-title>
            <v-card-text class="text-white">
              <div class="text-h4">{{ stats.orders?.total || 0 }}</div>
              <div>Всего заказов</div>
              <div class="mt-2">
                <div>Активных: {{ stats.orders?.active || 0 }}</div>
                <div>Завершенных: {{ stats.orders?.completed || 0 }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card color="success" class="pa-4">
            <v-card-title class="text-h6 text-white">Проекты</v-card-title>
            <v-card-text class="text-white">
              <div class="text-h4">{{ stats.projects?.total || 0 }}</div>
              <div>Всего проектов</div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card color="info" class="pa-4">
            <v-card-title class="text-h6 text-white">Пользователи</v-card-title>
            <v-card-text class="text-white">
              <div class="text-h4">{{ stats.users?.total || 0 }}</div>
              <div>Всего пользователей</div>
              <div class="mt-2">
                <div>Подрядчиков: {{ stats.users?.contractors || 0 }}</div>
                <div>Заказчиков: {{ stats.users?.customers || 0 }}</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Графики -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card class="pa-4">
            <v-card-title>Заказы по статусам</v-card-title>
            <v-card-text>
              <canvas ref="ordersChart"></canvas>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card class="pa-4">
            <v-card-title>Проекты по направлениям</v-card-title>
            <v-card-text>
              <canvas ref="projectsChart"></canvas>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'

const stats = ref({})
const ordersChart = ref(null)
const projectsChart = ref(null)

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/reports/dashboard')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const fetchOrdersByStatus = async () => {
  try {
    const response = await axios.get('/api/reports/orders-by-status')
    const data = response.data

    new Chart(ordersChart.value, {
      type: 'pie',
      data: {
        labels: Object.keys(data),
        datasets: [{
          data: Object.values(data),
          backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    })
  } catch (error) {
    console.error('Error fetching orders by status:', error)
  }
}

const fetchProjectsByDirection = async () => {
  try {
    const response = await axios.get('/api/reports/projects-by-direction')
    const data = response.data

    new Chart(projectsChart.value, {
      type: 'bar',
      data: {
        labels: Object.keys(data),
        datasets: [{
          label: 'Количество проектов',
          data: Object.values(data),
          backgroundColor: '#36A2EB'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    })
  } catch (error) {
    console.error('Error fetching projects by direction:', error)
  }
}

const generatePDF = async (type) => {
  try {
    const response = await axios.get(`/api/reports/generate-pdf/${type}`, {
      responseType: 'blob'
    })
    
    // Создаем ссылку для скачивания
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `${type}_report.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error generating PDF:', error)
  }
}

onMounted(() => {
  fetchStats()
  fetchOrdersByStatus()
  fetchProjectsByDirection()
})
</script>
