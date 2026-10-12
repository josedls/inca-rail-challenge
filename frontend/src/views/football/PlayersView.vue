<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { ApiError } from '../../services/httpClient'
import { listPlayers, type Player } from '../../services/footballApi'

const players = ref<Player[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

onMounted(loadPlayers)

async function loadPlayers(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    players.value = await listPlayers()
  } catch (error) {
    errorMessage.value = getPlayersErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

function getPlayersErrorMessage(error: unknown): string {
  if (error instanceof ApiError && error.status === 403) {
    return 'No tienes permiso para consultar los jugadores.'
  }

  return 'No pudimos cargar los jugadores. Inténtalo nuevamente.'
}
</script>

<template>
  <v-container class="py-8">
    <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
      <div>
        <h1 class="text-h4 mb-1">Jugadores</h1>
        <p class="text-medium-emphasis">Consulta los jugadores guardados localmente.</p>
      </div>

      <v-btn :loading="isLoading" variant="outlined" @click="loadPlayers">
        Actualizar
      </v-btn>
    </div>

    <v-alert v-if="errorMessage" class="mb-6" type="error">
      {{ errorMessage }}
    </v-alert>

    <div v-if="isLoading" class="d-flex justify-center py-12">
      <v-progress-circular color="primary" indeterminate />
    </div>

    <v-card v-else-if="players.length === 0" class="pa-8 text-center">
      <v-icon icon="mdi-account-group-outline" size="48" class="mb-4" />
      <h2 class="text-h6 mb-2">Aún no hay jugadores guardados</h2>
      <p class="text-medium-emphasis">Importa una liga para conservar sus jugadores.</p>
    </v-card>

    <v-row v-else>
      <v-col v-for="player in players" :key="player.id" cols="12" sm="6" lg="4">
        <v-card class="h-100">
          <v-card-item>
            <template #prepend>
              <v-avatar color="secondary">
                <v-icon icon="mdi-account-outline" />
              </v-avatar>
            </template>
            <v-card-title>{{ player.name }}</v-card-title>
            <v-card-subtitle>{{ player.position ?? 'Posición no disponible' }}</v-card-subtitle>
          </v-card-item>

          <v-card-text>
            <div>Camiseta: {{ player.shirt_number ?? 'No disponible' }}</div>
            <div>Nacionalidad: {{ player.nationality ?? 'No disponible' }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
