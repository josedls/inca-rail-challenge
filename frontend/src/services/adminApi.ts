import { request } from './httpClient'

export interface AdminUser {
  id: number
  name: string
  email: string
  is_active: boolean
  roles: string[]
}

export interface AdminRole {
  id: number
  name: string
  guard_name: string
  permissions: string[]
}

export interface AdminPermission {
  id: number
  name: string
  guard_name: string
}

interface CollectionResponse<T> { data: T[] }

export async function listAdminUsers(): Promise<AdminUser[]> {
  return (await request<CollectionResponse<AdminUser>>('/users')).data
}

export async function createAdminUser(payload: {
  name: string
  email: string
  password: string
  password_confirmation: string
  roles: string[]
}): Promise<AdminUser> {
  return (await request<{ data: AdminUser }>('/users', {
    method: 'POST',
    body: JSON.stringify(payload),
  })).data
}

export async function updateAdminUser(id: number, payload: {
  name: string
  email: string
  password?: string
  password_confirmation?: string
  roles: string[]
}): Promise<AdminUser> {
  return (await request<{ data: AdminUser }>(`/users/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })).data
}

export async function changeAdminUserStatus(id: number, isActive: boolean): Promise<AdminUser> {
  return (await request<{ data: AdminUser }>(`/users/${id}/status`, {
    method: 'PATCH',
    body: JSON.stringify({ is_active: isActive }),
  })).data
}

export async function listAdminRoles(): Promise<AdminRole[]> {
  return (await request<CollectionResponse<AdminRole>>('/roles')).data
}

export async function createAdminRole(name: string, permissions: string[]): Promise<AdminRole> {
  return (await request<{ data: AdminRole }>('/roles', {
    method: 'POST',
    body: JSON.stringify({ name, permissions }),
  })).data
}

export async function updateAdminRole(id: number, name: string, permissions: string[]): Promise<AdminRole> {
  return (await request<{ data: AdminRole }>(`/roles/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ name, permissions }),
  })).data
}

export async function deleteAdminRole(id: number): Promise<void> {
  await request<void>(`/roles/${id}`, { method: 'DELETE' })
}

export async function listAdminPermissions(): Promise<AdminPermission[]> {
  return (await request<CollectionResponse<AdminPermission>>('/permissions')).data
}

export async function createAdminPermission(name: string): Promise<AdminPermission> {
  return (await request<{ data: AdminPermission }>('/permissions', {
    method: 'POST',
    body: JSON.stringify({ name }),
  })).data
}

export async function updateAdminPermission(id: number, name: string): Promise<AdminPermission> {
  return (await request<{ data: AdminPermission }>(`/permissions/${id}`, {
    method: 'PATCH',
    body: JSON.stringify({ name }),
  })).data
}

export async function deleteAdminPermission(id: number): Promise<void> {
  await request<void>(`/permissions/${id}`, { method: 'DELETE' })
}
