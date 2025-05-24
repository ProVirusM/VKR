<template>
  <v-container>
    <v-card class="pa-6">
      <v-card-title class="text-h5 mb-4">
        Управление направлениями
      </v-card-title>

      <!-- Форма добавления нового направления -->
      <v-form @submit.prevent="createDirection" class="mb-6">
        <v-row>
          <v-col cols="12" md="8">
            <v-text-field
              v-model="newDirection.title"
              label="Название направления"
              required
              :rules="[v => !!v || 'Название обязательно']"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4" class="d-flex align-center">
            <v-btn
              color="primary"
              type="submit"
              :loading="loading"
              :disabled="!newDirection.title"
            >
              Добавить направление
            </v-btn>
          </v-col>
        </v-row>
      </v-form>

      <!-- Таблица направлений -->
      <v-data-table
        :headers="headers"
        :items="directions"
        :loading="loading"
        class="elevation-1"
      >
        <template v-slot:item.actions="{ item }">
          <v-btn
            icon="mdi-pencil"
            size="small"
            color="primary"
            class="me-2"
            @click="editDirection(item)"
          ></v-btn>
          <v-btn
            icon="mdi-delete"
            size="small"
            color="error"
            @click="deleteDirection(item)"
          ></v-btn>
        </template>
      </v-data-table>

      <!-- Диалог редактирования -->
      <v-dialog v-model="editDialog" max-width="500px">
        <v-card>
          <v-card-title>Редактировать направление</v-card-title>
          <v-card-text>
            <v-text-field
              v-model="editedDirection.title"
              label="Название направления"
              required
            ></v-text-field>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="error" @click="editDialog = false">Отмена</v-btn>
            <v-btn color="primary" @click="saveDirection">Сохранить</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const directions = ref([])
const loading = ref(false)
const editDialog = ref(false)
const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Название', key: 'title' },
  { title: 'Действия', key: 'actions', sortable: false }
]

const newDirection = ref({
  title: ''
})

const editedDirection = ref({
  id: null,
  title: ''
})

const fetchDirections = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/directions')
    directions.value = response.data
  } catch (error) {
    console.error('Error fetching directions:', error)
  } finally {
    loading.value = false
  }
}

const createDirection = async () => {
  if (!newDirection.value.title) return

  loading.value = true
  try {
    await axios.post('/api/directions/', newDirection.value)
    newDirection.value.title = ''
    await fetchDirections()
  } catch (error) {
    console.error('Error creating direction:', error)
  } finally {
    loading.value = false
  }
}

const editDirection = (item) => {
  editedDirection.value = { ...item }
  editDialog.value = true
}

const saveDirection = async () => {
  if (!editedDirection.value.title) return

  loading.value = true
  try {
    await axios.put(`/api/directions/${editedDirection.value.id}`, {
      title: editedDirection.value.title
    })
    editDialog.value = false
    await fetchDirections()
  } catch (error) {
    console.error('Error updating direction:', error)
  } finally {
    loading.value = false
  }
}

const deleteDirection = async (item) => {
  if (!confirm('Вы уверены, что хотите удалить это направление?')) return

  loading.value = true
  try {
    await axios.delete(`/api/directions/${item.id}`)
    await fetchDirections()
  } catch (error) {
    console.error('Error deleting direction:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchDirections)
</script>
