import { request } from './httpClient'
import type { UserSession } from '../types/auth'

interface LoginResponse {
  data: {
    token: string
    token_type: string
    user: UserSession
  }
}

export interface LoginCredentials {
  email: string
  password: string
}

export async function login(credentials: LoginCredentials): Promise<LoginResponse['data']> {
  const response = await request<LoginResponse>('/login', {
    method: 'POST',
    body: JSON.stringify(credentials),
  })

  return response.data
}

export async function getCurrentUser(): Promise<UserSession> {
  const response = await request<{ data: UserSession }>('/me')

  return response.data
}

export async function logout(): Promise<void> {
  await request<void>('/logout', { method: 'POST' })
}
