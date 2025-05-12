<template>
  <v-container class="d-flex align-center justify-center fill-height">
    <v-card class="pa-6" max-width="400" elevation="12">
      <v-card-title class="text-h5 text-center mb-4">Вход в систему</v-card-title>

      <v-form @submit.prevent="login">
        <v-text-field
          v-model="email"
          label="Имя пользователя"
          required
        ></v-text-field>

        <v-text-field
          v-model="password"
          label="Пароль"
          type="password"
          required
        ></v-text-field>

        <v-btn type="submit" color="primary" class="mt-4" block>Войти</v-btn>

        <v-btn variant="text" class="mt-2" block to="/register">Регистрация</v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const router = useRouter()

const login = async () => {
  try {
    const response = await axios.post('/api/login_check', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', response.data.token)
    localStorage.setItem('name', response.data.name)
    localStorage.setItem('email', response.data.email)
    router.push('/dashboard')
  } catch (err) {
    alert('Ошибка входа. Проверьте данные.')
  }
}
</script>
