<template>
  <v-container fluid class="py-10">
    <v-row justify="center">
      <v-col cols="12" md="8">
        <!-- Фильтры -->
        <v-card class="mb-6 pa-4" elevation="2">
          <v-row>
            <!-- Выбор направления -->
            <v-col cols="12" sm="4">
              <v-select
                v-model="selectedDirection"
                :items="directions"
                item-text="title"
                item-value="id"
                label="Выберите направление"
                outlined
                dense
              ></v-select>
            </v-col>

            <!-- Выбор языка -->
            <v-col cols="12" sm="4">
              <v-select
                v-model="selectedLanguage"
                :items="languages"
                item-text="title"
                item-value="id"
                label="Выберите язык"
                :disabled="!selectedDirection"
                outlined
                dense
              ></v-select>
            </v-col>

            <!-- Выбор технологии -->
            <v-col cols="12" sm="4">
              <v-select
                v-model="selectedStacks"
                :items="stacks"
                item-text="title"
                item-value="id"
                label="Выберите технологию"
                :disabled="!selectedLanguage"
                outlined
                dense
              ></v-select>
            </v-col>
          </v-row>
        </v-card>

        <!-- Список заказов -->
        <v-card
          v-for="order in filteredOrders"
          :key="order.id"
          class="mb-4 pa-6"
          elevation="10"
          rounded="xl"
        >
          <v-card-title class="text-h5 text-primary">
            {{ order.ord_title }}
          </v-card-title>

          <v-card-text class="text-body-1 text-grey-darken-2">
            {{ order.ord_text }}
          </v-card-text>

          <!-- Технологии -->
          <v-card-text v-if="order.ord_stacks && order.ord_stacks.length">
            <div class="text-subtitle-2 mb-2">Технологии:</div>
            <v-chip-group>
              <v-chip
                v-for="stack in order.ord_stacks"
                :key="stack.id"
                color="primary"
                variant="outlined"
                class="ma-1"
              >
                {{ stack.title }}
              </v-chip>
            </v-chip-group>
          </v-card-text>

          <v-divider class="my-4" />

          <v-row justify="space-between">
            <v-col cols="6">
              <div class="text-caption text-grey">Статус:</div>
              <div :class="getStatusClass(order.ord_status)" class="text-subtitle-2">
                {{ order.ord_status }}
              </div>
            </v-col>
            <v-col cols="6" class="text-right">
              <div class="text-caption text-grey">Цена:</div>
              <div class="text-subtitle-2 text-error">
                {{ order.ord_price }} ₽
              </div>
            </v-col>
          </v-row>

          <div class="text-caption text-right text-grey mt-2">
            Срок: {{ order.ord_time }}
          </div>

          <v-card-actions class="justify-end">
            <v-btn
              color="primary"
              variant="elevated"
              rounded
              @click="viewDetails(order.id)"
            >
              Подробнее
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Сообщение, если нет заказов -->
        <p v-if="!filteredOrders.length" class="text-center">Заказы не найдены.</p>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const directions = ref([]) // Направления
const languages = ref([])  // Языки
const stacks = ref([])     // Технологии

const selectedDirection = ref(null)
const selectedLanguage = ref(null)
const selectedStacks = ref(null)

const filteredOrders = ref([])

// Загружаем направления при старте
onMounted(() => {
  getDirections()
  filterOrders()
})


// Загружаем направления
const getDirections = async () => {
  const res = await axios.get('/api/directions')
  directions.value = res.data
}

// Следим за выбором направления → загружаем языки
watch(selectedDirection, async (newVal) => {
  if (!newVal) return
  await fetchLanguages()
  selectedLanguage.value = null
  selectedStacks.value = null
  stacks.value = []
  filterOrders()
})

// Загружаем языки для выбранного направления
const fetchLanguages = async () => {
  try {
    const res = await axios.get(`/api/languages/${selectedDirection.value}`)
    // Убираем дубли
    const uniqueLanguages = Array.from(new Set(res.data.map(lang => lang.title)))
      .map(title => res.data.find(lang => lang.title === title))

    languages.value = uniqueLanguages
  } catch (error) {
    console.error('Ошибка при загрузке языков:', error)
  }
}

// Следим за выбором языка → загружаем технологии
watch(selectedLanguage, async (newVal) => {
  if (!newVal) return
  await fetchStacks()
  selectedStacks.value = null
  filterOrders()
})

// Загружаем технологии для выбранного языка и направления
const fetchStacks = async () => {
  if (!selectedLanguage.value || !selectedDirection.value) return
  const res = await axios.get(`/api/stacks/${selectedLanguage.value}/${selectedDirection.value}`)
  stacks.value = res.data
}

// Следим за выбором технологии
watch(selectedStacks, () => {
  filterOrders()
})

// Фильтрация заказов через бэкенд
const filterOrders = async () => {
  const params = {}
  if (selectedDirection.value) params.direction = selectedDirection.value
  if (selectedLanguage.value) params.language = selectedLanguage.value
  if (selectedStacks.value) params.stacks = selectedStacks.value

  try {
    const res = await axios.get('/api/orders', { params })
    filteredOrders.value = res.data
  } catch (error) {
    console.error('Ошибка при фильтрации заказов:', error)
  }
}

const viewDetails = (id) => {
  console.log(`Перейти к деталям заказа: ${id}`)
}
</script>

<style scoped>
.v-card {
  transition: box-shadow 0.3s ease;
}

.v-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.text-grey {
  color: #9e9e9e;
}

.text-orange {
  color: #ffa500;
}

.text-green {
  color: #4caf50;
}

.text-red {
  color: #f44336;
}
</style>

