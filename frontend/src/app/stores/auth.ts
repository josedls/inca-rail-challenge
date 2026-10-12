import { defineStore } from 'pinia'
import type { UserSession } from '../../types/auth'

const SESSION_STORAGE_KEY = 'inca-rail.session'

interface StoredSession {
  accessToken: string
  user: UserSession
}

export function getStoredToken(): string | null {
  return loadSession()?.accessToken ?? null
}

export function clearStoredSession(): void {
  localStorage.removeItem(SESSION_STORAGE_KEY)
}

function loadSession(): StoredSession | null {
  const storedSession = localStorage.getItem(SESSION_STORAGE_KEY)

  if (!storedSession) {
    return null
  }

  try {
    return JSON.parse(storedSession) as StoredSession
  } catch {
    clearStoredSession()
    return null
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    accessToken: loadSession()?.accessToken ?? null,
    user: loadSession()?.user ?? null,
  }),

  getters: {
    isAuthenticated: (state) => state.accessToken !== null,
  },

  actions: {
    setSession(accessToken: string, user: UserSession): void {
      this.accessToken = accessToken
      this.user = user
      localStorage.setItem(
        SESSION_STORAGE_KEY,
        JSON.stringify({ accessToken, user }),
      )
    },

    setUser(user: UserSession): void {
      this.user = user

      if (this.accessToken) {
        localStorage.setItem(
          SESSION_STORAGE_KEY,
          JSON.stringify({ accessToken: this.accessToken, user }),
        )
      }
    },

    clearSession(): void {
      this.accessToken = null
      this.user = null
      clearStoredSession()
    },

    can(permission: string): boolean {
      return this.user?.permissions.includes(permission) ?? false
    },
  },
})
