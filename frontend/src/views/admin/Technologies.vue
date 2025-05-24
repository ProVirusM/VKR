<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h5 mb-4">
        Управление технологиями
      </v-card-title>

      <!-- Форма добавления новой технологии -->
      <v-form @submit.prevent="createTechnology" class="mb-6">
        <v-row>
          <v-col cols="12" md="4">
            <v-select
              v-model="newTechnology.direction_id"
              :items="directions"
              item-title="title"
              item-value="id"
              label="Направление"
              required
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="newTechnology.language_id"
              :items="languages"
              item-title="title"
              item-value="id"
              label="Язык программирования"
              required
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="newTechnology.title"
              label="Название технологии"
              required
              :rules="[v => !!v || 'Название обязательно']"
            ></v-text-field>
          </v-col>
          <v-col cols="12" class="d-flex justify-end">
            <v-btn
              color="primary"
              type="submit"
              :loading="loading"
              :disabled="!newTechnology.title || !newTechnology.direction_id || !newTechnology.language_id"
            >
              Добавить технологию
            </v-btn>
          </v-col>
        </v-row>
      </v-form>

      <!-- Таблица технологий -->
      <v-data-table
        :headers="headers"
        :items="technologies"
        :loading="loading"
        class="elevation-1"
      >
        <template v-slot:item.direction="{ item }">
          {{ getDirectionName(item.direction_id) }}
        </template>
        <template v-slot:item.language="{ item }">
          {{ getLanguageName(item.language_id) }}
        </template>
        <template v-slot:item.actions="{ item }">
          <v-btn
            icon="mdi-pencil"
            size="small"
            color="primary"
            class="me-2"
            @click="editTechnology(item)"
          ></v-btn>
          <v-btn
            icon="mdi-delete"
            size="small"
            color="error"
            @click="deleteTechnology(item)"
          ></v-btn>
        </template>
      </v-data-table>

      <!-- Диалог редактирования -->
      <v-dialog v-model="editDialog" max-width="500px">
        <v-card>
          <v-card-title>Редактировать технологию</v-card-title>
          <v-card-text>
            <v-select
              v-model="editedTechnology.direction_id"
              :items="directions"
              item-title="title"
              item-value="id"
              label="Направление"
              required
              class="mb-4"
            ></v-select>
            <v-select
              v-model="editedTechnology.language_id"
              :items="languages"
              item-title="title"
              item-value="id"
              label="Язык программирования"
              required
              class="mb-4"
            ></v-select>
            <v-text-field
              v-model="editedTechnology.title"
              label="Название технологии"
              required
            ></v-text-field>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="error" @click="editDialog = false">Отмена</v-btn>
            <v-btn color="primary" @click="saveTechnology">Сохранить</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const technologies = ref([])
const directions = ref([])
const languages = ref([])
const loading = ref(false)
const editDialog = ref(false)

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Название', key: 'title' },
  { title: 'Направление', key: 'direction' },
  { title: 'Язык программирования', key: 'language' },
  { title: 'Действия', key: 'actions', sortable: false }
]

const newTechnology = ref({
  title: '',
  direction_id: null,
  language_id: null
})

const editedTechnology = ref({
  id: null,
  title: '',
  direction_id: null,
  language_id: null
})

const fetchTechnologies = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/stacks/all')
    technologies.value = response.data
  } catch (error) {
    console.error('Error fetching technologies:', error)
  } finally {
    loading.value = false
  }
}

const fetchDirections = async () => {
  try {
    const response = await axios.get('/api/directions')
    directions.value = response.data
  } catch (error) {
    console.error('Error fetching directions:', error)
  }
}

const fetchLanguages = async () => {
  try {
    const response = await axios.get('/api/languages')
    languages.value = response.data
  } catch (error) {
    console.error('Error fetching languages:', error)
  }
}

const getDirectionName = (directionId) => {
  const direction = directions.value.find(d => d.id === directionId)
  return direction ? direction.title : ''
}

const getLanguageName = (languageId) => {
  const language = languages.value.find(l => l.id === languageId)
  return language ? language.title : ''
}

const createTechnology = async () => {
  if (!newTechnology.value.title || !newTechnology.value.direction_id || !newTechnology.value.language_id) return

  loading.value = true
  try {
    await axios.post('/api/stacks/', newTechnology.value)
    newTechnology.value = {
      title: '',
      direction_id: null,
      language_id: null
    }
    await fetchTechnologies()
  } catch (error) {
    console.error('Error creating technology:', error)
  } finally {
    loading.value = false
  }
}

const editTechnology = (item) => {
  editedTechnology.value = { ...item }
  editDialog.value = true
}

const saveTechnology = async () => {
  if (!editedTechnology.value.title || !editedTechnology.value.direction_id || !editedTechnology.value.language_id) return

  loading.value = true
  try {
    await axios.put(`/api/stacks/${editedTechnology.value.id}`, {
      title: editedTechnology.value.title,
      direction_id: editedTechnology.value.direction_id,
      language_id: editedTechnology.value.language_id
    })
    editDialog.value = false
    await fetchTechnologies()
  } catch (error) {
    console.error('Error updating technology:', error)
  } finally {
    loading.value = false
  }
}

const deleteTechnology = async (item) => {
  if (!confirm('Вы уверены, что хотите удалить эту технологию?')) return

  loading.value = true
  try {
    await axios.delete(`/api/stacks/${item.id}`)
    await fetchTechnologies()
  } catch (error) {
    console.error('Error deleting technology:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTechnologies()
  fetchDirections()
  fetchLanguages()
})
</script>
