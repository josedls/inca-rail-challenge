<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { ApiError } from '../../services/httpClient'
import { listTeams, type Team } from '../../services/footballApi'

const teams = ref<Team[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const search = ref('')
const page = ref(1)
const itemsPerPage = ref(12)
const filteredTeams = computed(() => {
  const normalizedSearch = search.value.trim().toLocaleLowerCase()

  return teams.value.filter((team) => [team.name, team.short_name]
    .filter(Boolean)
    .some((value) => value!.toLocaleLowerCase().includes(normalizedSearch)))
})
const pageCount = computed(() => Math.max(1, Math.ceil(filteredTeams.value.length / itemsPerPage.value)))
const visibleTeams = computed(() => {
  const start = (page.value - 1) * itemsPerPage.value

  return filteredTeams.value.slice(start, start + itemsPerPage.value)
})

watch([search, itemsPerPage], () => {
  page.value = 1
})

onMounted(loadTeams)

async function loadTeams(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    teams.value = await listTeams()
  } catch (error) {
    errorMessage.value = getTeamsErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}

function getTeamsErrorMessage(error: unknown): string {
  if (error instanceof ApiError && error.status === 403) {
    return 'No tienes permiso para consultar los equipos.'
  }

  return 'No pudimos cargar los equipos. Inténtalo nuevamente.'
}
</script>

<template>
  <v-container class="py-8">
    <div class="d-flex flex-wrap align-center justify-space-between ga-4 mb-6">
      <div>
        <h1 class="text-h4 mb-1">Equipos</h1>
        <p class="text-medium-emphasis">Consulta los equipos guardados localmente.</p>
      </div>

      <v-btn :loading="isLoading" variant="outlined" @click="loadTeams">
        Actualizar
      </v-btn>
    </div>

    <v-alert v-if="errorMessage" class="mb-6" type="error">
      {{ errorMessage }}
    </v-alert>

    <div v-if="isLoading" class="d-flex justify-center py-12">
      <v-progress-circular color="primary" indeterminate />
    </div>

    <v-card v-else-if="teams.length === 0" class="pa-8 text-center">
      <v-icon icon="mdi-shield-outline" size="48" class="mb-4" />
      <h2 class="text-h6 mb-2">Aún no hay equipos guardados</h2>
      <p class="text-medium-emphasis">Importa una liga para conservar sus equipos.</p>
    </v-card>

    <template v-else>
      <v-card class="mb-6 pa-4">
        <v-row>
          <v-col cols="12" md="9">
            <v-text-field
              v-model="search"
              clearable
              hide-details
              label="Buscar equipo"
              prepend-inner-icon="mdi-magnify"
              placeholder="Nombre o nombre corto"
              @click:clear="search = ''"
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="itemsPerPage"
              :items="[6, 12, 24]"
              hide-details
              label="Por página"
            />
          </v-col>
        </v-row>
      </v-card>

      <v-card v-if="filteredTeams.length === 0" class="pa-8 text-center">
        <v-icon icon="mdi-filter-off-outline" size="48" class="mb-4" />
        <h2 class="text-h6 mb-2">No encontramos equipos</h2>
        <p class="text-medium-emphasis">Prueba con otro término de búsqueda.</p>
      </v-card>

      <v-row v-else>
      <v-col v-for="team in visibleTeams" :key="team.id" cols="12" sm="6" lg="4">
        <v-card
          class="h-100"
          :to="{ name: 'team-detail', params: { team: team.id } }"
          link
        >
          <v-card-item>
            <template #prepend>
              <v-avatar color="primary" rounded="lg">
                <v-img v-if="team.crest" :src="team.crest" alt="" />
                <v-icon v-else icon="mdi-shield-outline" />
              </v-avatar>
            </template>
            <v-card-title>{{ team.name }}</v-card-title>
            <v-card-subtitle>{{ team.short_name ?? 'Sin nombre corto' }}</v-card-subtitle>
          </v-card-item>
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
