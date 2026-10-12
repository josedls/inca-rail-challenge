<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { ApiError } from '../../services/httpClient'
import { listPlayers, type Player } from '../../services/footballApi'

const players = ref<Player[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const search = ref('')
const selectedPosition = ref<string | null>(null)
const selectedNationality = ref<string | null>(null)
const page = ref(1)
const itemsPerPage = ref(12)

const positions = computed(() => getFilterOptions('position'))
const nationalities = computed(() => getFilterOptions('nationality'))
const filteredPlayers = computed(() => {
  const normalizedSearch = search.value.trim().toLocaleLowerCase()

  return players.value.filter((player) => {
    const matchesSearch = [player.name, player.position, player.nationality]
      .filter(Boolean)
      .some((value) => value!.toLocaleLowerCase().includes(normalizedSearch))
    const matchesPosition = !selectedPosition.value || player.position === selectedPosition.value
    const matchesNationality = !selectedNationality.value || player.nationality === selectedNationality.value

    return matchesSearch && matchesPosition && matchesNationality
  })
})
const pageCount = computed(() => Math.max(1, Math.ceil(filteredPlayers.value.length / itemsPerPage.value)))
const visiblePlayers = computed(() => {
  const start = (page.value - 1) * itemsPerPage.value

  return filteredPlayers.value.slice(start, start + itemsPerPage.value)
})

watch([search, selectedPosition, selectedNationality, itemsPerPage], () => {
  page.value = 1
})

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

function getFilterOptions(field: 'position' | 'nationality'): string[] {
  return [...new Set(
    players.value
      .map((player) => player[field])
      .filter((value): value is string => Boolean(value)),
  )].sort((first, second) => first.localeCompare(second))
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

    <template v-else>
      <v-card class="mb-6 pa-4">
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              clearable
              hide-details
              label="Buscar jugador"
              prepend-inner-icon="mdi-magnify"
              placeholder="Nombre, posición o nacionalidad"
              @click:clear="search = ''"
            />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-select
              v-model="selectedPosition"
              :items="positions"
              clearable
              hide-details
              label="Posición"
            />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-select
              v-model="selectedNationality"
              :items="nationalities"
              clearable
              hide-details
              label="Nacionalidad"
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-select
              v-model="itemsPerPage"
              :items="[6, 12, 24]"
              hide-details
              label="Por página"
            />
          </v-col>
        </v-row>
      </v-card>

      <v-card v-if="filteredPlayers.length === 0" class="pa-8 text-center">
        <v-icon icon="mdi-filter-off-outline" size="48" class="mb-4" />
        <h2 class="text-h6 mb-2">No encontramos jugadores</h2>
        <p class="text-medium-emphasis">Prueba con otros términos o filtros.</p>
      </v-card>

      <v-row v-else>
      <v-col v-for="player in visiblePlayers" :key="player.id" cols="12" sm="6" lg="4">
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

      <v-pagination
        v-if="pageCount > 1"
        v-model="page"
        class="mt-6"
        :length="pageCount"
        rounded="circle"
      />
    </template>
  </v-container>
</template>
