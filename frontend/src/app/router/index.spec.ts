import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { useAuthStore } from '../stores/auth'

vi.mock('../../views/HomeView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/auth/LoginView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../layouts/AppLayout.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/football/CompetitionsView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/football/CompetitionDetailView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/football/TeamsView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/football/TeamDetailView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/football/PlayersView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/admin/UsersView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/admin/RolesView.vue', () => ({
  default: { template: '<div />' },
}))

vi.mock('../../views/admin/PermissionsView.vue', () => ({
  default: { template: '<div />' },
}))

import { router } from './index'

describe('application router', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('redirects unauthenticated users to login', async () => {
    await router.push('/')
    await router.isReady()

    expect(router.currentRoute.value.name).toBe('login')
    expect(router.currentRoute.value.query.redirect).toBe('/')
  })

  it('redirects authenticated users without an admin permission', async () => {
    const authStore = useAuthStore()

    authStore.setSession('token', {
      id: 2,
      name: 'Viewer',
      email: 'viewer@example.test',
      is_active: true,
      roles: ['viewer'],
      permissions: ['competitions.view'],
    })

    await router.push({ name: 'admin-users' })

    expect(router.currentRoute.value.name).toBe('competitions')
  })
})
