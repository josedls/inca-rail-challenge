<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../app/stores/auth'
import { logout } from '../services/authApi'

const router = useRouter()
const authStore = useAuthStore()

async function signOut(): Promise<void> {
  try {
    await logout()
  } finally {
    authStore.clearSession()
    await router.push({ name: 'login' })
  }
}
</script>

<template>
  <v-app-bar color="primary">
    <v-app-bar-title>Inca Rail Challenge</v-app-bar-title>

    <template #append>
      <v-btn to="/competitions" variant="text">Ligas</v-btn>
      <span class="mr-4">{{ authStore.user?.name }}</span>
      <v-btn variant="text" @click="signOut">Cerrar sesión</v-btn>
    </template>
  </v-app-bar>

  <v-main>
    <RouterView />
  </v-main>
</template>
