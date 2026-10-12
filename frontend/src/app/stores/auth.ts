import { defineStore } from 'pinia'
import type { UserSession } from '../../types/auth'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    accessToken: null as string | null,
    user: null as UserSession | null,
  }),

  getters: {
    isAuthenticated: (state) => state.accessToken !== null,
  },

  actions: {
    setSession(accessToken: string, user: UserSession): void {
      this.accessToken = accessToken
      this.user = user
    },

    clearSession(): void {
      this.accessToken = null
      this.user = null
    },

    can(permission: string): boolean {
      return this.user?.permissions.includes(permission) ?? false
    },
  },
})
