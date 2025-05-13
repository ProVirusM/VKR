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

          <!-- Направление -->
          <v-select
            v-model="selectedDirection"
            :items="directions"
            item-text="title"
            item-value="id"
            label="Направление"
            prepend-icon="mdi-map"
            outlined
            dense
            :rules="[rules.required]"
          />

          <!-- Язык -->
          <v-select
            v-model="selectedLanguage"
            :items="languages"
            item-text="title"
            item-value="id"
            label="Язык"
            prepend-icon="mdi-translate"
            outlined
            dense
            :disabled="!selectedDirection"
            :rules="[rules.required]"
          />

          <!-- Технологии -->
          <v-select
            v-model="form.stacks"
            :items="stacks"
            item-text="title"
            item-value="id"
            label="Технология"
            prepend-icon="mdi-layers"
            outlined
            dense
            :disabled="!selectedLanguage"
            :rules="[rules.required]"

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
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

const form = ref({
  title: '',
  text: '',
  price: null,
  time: '',
  stacks: []
})

const isValid = ref(false)
const loading = ref(false)
const user = ref(null)

const rules = {
  required: v => !!v || 'Обязательное поле',
  positive: v => v > 0 || 'Должно быть положительное число',
}

// Выборы и списки
const selectedDirection = ref(null)
const selectedLanguage = ref(null)
const directions = ref([])
const languages = ref([])
const stacks = ref([])

// Получение текущего пользователя
const fetchUser = async () => {
  const token = localStorage.getItem('token')
  try {
    const res = await axios.get('/api/profile', {
      headers: { Authorization: `Bearer ${token}` }
    })
    user.value = res.data
  } catch (err) {
    console.error('Ошибка получения пользователя:', err)
  }
}

// Направления
const fetchDirections = async () => {
  try {
    const res = await axios.get('/api/directions')
    directions.value = res.data
  } catch (err) {
    console.error('Ошибка загрузки направлений:', err)
  }
}

// Языки
const fetchLanguages = async () => {
  if (!selectedDirection.value) return
  try {
    const res = await axios.get(`/api/languages/${selectedDirection.value}`)
    languages.value = res.data
  } catch (err) {
    console.error('Ошибка загрузки языков:', err)
  }
}

// Стек
const fetchStacks = async () => {
  if (!selectedDirection.value || !selectedLanguage.value) return
  try {
    const res = await axios.get(`/api/stacks/${selectedLanguage.value}/${selectedDirection.value}`)
    stacks.value = res.data
  } catch (err) {
    console.error('Ошибка загрузки стеков:', err)
  }
}

// Watchers
watch(selectedDirection, () => {
  selectedLanguage.value = null
  stacks.value = []
  fetchLanguages()
})

watch(selectedLanguage, () => {
  form.value.stacks = []
  fetchStacks()
})

// Submit
const submit = async () => {
  if (!isValid.value || !user.value) return
  loading.value = true
  console.log(form.value.stacks)
  if (typeof form.value.stacks === 'number') {
    form.value.stacks = [form.value.stacks]; // Преобразуем число в массив
  }
  console.log(form.value.stacks)
  try {
    await axios.post('/api/orders/', {
      ord_title: form.value.title,
      ord_text: form.value.text,
      ord_status: 'Новый', // статус не выбирается
      ord_price: form.value.price,
      ord_time: form.value.time,
      cst_id: user.value.customerId,
      ord_stacks: form.value.stacks // массив ID стеков
    })
    console.log(form.value.stacks)
    toast.success('Заказ успешно создан!')

    form.value = {
      title: '',
      text: '',
      price: null,
      time: '',
      stacks: []
    }

    selectedDirection.value = null
    selectedLanguage.value = null
    stacks.value = []
  } catch (err) {
    toast.error('Ошибка при создании заказа')
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchUser()
  fetchDirections()
})
</script>
