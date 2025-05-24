<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h5 mb-4">
        Управление языками программирования
      </v-card-title>

      <!-- Форма добавления нового языка -->
      <v-form @submit.prevent="createLanguage" class="mb-6">
        <v-row>
          <v-col cols="12" md="8">
            <v-text-field
              v-model="newLanguage.title"
              label="Название языка"
              required
              :rules="[v => !!v || 'Название обязательно']"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4" class="d-flex align-center">
            <v-btn
              color="primary"
              type="submit"
              :loading="loading"
              :disabled="!newLanguage.title"
            >
              Добавить язык
            </v-btn>
          </v-col>
        </v-row>
      </v-form>

      <!-- Таблица языков -->
      <v-data-table
        :headers="headers"
        :items="languages"
        :loading="loading"
        class="elevation-1"
      >
        <template v-slot:item.actions="{ item }">
          <v-btn
            icon="mdi-pencil"
            size="small"
            color="primary"
            class="me-2"
            @click="editLanguage(item)"
          ></v-btn>
          <v-btn
            icon="mdi-delete"
            size="small"
            color="error"
            @click="deleteLanguage(item)"
          ></v-btn>
        </template>
      </v-data-table>

      <!-- Диалог редактирования -->
      <v-dialog v-model="editDialog" max-width="500px">
        <v-card>
          <v-card-title>Редактировать язык</v-card-title>
          <v-card-text>
            <v-text-field
              v-model="editedLanguage.title"
              label="Название языка"
              required
            ></v-text-field>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="error" @click="editDialog = false">Отмена</v-btn>
            <v-btn color="primary" @click="saveLanguage">Сохранить</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const languages = ref([])
const loading = ref(false)
const editDialog = ref(false)
const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Название', key: 'title' },
  { title: 'Действия', key: 'actions', sortable: false }
]

const newLanguage = ref({
  title: ''
})

const editedLanguage = ref({
  id: null,
  title: ''
})

const fetchLanguages = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/languages')
    languages.value = response.data
  } catch (error) {
    console.error('Error fetching languages:', error)
  } finally {
    loading.value = false
  }
}

const createLanguage = async () => {
  if (!newLanguage.value.title) return

  loading.value = true
  try {
    await axios.post('/api/languages/', newLanguage.value)
    newLanguage.value.title = ''
    await fetchLanguages()
  } catch (error) {
    console.error('Error creating language:', error)
  } finally {
    loading.value = false
  }
}

const editLanguage = (item) => {
  editedLanguage.value = { ...item }
  editDialog.value = true
}

const saveLanguage = async () => {
  if (!editedLanguage.value.title) return

  loading.value = true
  try {
    await axios.put(`/api/languages/${editedLanguage.value.id}`, {
      title: editedLanguage.value.title
    })
    editDialog.value = false
    await fetchLanguages()
    alert('Язык успешно обновлен')
  } catch (error) {
    console.error('Error updating language:', error)
    alert(error.response?.data?.error || 'Ошибка при обновлении языка')
  } finally {
    loading.value = false
  }
}

const deleteLanguage = async (item) => {
  if (!confirm('Вы уверены, что хотите удалить этот язык?')) return

  loading.value = true
  try {
    await axios.delete(`/api/languages/${item.id}`)
    await fetchLanguages()
  } catch (error) {
    console.error('Error deleting language:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchLanguages)
</script>
