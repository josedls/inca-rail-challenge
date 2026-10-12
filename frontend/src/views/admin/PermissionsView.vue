<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuthStore } from '../../app/stores/auth'
import { ApiError } from '../../services/httpClient'
import {
  createAdminPermission,
  deleteAdminPermission,
  listAdminPermissions,
  updateAdminPermission,
  type AdminPermission,
} from '../../services/adminApi'

const authStore = useAuthStore()
const permissions = ref<AdminPermission[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const errorMessage = ref('')
const dialog = ref(false)
const editingPermission = ref<AdminPermission | null>(null)
const name = ref('')

onMounted(loadPermissions)

async function loadPermissions(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    permissions.value = await listAdminPermissions()
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos cargar los permisos.')
  } finally {
    isLoading.value = false
  }
}

function openCreate(): void {
  editingPermission.value = null
  name.value = ''
  dialog.value = true
}

function openEdit(permission: AdminPermission): void {
  editingPermission.value = permission
  name.value = permission.name
  dialog.value = true
}

async function save(): Promise<void> {
  isSaving.value = true
  errorMessage.value = ''

  try {
    const saved = editingPermission.value
      ? await updateAdminPermission(editingPermission.value.id, name.value)
      : await createAdminPermission(name.value)
    const index = permissions.value.findIndex((permission) => permission.id === saved.id)

    if (index === -1) {
      permissions.value.push(saved)
    } else {
      permissions.value[index] = saved
    }

    dialog.value = false
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos guardar el permiso.')
  } finally {
    isSaving.value = false
  }
}

async function remove(permission: AdminPermission): Promise<void> {
  if (!window.confirm(`¿Eliminar el permiso ${permission.name}?`)) {
    return
  }

  try {
    await deleteAdminPermission(permission.id)
    permissions.value = permissions.value.filter((item) => item.id !== permission.id)
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos eliminar el permiso.')
  }
}

function getErrorMessage(error: unknown, fallback: string): string {
  return error instanceof ApiError ? error.message : fallback
}
</script>

<template>
  <v-container class="py-8">
    <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
      <div><h1 class="text-h4 mb-1">Permisos</h1><p class="text-medium-emphasis">Administra las capacidades disponibles.</p></div>
      <div class="d-flex ga-2"><v-btn variant="outlined" :loading="isLoading" @click="loadPermissions">Actualizar</v-btn><v-btn v-if="authStore.can('permissions.create')" color="primary" prepend-icon="mdi-plus" @click="openCreate">Nuevo permiso</v-btn></div>
    </div>
    <v-alert v-if="errorMessage" type="error" class="mb-6">{{ errorMessage }}</v-alert>
    <div v-if="isLoading" class="d-flex justify-center py-12"><v-progress-circular indeterminate color="primary" /></div>
    <v-card v-else-if="permissions.length === 0" class="pa-8 text-center"><v-icon icon="mdi-key-off-outline" size="48" class="mb-4" /><h2 class="text-h6">No hay permisos</h2></v-card>
    <v-card v-else><v-list><v-list-item v-for="permission in permissions" :key="permission.id" :title="permission.name" :subtitle="permission.guard_name"><template #append><v-btn v-if="authStore.can('permissions.update')" icon="mdi-pencil-outline" variant="text" :aria-label="`Editar ${permission.name}`" @click="openEdit(permission)" /><v-btn v-if="authStore.can('permissions.delete')" icon="mdi-delete-outline" variant="text" color="error" :aria-label="`Eliminar ${permission.name}`" @click="remove(permission)" /></template></v-list-item></v-list></v-card>
    <v-dialog v-model="dialog" max-width="560"><v-card><v-card-title>{{ editingPermission ? 'Editar permiso' : 'Nuevo permiso' }}</v-card-title><v-card-text><v-text-field v-model="name" label="Nombre" placeholder="resource.action" /></v-card-text><v-card-actions><v-spacer /><v-btn @click="dialog = false">Cancelar</v-btn><v-btn color="primary" :loading="isSaving" :disabled="!name.trim()" @click="save">Guardar</v-btn></v-card-actions></v-card></v-dialog>
  </v-container>
</template>
