<template>
  <v-container>
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

          <!-- Технологические группы -->
          <div v-for="(techGroup, index) in technologyGroups" :key="index" class="mb-4">
            <v-card class="pa-4" elevation="2">
              <div class="d-flex justify-space-between align-center mb-4">
                <h3 class="text-h6">Технология {{ index + 1 }}</h3>
                <v-btn
                  v-if="index > 0"
                  color="error"
                  icon
                  @click="removeTechnologyGroup(index)"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </div>

              <!-- Направление -->
              <v-select
                v-model="techGroup.direction"
                :items="directions"
                item-text="title"
                item-value="id"
                label="Направление"
                prepend-icon="mdi-map"
                outlined
                dense
                :rules="[rules.required]"
                @update:model-value="() => updateLanguages(index)"
              />

              <!-- Язык -->
              <v-select
                v-model="techGroup.language"
                :items="techGroup.languages"
                item-text="title"
                item-value="id"
                label="Язык"
                prepend-icon="mdi-translate"
                outlined
                dense
                :disabled="!techGroup.direction"
                :rules="[rules.required]"
                @update:model-value="() => updateStacks(index)"
              />

              <!-- Технология -->
              <v-select
                v-model="techGroup.stack"
                :items="techGroup.stacks"
                item-text="title"
                item-value="id"
                label="Технология"
                prepend-icon="mdi-layers"
                outlined
                dense
                :disabled="!techGroup.language"
                :rules="[rules.required]"
              />
            </v-card>
          </div>

          <v-btn
            color="primary"
            variant="outlined"
            class="mb-4"
            @click="addTechnologyGroup"
            prepend-icon="mdi-plus"
          >
            Добавить технологию
          </v-btn>

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
import { useRouter } from 'vue-router'

const toast = useToast()
const router = useRouter()

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

// Технологические группы
const technologyGroups = ref([
  {
    direction: null,
    language: null,
    stack: null,
    languages: [],
    stacks: []
  }
])

// Выборы и списки
const directions = ref([])

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

// Проверка на дубликаты технологий
const checkDuplicateTechnology = (stackId, currentIndex) => {
  for (let i = 0; i < technologyGroups.value.length; i++) {
    if (i !== currentIndex && technologyGroups.value[i].stack === stackId) {
      return i; // Возвращаем индекс группы с дубликатом
    }
  }
  return -1; // Нет дубликатов
}

// Языки
const updateLanguages = async (index) => {
  const group = technologyGroups.value[index]
  if (!group.direction) return
  
  try {
    const res = await axios.get(`/api/languages/${group.direction}`)
    group.languages = res.data
    group.language = null
    group.stack = null
    group.stacks = []
  } catch (err) {
    console.error('Ошибка загрузки языков:', err)
    toast.error('Ошибка при загрузке языков')
  }
}

// Стек
const updateStacks = async (index) => {
  const group = technologyGroups.value[index]
  if (!group.direction || !group.language) return
  
  try {
    const res = await axios.get(`/api/stacks/${group.language}/${group.direction}`)
    group.stacks = res.data
    group.stack = null
  } catch (err) {
    console.error('Ошибка загрузки стеков:', err)
    toast.error('Ошибка при загрузке технологий')
  }
}

// Добавление новой группы технологий
const addTechnologyGroup = () => {
  technologyGroups.value.push({
    direction: null,
    language: null,
    stack: null,
    languages: [],
    stacks: []
  })
}

// Удаление группы технологий
const removeTechnologyGroup = (index) => {
  technologyGroups.value.splice(index, 1)
}

// Submit
const submit = async () => {
  if (!isValid.value || !user.value) return
  loading.value = true

  // Собираем все выбранные стеки
  const selectedStacks = technologyGroups.value
    .filter(group => group.stack)
    .map(group => group.stack)

  if (selectedStacks.length === 0) {
    toast.error('Выберите хотя бы одну технологию')
    loading.value = false
    return
  }

  try {
    const orderResponse = await axios.post('/api/orders/', {
      ord_title: form.value.title,
      ord_text: form.value.text,
      ord_status: 'Новый',
      ord_price: form.value.price,
      ord_time: form.value.time,
      cst_id: user.value.customerId,
      ord_stacks: selectedStacks
    })

    toast.success('Заказ успешно создан!')
    
    // Обновляем данные пользователя перед перенаправлением
    await fetchUser()
    
    // Перенаправление на страницу активных заказов
    router.push('/customer/active-orders')
  } catch (err) {
    toast.error('Ошибка при создании заказа')
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Следим за изменениями в выборе технологий
watch(technologyGroups, (newGroups) => {
  newGroups.forEach((group, index) => {
    if (group.stack) {
      const duplicateIndex = checkDuplicateTechnology(group.stack, index)
      if (duplicateIndex !== -1) {
        toast.warning('Эта технология уже выбрана')
        // Сбрасываем значение у группы с дубликатом
        technologyGroups.value[duplicateIndex].stack = null
        // Обновляем список стеков для группы с дубликатом
        updateStacks(duplicateIndex)
      }
    }
  })
}, { deep: true })

onMounted(() => {
  fetchUser()
  fetchDirections()
})
</script>

<style scoped>
.v-card {
  margin-bottom: 16px;
}
</style>
