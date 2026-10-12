<script setup lang="ts">
import { ref } from 'vue'
import { useDisplay } from 'vuetify'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../app/stores/auth'
import { logout } from '../services/authApi'

const router = useRouter()
const authStore = useAuthStore()
const drawer = ref(false)
const { smAndDown } = useDisplay()

async function signOut(): Promise<void> {
  try {
    await logout()
  } finally {
    authStore.clearSession()
    await router.push({ name: 'login' })
  }
}

function closeDrawer(): void {
  if (smAndDown.value) {
    drawer.value = false
  }
}
</script>

<template>
  <v-app-bar color="primary">
    <v-app-bar-nav-icon @click="drawer = !drawer" />
    <v-app-bar-title>Inca Rail Challenge</v-app-bar-title>

    <template #append>
      <span v-if="!smAndDown" class="mr-4">{{ authStore.user?.name }}</span>
    </template>
  </v-app-bar>

  <v-navigation-drawer
    v-model="drawer"
    :permanent="!smAndDown"
    :temporary="smAndDown"
  >
    <v-list nav>
      <v-list-group value="football">
        <template #activator="{ props }">
          <v-list-item v-bind="props" prepend-icon="mdi-soccer" title="Fútbol" />
        </template>

        <v-list-item
          prepend-icon="mdi-trophy-outline"
          title="Ligas"
          to="/competitions"
          @click="closeDrawer"
        />
        <v-list-item
          prepend-icon="mdi-shield-outline"
          title="Equipos"
          to="/teams"
          @click="closeDrawer"
        />
        <v-list-item
          prepend-icon="mdi-account-group-outline"
          title="Jugadores"
          to="/players"
          @click="closeDrawer"
        />
      </v-list-group>

      <v-list-group
        v-if="authStore.can('users.view') || authStore.can('roles.view') || authStore.can('permissions.view')"
        value="administration"
      >
        <template #activator="{ props }">
          <v-list-item v-bind="props" prepend-icon="mdi-shield-account-outline" title="Administración" />
        </template>
        <v-list-item
          v-if="authStore.can('users.view')"
          prepend-icon="mdi-account-multiple-outline"
          title="Usuarios"
          to="/admin/users"
          @click="closeDrawer"
        />
        <v-list-item
          v-if="authStore.can('roles.view')"
          prepend-icon="mdi-account-key-outline"
          title="Roles"
          to="/admin/roles"
          @click="closeDrawer"
        />
        <v-list-item
          v-if="authStore.can('permissions.view')"
          prepend-icon="mdi-key-outline"
          title="Permisos"
          to="/admin/permissions"
          @click="closeDrawer"
        />
      </v-list-group>

      <v-divider class="my-2" />
      <v-list-item prepend-icon="mdi-account" :title="authStore.user?.name" />
      <v-list-item prepend-icon="mdi-logout" title="Cerrar sesión" @click="signOut" />
    </v-list>
  </v-navigation-drawer>

  <v-main>
    <RouterView />
  </v-main>
</template>
