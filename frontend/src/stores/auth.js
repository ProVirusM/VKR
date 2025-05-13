import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // Состояние для проверки, авторизован ли пользователь
  const isAuthenticated = ref(!!localStorage.getItem('token'))

  // Функция для входа, сохраняющая токен
  const login = (token) => {
    localStorage.setItem('token', token)  // Сохраняем токен в localStorage
    isAuthenticated.value = true  // Обновляем состояние
  }

  // Функция для выхода, удаляющая токен
  const logout = () => {
    localStorage.removeItem('token')  // Удаляем токен из localStorage
    isAuthenticated.value = false  // Обновляем состояние
  }

  return { isAuthenticated, login, logout }
})
