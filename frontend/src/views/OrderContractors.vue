<template>
  <v-container class="pa-8">
    <v-row justify="center" class="mb-8">
      <v-col cols="12" class="text-center">
        <h1 class="text-h3 font-weight-bold mb-2">
          Откликнувшиеся исполнители
        </h1>
        <v-divider class="mx-auto" style="max-width: 200px" />
      </v-col>
    </v-row>

    <div v-if="contractors.length" class="contractors-list">
      <v-row>
        <v-col v-for="contractor in contractors" :key="contractor.contractorId" cols="12" class="mb-6">
          <v-card
            class="contractor-card h-100"
            elevation="4"
            rounded="xl"
            :loading="loading"
          >
            <v-card-item>
              <template v-slot:prepend>
                <v-avatar
                  size="80"
                  class="mr-4"
                  color="primary"
                  elevation="4"
                >
                  <span class="text-h4 white--text">
                    {{ contractor.userName ? contractor.userName.charAt(0).toUpperCase() : '' }}
                  </span>
                </v-avatar>
              </template>

              <v-card-title class="text-h5 font-weight-bold">
                {{ contractor.userSurname }} {{ contractor.userName }} {{ contractor.userPatronymic }}
              </v-card-title>
            </v-card-item>

            <v-card-text>
              <v-chip
                color="primary"
                variant="outlined"
                class="mb-2"
                prepend-icon="mdi-account-check"
              >
                Исполнитель
              </v-chip>
            </v-card-text>

            <v-divider class="mx-4" />

            <v-card-actions class="pa-4">
              <v-row>
                <v-col cols="12">
                  <v-btn
                    block
                    color="success"
                    size="large"
                    variant="elevated"
                    class="mb-2"
                    prepend-icon="mdi-check-circle"
                    @click="approveContractor(contractor.contractorId)"
                  >
                    Одобрить
                  </v-btn>
                </v-col>
                <v-col cols="12">
                  <v-btn
                    block
                    color="primary"
                    size="large"
                    variant="outlined"
                    prepend-icon="mdi-account-circle"
                    @click="goToProfile(contractor.contractorId)"
                  >
                    Профиль
                  </v-btn>
                </v-col>
              </v-row>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <v-row v-else justify="center" class="mt-16">
      <v-col cols="12" md="8" lg="6" class="text-center">
        <v-card class="pa-8" variant="outlined">
          <v-icon
            size="64"
            color="grey"
            class="mb-4"
          >
            mdi-account-group-outline
          </v-icon>
          <h2 class="text-h4 font-weight-bold mb-2 text-grey">
            Нет откликнувшихся исполнителей
          </h2>
          <p class="text-body-1 text-grey">
            Пока никто не откликнулся на ваш заказ. Пожалуйста, подождите.
          </p>
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
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'

const contractors = ref([])
const router = useRouter()
const route = useRoute()
const orderId = route.params.id
const loading = ref(false)

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

const approveContractor = async (contractorId) => {
  loading.value = true
  const token = localStorage.getItem('token')
  try {
    const response = await axios.post(`/api/orders/${orderId}/approve-contractor/${contractorId}`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    if (response.data.message) {
      showNotification(response.data.message, 'success')
    } else {
      showNotification('Исполнитель успешно одобрен!', 'success')
    }
    
    // Перенаправляем на страницу завершенных заказов
    router.push('/customer/completed-orders')
  } catch (error) {
    console.error('Ошибка при одобрении исполнителя', error)
    const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Не удалось одобрить исполнителя'
    showNotification(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

const goToProfile = (contractorId) => {
  router.push(`/contractor/${contractorId}`)
}

const loadContractors = async () => {
  loading.value = true
  const token = localStorage.getItem('token')
  try {
    const res = await axios.get(`/api/orders/${orderId}/contractors`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    contractors.value = res.data
  } catch (error) {
    console.error('Ошибка при загрузке исполнителей', error)
    showNotification('Не удалось загрузить список исполнителей', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadContractors()
})
</script>

<style scoped>
.contractors-list {
  margin-top: 2rem;
}

.contractor-card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.contractor-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
}

.v-card-title {
  word-break: break-word;
  line-height: 1.4;
}
</style>
