<template>
  <v-container>
    <h2>История заказов</h2>
    <v-list v-if="orders.length">
      <v-list-item
        v-for="order in orders"
        :key="order.id"
      >
        <v-list-item-title>{{ order.title }}</v-list-item-title>
      </v-list-item>
    </v-list>
    <p v-else>История заказов пуста.</p>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const orders = ref([])

onMounted(async () => {
  const token = localStorage.getItem('token')
  const res = await axios.get('/api/customer/completed-orders', {
    headers: { Authorization: `Bearer ${token}` }
  })
  orders.value = res.data
})
</script>
