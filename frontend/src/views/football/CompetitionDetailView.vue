<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ApiError } from '../../services/httpClient'
import { getCompetition, type Competition } from '../../services/footballApi'

const route = useRoute()
const router = useRouter()
const competition = ref<Competition | null>(null)
const isLoading = ref(true)
const errorMessage = ref('')

onMounted(loadCompetition)

async function loadCompetition(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    competition.value = await getCompetition(Number(route.params.competition))
  } catch (error) {
    errorMessage.value = getCompetitionErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

function getCompetitionErrorMessage(error: unknown): string {
  if (error instanceof ApiError) {
    if (error.status === 403) {
      return 'No tienes permiso para consultar esta liga.'
    }

    if (error.status === 404) {
      return 'La liga solicitada no existe en el catálogo guardado.'
    }
  }

  return 'No pudimos cargar el detalle de la liga. Inténtalo nuevamente.'
}

function statusLabel(status: Competition['import_status']): string {
  const labels: Record<NonNullable<Competition['import_status']>, string> = {
    available: 'Disponible',
    processing: 'Procesando',
    completed: 'Importada',
    failed: 'Error de importación',
  }

  return status ? labels[status] : 'Sin importar'
}

function statusColor(status: Competition['import_status']): string {
  if (status === 'completed') return 'success'
  if (status === 'processing') return 'info'
  if (status === 'failed') return 'error'

  return 'default'
}
</script>

<template>
  <v-container class="py-8">
    <v-btn
      class="mb-6"
      prepend-icon="mdi-arrow-left"
      variant="text"
      @click="router.push({ name: 'competitions' })"
    >
      Volver a ligas
    </v-btn>

    <div v-if="isLoading" class="d-flex justify-center py-12">
      <v-progress-circular color="primary" indeterminate />
    </div>

    <v-alert v-else-if="errorMessage" type="error">
      {{ errorMessage }}
    </v-alert>

    <template v-else-if="competition">
      <div class="d-flex flex-wrap align-center ga-4 mb-6">
        <v-avatar color="primary" rounded="lg" size="64">
          <v-img v-if="competition.emblem" :src="competition.emblem" alt="" />
          <v-icon v-else icon="mdi-trophy-outline" size="32" />
        </v-avatar>

        <div>
          <h1 class="text-h4 mb-1">{{ competition.name }}</h1>
          <p class="text-medium-emphasis">{{ competition.code ?? 'Sin código' }}</p>
        </div>

        <v-spacer />
        <v-chip :color="statusColor(competition.import_status)">
          {{ statusLabel(competition.import_status) }}
        </v-chip>
      </div>

      <v-row>
        <v-col cols="12" md="4">
          <v-card class="h-100 pa-5">
            <div class="text-overline text-medium-emphasis">Equipos guardados</div>
            <div class="text-h3">{{ competition.import_progress.total }}</div>
            <p class="text-body-2 text-medium-emphasis mt-2">
              Equipos asociados durante la importación de esta liga.
            </p>
          </v-card>
        </v-col>

        <v-col cols="12" md="8">
          <v-card class="h-100 pa-5">
            <div class="d-flex justify-space-between mb-2">
              <span class="text-subtitle-1">Importación</span>
              <span class="text-body-2">{{ competition.import_progress.percentage }}%</span>
            </div>
            <v-progress-linear
              :model-value="competition.import_progress.percentage"
              :color="statusColor(competition.import_status)"
              rounded
            />
            <p v-if="competition.import_error" class="text-body-2 text-error mt-4">
              {{ competition.import_error }}
            </p>
            <p v-else class="text-body-2 text-medium-emphasis mt-4">
              {{ competition.import_progress.completed }} equipos procesados,
              {{ competition.import_progress.pending }} pendientes y
              {{ competition.import_progress.failed }} con error.
            </p>
          </v-card>
        </v-col>
      </v-row>

      <v-card class="mt-6">
        <v-card-title>Equipos de la liga</v-card-title>

        <v-card-text v-if="!competition.teams?.length" class="text-medium-emphasis">
          Aún no hay equipos guardados para esta liga.
        </v-card-text>

        <v-list v-else lines="two">
          <v-list-item
            v-for="team in competition.teams"
            :key="team.id"
            :to="{ name: 'team-detail', params: { team: team.id } }"
          >
            <template #prepend>
              <v-avatar color="primary" rounded="lg">
                <v-img v-if="team.crest" :src="team.crest" alt="" />
                <v-icon v-else icon="mdi-shield-outline" />
              </v-avatar>
            </template>
            <v-list-item-title>{{ team.name }}</v-list-item-title>
            <v-list-item-subtitle>{{ team.short_name ?? 'Sin nombre corto' }}</v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </v-card>
    </template>
  </v-container>
</template>
