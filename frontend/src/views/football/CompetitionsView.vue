<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { ApiError } from '../../services/httpClient'
import {
  importCompetition,
  listCompetitions,
  type Competition,
} from '../../services/footballApi'

const competitions = ref<Competition[]>([])
const isLoading = ref(true)
const isSyncing = ref(false)
const errorMessage = ref('')
const importingCompetitions = ref<Record<number, boolean>>({})
const pendingImports = ref<Record<number, boolean>>({})
let refreshTimer: number | undefined

onMounted(loadCompetitions)
onUnmounted(() => {
  if (refreshTimer) {
    window.clearTimeout(refreshTimer)
  }
})

async function loadCompetitions(): Promise<void> {
  isLoading.value = competitions.value.length === 0
  errorMessage.value = ''

  try {
    const result = await listCompetitions()
    competitions.value = mergeCompetitionStatuses(result.items)
    isSyncing.value = result.syncQueued

    if (result.syncQueued || hasPendingImports()) {
      scheduleRefresh()
    }
  } catch (error) {
    isSyncing.value = false
    errorMessage.value = getFootballErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

async function requestImport(competition: Competition): Promise<void> {
  importingCompetitions.value[competition.id] = true
  errorMessage.value = ''

  try {
    const result = await importCompetition(competition.id)
    const competitionIndex = competitions.value.findIndex(
      (item) => item.id === competition.id,
    )

    if (competitionIndex !== -1) {
      competitions.value[competitionIndex] = {
        ...competitions.value[competitionIndex],
        import_status: result.status === 'queued' ? 'processing' : result.status,
      }
    }

    pendingImports.value[competition.id] = true

    scheduleRefresh()
  } catch (error) {
    delete pendingImports.value[competition.id]
    errorMessage.value = getFootballErrorMessage(error)
  } finally {
    importingCompetitions.value[competition.id] = false
  }
}

function scheduleRefresh(): void {
  if (refreshTimer) {
    window.clearTimeout(refreshTimer)
  }

  refreshTimer = window.setTimeout(loadCompetitions, 3000)
}

function mergeCompetitionStatuses(items: Competition[]): Competition[] {
  const currentCompetitions = new Map(
    competitions.value.map((competition) => [competition.id, competition]),
  )
  const returnedCompetitionIds = new Set(items.map((competition) => competition.id))
  const mergedCompetitions = items.map((competition): Competition => {
    const currentCompetition = currentCompetitions.get(competition.id)
    const importIsPending = pendingImports.value[competition.id] === true

    if (competition.import_status === 'completed' || competition.import_status === 'failed') {
      delete pendingImports.value[competition.id]
    }

    if (importIsPending && !isTerminalStatus(competition.import_status)) {
      return { ...competition, import_status: 'processing' as const }
    }

    if (
      currentCompetition?.import_status === 'processing' &&
      !isTerminalStatus(competition.import_status)
    ) {
      return { ...competition, import_status: 'processing' as const }
    }

    return competition
  })

  const pendingCompetitions = competitions.value.filter(
    (competition) => pendingImports.value[competition.id] && !returnedCompetitionIds.has(competition.id),
  )

  return [...mergedCompetitions, ...pendingCompetitions]
}

function hasPendingImports(): boolean {
  return Object.values(pendingImports.value).some(Boolean)
}

function isTerminalStatus(status: Competition['import_status']): boolean {
  return status === 'completed' || status === 'failed'
}

function getFootballErrorMessage(error: unknown): string {
  if (error instanceof ApiError) {
    if (error.status === 403) {
      return 'No tienes permiso para consultar las ligas.'
    }

    if (error.status === 429) {
      return 'Se alcanzó el límite de consultas. Inténtalo nuevamente en un momento.'
    }

    if ([502, 503, 504].includes(error.status)) {
      return 'El servicio de fútbol no está disponible. Los datos guardados siguen accesibles.'
    }
  }

  return 'No pudimos cargar las ligas. Inténtalo nuevamente.'
}
</script>

<template>
  <v-container class="py-8">
    <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
      <div>
        <h1 class="text-h4 mb-1">Ligas</h1>
        <p class="text-medium-emphasis">Explora las ligas disponibles y consulta su información.</p>
      </div>

      <v-btn :loading="isLoading" variant="outlined" @click="loadCompetitions">
        Actualizar
      </v-btn>
    </div>

    <v-alert v-if="errorMessage" class="mb-6" type="error" closable @click:close="errorMessage = ''">
      {{ errorMessage }}
    </v-alert>

    <v-alert v-if="isSyncing" class="mb-6" type="info">
      Estamos sincronizando el catálogo inicial. Esta pantalla se actualizará automáticamente.
    </v-alert>

    <div v-if="isLoading" class="d-flex justify-center py-12">
      <v-progress-circular color="primary" indeterminate />
    </div>

    <v-card v-else-if="competitions.length === 0" class="pa-8 text-center">
      <v-icon icon="mdi-trophy-outline" size="48" class="mb-4" />
      <h2 class="text-h6 mb-2">Aún no hay ligas guardadas</h2>
      <p class="text-medium-emphasis">La sincronización inicial se iniciará automáticamente.</p>
    </v-card>

    <v-row v-else>
      <v-col v-for="competition in competitions" :key="competition.id" cols="12" sm="6" lg="4">
        <v-card class="h-100 d-flex flex-column">
          <v-card-item>
            <template #prepend>
              <v-avatar color="primary" rounded="lg">
                <v-img v-if="competition.emblem" :src="competition.emblem" alt="" />
                <v-icon v-else icon="mdi-trophy-outline" />
              </v-avatar>
            </template>
            <v-card-title>{{ competition.name }}</v-card-title>
            <v-card-subtitle>{{ competition.code ?? 'Sin código' }}</v-card-subtitle>
          </v-card-item>

          <v-spacer />

          <v-card-actions>
            <v-chip v-if="competition.import_status === 'completed'" color="success" size="small">
              Importada
            </v-chip>
            <v-chip v-else-if="competition.import_status === 'processing'" color="info" size="small">
              Procesando
            </v-chip>
            <v-chip v-else-if="competition.import_status === 'failed'" color="error" size="small">
              Error de importación
            </v-chip>

            <v-spacer />

            <v-btn
              color="primary"
              variant="text"
              :loading="importingCompetitions[competition.id]"
              :disabled="competition.import_status === 'processing'"
              @click="requestImport(competition)"
            >
              Importar liga
            </v-btn>
          </v-card-actions>

          <div
            v-if="competition.import_status === 'processing' && competition.import_progress.total > 0"
            class="px-4 pb-4"
          >
            <div class="d-flex justify-space-between text-caption mb-1">
              <span>Importando jugadores de los equipos</span>
              <span>{{ competition.import_progress.completed }} de {{ competition.import_progress.total }} equipos procesados</span>
            </div>
            <v-progress-linear
              :model-value="competition.import_progress.percentage"
              color="info"
              rounded
            />
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
