<template>
  <v-container class="d-flex align-center justify-center fill-height">
    <v-card class="pa-6" max-width="400" elevation="12">
      <v-card-title class="text-h5 text-center mb-4">Регистрация</v-card-title>

      <v-form @submit.prevent="register">
        <v-text-field v-model="usrName" label="Имя" required />
        <v-text-field v-model="usrSurname" label="Фамилия" required />
        <v-text-field v-model="usrPatronymic" label="Отчество" required />
        <v-text-field v-model="email" label="Email" type="email" required />
        <v-text-field v-model="password" label="Пароль" type="password" required />

        <v-btn type="submit" color="success" class="mt-4" block>Зарегистрироваться</v-btn>
        <v-btn variant="text" class="mt-2" block to="/login">Уже есть аккаунт?</v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const usrName = ref('')
const usrSurname = ref('')
const usrPatronymic = ref('')
const email = ref('')
const password = ref('')
const router = useRouter()

const register = async () => {
  try {
    await axios.post('/api/users', {
      usr_name: usrName.value,
      usr_surname: usrSurname.value,
      usr_patronymic: usrPatronymic.value,
      email: email.value,
      password: password.value,
    })
    router.push('/login')
  } catch (err) {
    alert('Ошибка регистрации')
  }
}
</script>
