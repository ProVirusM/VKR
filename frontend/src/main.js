// import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import vuetify from './plugins/vuetify'
import '@mdi/font/css/materialdesignicons.css'
import App from './App.vue'
import router from './router'
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(vuetify)
app.use(Toast)
app.mount('#app')
