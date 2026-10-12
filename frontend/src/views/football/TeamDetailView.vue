<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ApiError } from '../../services/httpClient'
import { getTeam, type Team } from '../../services/footballApi'

const route = useRoute()
const router = useRouter()
const team = ref<Team | null>(null)
const isLoading = ref(true)
const errorMessage = ref('')

onMounted(loadTeam)

async function loadTeam(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    team.value = await getTeam(Number(route.params.team))
  } catch (error) {
    errorMessage.value = getTeamErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

function getTeamErrorMessage(error: unknown): string {
  if (error instanceof ApiError) {
    if (error.status === 403) return 'No tienes permiso para consultar este equipo.'
    if (error.status === 404) return 'El equipo solicitado no existe en el catálogo guardado.'
  }

  return 'No pudimos cargar el detalle del equipo. Inténtalo nuevamente.'
}
</script>

<template>
  <v-container class="py-8">
    <v-btn
      class="mb-6"
      prepend-icon="mdi-arrow-left"
      variant="text"
      @click="router.push({ name: 'teams' })"
    >
      Volver a equipos
    </v-btn>

    <div v-if="isLoading" class="d-flex justify-center py-12">
      <v-progress-circular color="primary" indeterminate />
    </div>

    <v-alert v-else-if="errorMessage" type="error">
      {{ errorMessage }}
    </v-alert>

    <template v-else-if="team">
      <div class="d-flex flex-wrap align-center ga-4 mb-6">
        <v-avatar color="primary" rounded="lg" size="64">
          <v-img v-if="team.crest" :src="team.crest" alt="" />
          <v-icon v-else icon="mdi-shield-outline" size="32" />
        </v-avatar>

        <div>
          <h1 class="text-h4 mb-1">{{ team.name }}</h1>
          <p class="text-medium-emphasis">{{ team.short_name ?? 'Sin nombre corto' }}</p>
        </div>
      </div>

      <v-card>
        <v-card-title>Jugadores guardados</v-card-title>

        <v-card-text v-if="!team.players?.length" class="text-medium-emphasis">
          Este equipo todavía no tiene jugadores guardados.
        </v-card-text>

        <v-list v-else lines="two">
          <v-list-item v-for="player in team.players" :key="player.id">
            <template #prepend>
              <v-avatar color="secondary">
                <v-icon icon="mdi-account-outline" />
              </v-avatar>
            </template>
            <v-list-item-title>{{ player.name }}</v-list-item-title>
            <v-list-item-subtitle>
              {{ player.position ?? 'Posición no disponible' }} ·
              Camiseta {{ player.shirt_number ?? 'no disponible' }}
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </v-card>
    </template>
  </v-container>
</template>
