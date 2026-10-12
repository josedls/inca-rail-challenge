<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { ApiError } from '../../services/httpClient'
import {
  changeAdminUserStatus,
  createAdminUser,
  listAdminRoles,
  listAdminUsers,
  updateAdminUser,
  type AdminRole,
  type AdminUser,
} from '../../services/adminApi'
import { useAuthStore } from '../../app/stores/auth'

const authStore = useAuthStore()
const users = ref<AdminUser[]>([])
const roles = ref<AdminRole[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const errorMessage = ref('')
const dialog = ref(false)
const editingUser = ref<AdminUser | null>(null)
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [] as string[],
})

onMounted(loadData)

async function loadData(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const [loadedUsers, loadedRoles] = await Promise.all([
      listAdminUsers(),
      listAdminRoles(),
    ])
    users.value = loadedUsers
    roles.value = loadedRoles
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos cargar los usuarios.')
  } finally {
    isLoading.value = false
  }
}

function openCreate(): void {
  editingUser.value = null
  form.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
  }
  dialog.value = true
}

function openEdit(user: AdminUser): void {
  editingUser.value = user
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    roles: [...user.roles],
  }
  dialog.value = true
}

async function save(): Promise<void> {
  isSaving.value = true
  errorMessage.value = ''
  try {
    const saved = editingUser.value
      ? await updateAdminUser(editingUser.value.id, form.value)
      : await createAdminUser(form.value)
    const index = users.value.findIndex((user) => user.id === saved.id)
    if (index === -1) {
      users.value.push(saved)
    } else {
      users.value[index] = saved
    }
    dialog.value = false
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos guardar el usuario.')
  } finally {
    isSaving.value = false
  }
}

async function toggleStatus(user: AdminUser): Promise<void> {
  try {
    const saved = await changeAdminUserStatus(user.id, !user.is_active)
    Object.assign(user, saved)
    if (saved.id === authStore.user?.id && authStore.user) {
      authStore.setUser({ ...authStore.user, is_active: saved.is_active })
    }
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos cambiar el estado del usuario.')
  }
}

function getErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof ApiError && error.status === 403) return 'No tienes permiso para administrar usuarios.'
  return error instanceof ApiError ? error.message : fallback
}
</script>

<template>
  <v-container class="py-8">
    <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
      <div>
        <h1 class="text-h4 mb-1">Usuarios</h1>
        <p class="text-medium-emphasis">Administra acceso y roles.</p>
      </div>
      <div class="d-flex ga-2">
        <v-btn variant="outlined" :loading="isLoading" @click="loadData">Actualizar</v-btn>
        <v-btn v-if="authStore.can('users.create')" color="primary" prepend-icon="mdi-plus" @click="openCreate">Nuevo usuario</v-btn>
      </div>
    </div>
    <v-alert v-if="errorMessage" type="error" class="mb-6">{{ errorMessage }}</v-alert>
    <div v-if="isLoading" class="d-flex justify-center py-12"><v-progress-circular indeterminate color="primary" /></div>
    <v-card v-else-if="users.length === 0" class="pa-8 text-center"><v-icon icon="mdi-account-off-outline" size="48" class="mb-4" /><h2 class="text-h6">No hay usuarios</h2></v-card>
    <v-card v-else>
      <v-table>
        <thead><tr><th>Usuario</th><th>Roles</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td><div class="font-weight-medium">{{ user.name }}</div><small class="text-medium-emphasis">{{ user.email }}</small></td>
            <td><v-chip v-for="role in user.roles" :key="role" size="small" class="mr-1">{{ role }}</v-chip></td>
            <td><v-chip :color="user.is_active ? 'success' : 'error'" size="small">{{ user.is_active ? 'Activo' : 'Inactivo' }}</v-chip></td>
            <td class="text-end">
              <v-btn v-if="authStore.can('users.update')" icon="mdi-pencil-outline" variant="text" :aria-label="`Editar ${user.name}`" @click="openEdit(user)" />
              <v-btn v-if="authStore.can('users.update')" :icon="user.is_active ? 'mdi-account-off-outline' : 'mdi-account-check-outline'" variant="text" :aria-label="user.is_active ? 'Desactivar usuario' : 'Activar usuario'" @click="toggleStatus(user)" />
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>
    <v-dialog v-model="dialog" max-width="560">
      <v-card><v-card-title>{{ editingUser ? 'Editar usuario' : 'Nuevo usuario' }}</v-card-title>
        <v-card-text>
          <v-text-field v-model="form.name" label="Nombre" />
          <v-text-field v-model="form.email" label="Correo" type="email" />
          <v-text-field
            v-model="form.password"
            :label="editingUser ? 'Nueva contraseña (opcional)' : 'Contraseña'"
            type="password"
          />
          <v-text-field
            v-model="form.password_confirmation"
            label="Confirmar contraseña"
            type="password"
          />
          <v-select
            v-model="form.roles"
            :items="roles.map((role) => role.name)"
            label="Roles"
            multiple
            chips
          />
        </v-card-text>
        <v-card-actions><v-spacer /><v-btn @click="dialog = false">Cancelar</v-btn><v-btn color="primary" :loading="isSaving" :disabled="form.roles.length === 0" @click="save">Guardar</v-btn></v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
