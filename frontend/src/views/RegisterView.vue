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

        <v-radio-group v-model="selectedRole" class="mt-4">
          <template v-slot:label>
            <div class="text-subtitle-1 font-weight-bold">Выберите роль:</div>
          </template>
          <v-radio
            value="customer"
            color="primary"
          >
            <template v-slot:label>
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-2">mdi-account</v-icon>
                Заказчик
              </div>
            </template>
          </v-radio>
          <v-radio
            value="contractor"
            color="primary"
          >
            <template v-slot:label>
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-2">mdi-briefcase</v-icon>
                Исполнитель
              </div>
            </template>
          </v-radio>
        </v-radio-group>

        <!-- Поле для описания исполнителя -->
        <v-textarea
          v-if="selectedRole === 'contractor'"
          v-model="description"
          label="Опишите себя и свой опыт"
          rows="4"
          auto-grow
          class="mt-4"
          :rules="[v => !v || v.length <= 1000 || 'Описание не должно превышать 1000 символов']"
          counter
        ></v-textarea>

        <v-alert v-if="error" type="error" class="mt-3">{{ error }}</v-alert>

        <v-btn type="submit" color="success" class="mt-4" block :loading="loading" :disabled="loading">
          {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
        </v-btn>
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
const description = ref('')
const error = ref('')
const loading = ref(false)
const selectedRole = ref('customer')
const router = useRouter()

const register = async () => {
  error.value = ''
  loading.value = true

  try {
    console.log('Начало регистрации')
    // Регистрация
    const registerResponse = await axios.post('/api/register', {
      usr_name: usrName.value,
      usr_surname: usrSurname.value,
      usr_patronymic: usrPatronymic.value,
      email: email.value,
      password: password.value,
      role: selectedRole.value,
      description: selectedRole.value === 'contractor' ? description.value : undefined
    })
    console.log('Регистрация успешна:', registerResponse.data)

    // Автоматический вход после регистрации
    console.log('Попытка входа')
    const loginResponse = await axios.post('/api/login_check', {
      email: email.value,
      password: password.value
    })
    console.log('Вход успешен:', loginResponse.data)

    // Сохраняем токен и данные пользователя
    const token = loginResponse.data.token
    console.log('Токен получен:', token)
    localStorage.setItem('token', token)
    localStorage.setItem('name', registerResponse.data.user.name)
    localStorage.setItem('surname', registerResponse.data.user.surname)
    localStorage.setItem('email', registerResponse.data.user.email)
    localStorage.setItem('roles', JSON.stringify(registerResponse.data.user.roles))

    // Перенаправляем в дашборд
    console.log('Перенаправление на /dashboard')
    await router.push('/dashboard')
  } catch (err) {
    console.error('Ошибка:', err)
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else if (err.response?.data?.error) {
      // Преобразуем сообщения об ошибках на русский язык
      const errorMessages = {
        'User with this email already exists': 'Пользователь с таким email уже существует',
        'Missing required fields': 'Не все обязательные поля заполнены'
      }
      error.value = errorMessages[err.response.data.error] || err.response.data.error
    } else {
      error.value = 'Произошла ошибка при регистрации'
    }
  } finally {
    loading.value = false
  }
}
</script>
