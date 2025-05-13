<template>
  <v-container class="py-10">
    <v-card class="mx-auto pa-6" max-width="600" elevation="10" rounded="xl">
      <v-card-title class="text-h5 font-weight-bold text-center">Создание нового заказа</v-card-title>
      <v-card-text>
        <v-form ref="form" v-model="isValid" lazy-validation>
          <v-text-field
            v-model="form.title"
            label="Название заказа"
            :rules="[rules.required]"
            prepend-icon="mdi-clipboard-text-outline"
            outlined
            dense
          />

          <v-textarea
            v-model="form.text"
            label="Описание"
            :rules="[rules.required]"
            prepend-icon="mdi-text"
            outlined
            dense
          />

          <v-select
            v-model="form.status"
            label="Статус"
            :items="['Новый', 'В процессе', 'Завершен']"
            :rules="[rules.required]"
            prepend-icon="mdi-flag"
            outlined
            dense
          />

          <v-text-field
            v-model.number="form.price"
            label="Бюджет (₽)"
            type="number"
            :rules="[rules.required, rules.positive]"
            prepend-icon="mdi-currency-rub"
            outlined
            dense
          />

          <v-text-field
            v-model="form.time"
            label="Срок выполнения"
            :rules="[rules.required]"
            prepend-icon="mdi-clock-outline"
            outlined
            dense
          />

          <v-btn
            :disabled="!isValid || loading"
            @click="submit"
            class="mt-6"
            color="primary"
            block
            size="large"
            :loading="loading"
          >
            Создать заказ
          </v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import {ref} from 'vue'
import axios from 'axios'
import {useToast} from 'vue-toastification'

const toast = useToast()

// Состояния формы
const form = ref({
  title: '',
  text: '',
  status: '',
  price: null,
  time: '',
})

// Состояние валидации и загрузки
const isValid = ref(false)
const loading = ref(false)

const rules = {
  required: value => !!value || 'Обязательное поле',
  positive: v => v > 0 || 'Должно быть положительное число',
}

// Переменная для данных пользователя
const user = ref(null)

// Функция для получения данных о текущем пользователе
const fetchUser = async () => {
  const token = localStorage.getItem('token')
  console.log(token)
  try {
    const response = await axios.get('/api/profile', {
      headers: {Authorization: `Bearer ${token}`}
    }) // Замените на свой эндпоинт, чтобы получить данные о текущем пользователе
    user.value = response.data
  } catch (error) {
    console.error('Не удалось получить данные о пользователе:', error)
  }
}

// Загружаем данные о пользователе при монтировании компонента
fetchUser()

// Функция отправки данных заказа
const submit = async () => {
  if (!isValid.value || !user.value) return

  loading.value = true
  try {
    const response = await axios.post('/api/orders/', {
      ord_title: form.value.title,
      ord_text: form.value.text,
      ord_status: form.value.status,
      ord_price: form.value.price,
      ord_time: form.value.time,
      cst_id: user.value.customerId // Передаем customerId текущего пользователя
    })

    toast.success('Заказ успешно создан!')
    form.value = {
      title: '',
      text: '',
      status: '',
      price: null,
      time: '',
    }
  } catch (error) {
    toast.error('Ошибка при создании заказа')
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.v-text-field,
.v-textarea,
.v-select {
  margin-bottom: 16px;
}
</style>
