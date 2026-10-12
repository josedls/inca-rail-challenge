import { beforeEach, describe, expect, it, vi } from 'vitest'
import { getCurrentUser, login, logout } from './authApi'
import { request } from './httpClient'

vi.mock('./httpClient', () => ({
  request: vi.fn(),
}))

describe('authApi', () => {
  beforeEach(() => {
    vi.mocked(request).mockReset()
  })

  it('authenticates with the API login contract', async () => {
    vi.mocked(request).mockResolvedValue({
      data: {
        token: 'token-for-valeria',
        token_type: 'Bearer',
        user: {
          id: 1,
          name: 'Valeria Quispe',
          email: 'valeria.quispe@inca-rail.test',
          is_active: true,
          roles: ['viewer'],
          permissions: ['competitions.view'],
        },
      },
    })

    const session = await login({
      email: 'valeria.quispe@inca-rail.test',
      password: 'Railway#2026',
    })

    expect(request).toHaveBeenCalledWith('/login', {
      method: 'POST',
      body: JSON.stringify({
        email: 'valeria.quispe@inca-rail.test',
        password: 'Railway#2026',
      }),
    })
    expect(session.token).toBe('token-for-valeria')
    expect(session.user.roles).toEqual(['viewer'])
  })

  it('retrieves the current user', async () => {
    vi.mocked(request).mockResolvedValue({
      data: {
        id: 1,
        name: 'Valeria Quispe',
        email: 'valeria.quispe@inca-rail.test',
        is_active: true,
        roles: ['viewer'],
        permissions: ['competitions.view'],
      },
    })

    const user = await getCurrentUser()

    expect(request).toHaveBeenCalledWith('/me')
    expect(user.email).toBe('valeria.quispe@inca-rail.test')
  })

  it('revokes the current session', async () => {
    vi.mocked(request).mockResolvedValue(undefined)

    await logout()

    expect(request).toHaveBeenCalledWith('/logout', { method: 'POST' })
  })
})
