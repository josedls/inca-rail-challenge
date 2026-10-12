import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createVuetify } from 'vuetify'
import { router } from './app/router'
import 'vuetify/styles'
import './style.css'
import App from './App.vue'

const vuetify = createVuetify()

createApp(App).use(createPinia()).use(router).use(vuetify).mount('#app')
