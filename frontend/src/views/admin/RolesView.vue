<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuthStore } from '../../app/stores/auth'
import { ApiError } from '../../services/httpClient'
import {
  createAdminRole,
  deleteAdminRole,
  listAdminPermissions,
  listAdminRoles,
  updateAdminRole,
  type AdminPermission,
  type AdminRole,
} from '../../services/adminApi'

const authStore = useAuthStore()
const roles = ref<AdminRole[]>([])
const permissions = ref<AdminPermission[]>([])
const isLoading = ref(true)
const isSaving = ref(false)
const errorMessage = ref('')
const dialog = ref(false)
const editingRole = ref<AdminRole | null>(null)
const form = ref({ name: '', permissions: [] as string[] })

onMounted(loadData)

async function loadData(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const [loadedRoles, loadedPermissions] = await Promise.all([
      listAdminRoles(),
      listAdminPermissions(),
    ])

    roles.value = loadedRoles
    permissions.value = loadedPermissions
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos cargar los roles.')
  } finally {
    isLoading.value = false
  }
}

function openCreate(): void {
  editingRole.value = null
  form.value = { name: '', permissions: [] }
  dialog.value = true
}

function openEdit(role: AdminRole): void {
  editingRole.value = role
  form.value = { name: role.name, permissions: [...role.permissions] }
  dialog.value = true
}

async function save(): Promise<void> {
  isSaving.value = true
  errorMessage.value = ''

  try {
    const saved = editingRole.value
      ? await updateAdminRole(editingRole.value.id, form.value.name, form.value.permissions)
      : await createAdminRole(form.value.name, form.value.permissions)
    const index = roles.value.findIndex((role) => role.id === saved.id)

    if (index === -1) {
      roles.value.push(saved)
    } else {
      roles.value[index] = saved
    }

    dialog.value = false
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos guardar el rol.')
  } finally {
    isSaving.value = false
  }
}

async function remove(role: AdminRole): Promise<void> {
  if (!window.confirm(`¿Eliminar el rol ${role.name}?`)) {
    return
  }

  try {
    await deleteAdminRole(role.id)
    roles.value = roles.value.filter((item) => item.id !== role.id)
  } catch (error) {
    errorMessage.value = getErrorMessage(error, 'No pudimos eliminar el rol.')
  }
}

function getErrorMessage(error: unknown, fallback: string): string {
  return error instanceof ApiError ? error.message : fallback
}
</script>

<template>
  <v-container class="py-8">
    <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
      <div>
        <h1 class="text-h4 mb-1">Roles</h1>
        <p class="text-medium-emphasis">Define conjuntos de permisos.</p>
      </div>
      <div class="d-flex ga-2">
        <v-btn variant="outlined" :loading="isLoading" @click="loadData">Actualizar</v-btn>
        <v-btn v-if="authStore.can('roles.create')" color="primary" prepend-icon="mdi-plus" @click="openCreate">
          Nuevo rol
        </v-btn>
      </div>
    </div>
    <v-alert v-if="errorMessage" type="error" class="mb-6">{{ errorMessage }}</v-alert>
    <div v-if="isLoading" class="d-flex justify-center py-12"><v-progress-circular indeterminate color="primary" /></div>
    <v-card v-else-if="roles.length === 0" class="pa-8 text-center"><v-icon icon="mdi-account-key-outline" size="48" class="mb-4" /><h2 class="text-h6">No hay roles</h2></v-card>
    <v-row v-else>
      <v-col v-for="role in roles" :key="role.id" cols="12" md="6">
        <v-card class="h-100">
          <v-card-title>{{ role.name }}</v-card-title>
          <v-card-subtitle>{{ role.permissions.length }} permisos</v-card-subtitle>
          <v-card-text><v-chip v-for="permission in role.permissions" :key="permission" size="small" class="mr-1 mb-1">{{ permission }}</v-chip></v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn v-if="authStore.can('roles.update')" icon="mdi-pencil-outline" variant="text" :aria-label="`Editar ${role.name}`" @click="openEdit(role)" />
            <v-btn v-if="authStore.can('roles.delete')" icon="mdi-delete-outline" variant="text" color="error" :aria-label="`Eliminar ${role.name}`" @click="remove(role)" />
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
    <v-dialog v-model="dialog" max-width="560">
      <v-card>
        <v-card-title>{{ editingRole ? 'Editar rol' : 'Nuevo rol' }}</v-card-title>
        <v-card-text>
          <v-text-field v-model="form.name" label="Nombre" />
          <v-select v-model="form.permissions" :items="permissions.map((permission) => permission.name)" label="Permisos" multiple chips />
        </v-card-text>
        <v-card-actions><v-spacer /><v-btn @click="dialog = false">Cancelar</v-btn><v-btn color="primary" :loading="isSaving" @click="save">Guardar</v-btn></v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
