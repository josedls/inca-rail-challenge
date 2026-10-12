<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../app/stores/auth'
import { login } from '../../services/authApi'
import { ApiError } from '../../services/httpClient'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isSubmitting = ref(false)

async function submit(): Promise<void> {
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    const session = await login({
      email: email.value,
      password: password.value,
    })

    authStore.setSession(session.token, session.user)
    await router.push(getPostLoginPath())
  } catch (error) {
    errorMessage.value = getLoginErrorMessage(error)
  } finally {
    isSubmitting.value = false
  }
}

function getPostLoginPath(): string {
  const redirect = route.query.redirect

  if (
    typeof redirect === 'string'
    && redirect !== '/'
    && redirect.startsWith('/')
    && !redirect.startsWith('//')
  ) {
    return redirect
  }

  return '/competitions'
}

function getLoginErrorMessage(error: unknown): string {
  if (error instanceof ApiError && error.status === 422) {
    return 'El correo o la contraseña no son válidos.'
  }

  return 'No pudimos iniciar sesión. Inténtalo nuevamente.'
}
</script>

<template>
  <v-main>
    <v-container class="d-flex align-center justify-center fill-height py-10">
      <v-card class="pa-6" max-width="440" width="100%">
        <v-card-title class="text-h5 px-0">Iniciar sesión</v-card-title>
        <v-card-text class="px-0">
          <form @submit.prevent="submit">
            <v-text-field
              id="email"
              v-model="email"
              label="Correo electrónico"
              type="email"
              autocomplete="email"
              variant="outlined"
              required
            />

            <v-text-field
              id="password"
              v-model="password"
              label="Contraseña"
              type="password"
              autocomplete="current-password"
              variant="outlined"
              required
            />

            <v-alert v-if="errorMessage" class="mb-4" type="error" role="alert">
              {{ errorMessage }}
            </v-alert>

            <v-btn block color="primary" type="submit" :loading="isSubmitting">
              Ingresar
            </v-btn>
          </form>
        </v-card-text>
      </v-card>
    </v-container>
  </v-main>
</template>
