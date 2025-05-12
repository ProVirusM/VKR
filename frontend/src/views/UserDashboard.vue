<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h5">Личный кабинет</v-card-title>
      <v-card-text>
        Добро пожаловать, <strong>{{ user.name }}</strong>!
      </v-card-text>

      <v-btn color="error" @click="logout">Выйти</v-btn>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const user = ref({})
const router = useRouter()

onMounted(async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/profile', {
      headers: { Authorization: `Bearer ${token}` },
    })
    user.value = response.data
  } catch {
    router.push('/login')
  }
})

const logout = () => {
  localStorage.removeItem('token')
  router.push('/login')
}
</script>
