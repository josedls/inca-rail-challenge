import { clearStoredSession, getStoredToken, useAuthStore } from '../app/stores/auth'

export class ApiError extends Error {
  public readonly status: number
  public readonly details: unknown

  public constructor(status: number, message: string, details: unknown = null) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.details = details
  }
}

const apiUrl = import.meta.env.VITE_API_URL

export async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  if (!apiUrl) {
    throw new Error('The frontend API URL is not configured.')
  }

  const token = getStoredToken()

  const response = await fetch(`${apiUrl}${path}`, {
    ...options,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  })

  const body = await readResponseBody(response)

  if (!response.ok) {
    if (response.status === 401) {
      clearStoredSession()
      useAuthStore().clearSession()
    }

    const message = getErrorMessage(body, response.statusText)
    throw new ApiError(response.status, message, body)
  }

  return body as T
}

async function readResponseBody(response: Response): Promise<unknown> {
  const contentType = response.headers.get('content-type') ?? ''

  if (contentType.includes('application/json')) {
    return response.json()
  }

  return response.text()
}

function getErrorMessage(body: unknown, fallback: string): string {
  if (typeof body === 'object' && body !== null && 'message' in body) {
    const message = body.message

    if (typeof message === 'string') {
      return message
    }
  }

  return fallback
}
