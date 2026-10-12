import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createVuetify } from 'vuetify'
import { router } from './app/router'
import { useAuthStore } from './app/stores/auth'
import { getCurrentUser } from './services/authApi'
import 'vuetify/styles'
import './style.css'
import App from './App.vue'

const vuetify = createVuetify()
const pinia = createPinia()
const app = createApp(App)

app.use(pinia).use(router).use(vuetify)

const authStore = useAuthStore()

if (authStore.isAuthenticated) {
  try {
    authStore.setUser(await getCurrentUser())
  } catch {
    authStore.clearSession()
  }
}

app.mount('#app')
