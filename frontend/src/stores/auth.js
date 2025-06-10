import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
export const useAuthStore = defineStore('auth', () => {
  // Состояние для проверки, авторизован ли пользователь
  const isAuthenticated = ref(!!localStorage.getItem('token'))
  const user = ref({})

  // Функция для входа, сохраняющая токен
  const login = async (token) => {
    localStorage.setItem('token', token)  // Сохраняем токен в localStorage
    isAuthenticated.value = true  // Обновляем состояние
    await fetchUserProfile()
  }

  // Функция для выхода, удаляющая токен
  const logout = () => {
    localStorage.removeItem('token')  // Удаляем токен из localStorage
    isAuthenticated.value = false  // Обновляем состояние
  }

  const fetchUserProfile = async () => {
    const token = localStorage.getItem('token')
    if (!token) return

    try {
      const res = await axios.get('/api/profile', {
        headers: { Authorization: `Bearer ${token}` }
      })
      user.value = res.data
    } catch (err) {
      console.error('Ошибка загрузки профиля:', err)
    }
  }
  return { isAuthenticated, login, logout, user, fetchUserProfile }
})
