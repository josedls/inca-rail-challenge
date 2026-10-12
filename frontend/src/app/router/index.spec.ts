import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

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

import { router } from './index'

describe('application router', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('redirects unauthenticated users to login', async () => {
    await router.push('/')
    await router.isReady()

    expect(router.currentRoute.value.name).toBe('login')
  })
})
