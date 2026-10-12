import { request } from './httpClient'

export interface Competition {
  id: number
  name: string
  code: string | null
  emblem: string | null
  import_status: 'available' | 'processing' | 'completed' | 'failed' | null
  import_error: string | null
  last_synced_at: string | null
  import_progress: {
    total: number
    completed: number
    failed: number
    pending: number
    percentage: number
  }
  teams?: Team[]
}

export interface Team {
  id: number
  name: string
  short_name: string | null
  crest: string | null
  players?: Player[]
}

export interface Player {
  id: number
  name: string
  position: string | null
  shirt_number: number | null
  date_of_birth: string | null
  nationality: string | null
}

interface TeamCollectionResponse {
  data: Team[]
}

interface PlayerCollectionResponse {
  data: Player[]
}

interface CompetitionCollectionResponse {
  data: Competition[] | { status: 'queued' }
}

interface ImportCompetitionResponse {
  data: {
    competition_id: number
    status: 'queued' | 'processing' | 'completed'
  }
}

export interface CompetitionListResult {
  items: Competition[]
  syncQueued: boolean
}

export async function listCompetitions(): Promise<CompetitionListResult> {
  const response = await request<CompetitionCollectionResponse>('/competitions')

  if (!Array.isArray(response.data)) {
    return { items: [], syncQueued: response.data.status === 'queued' }
  }

  return { items: response.data, syncQueued: false }
}

export async function getCompetition(competitionId: number): Promise<Competition> {
  const response = await request<{ data: Competition }>(`/competitions/${competitionId}`)

  return response.data
}

export async function importCompetition(competitionId: number): Promise<ImportCompetitionResponse['data']> {
  const response = await request<ImportCompetitionResponse>(
    `/competitions/${competitionId}/import`,
    { method: 'POST' },
  )

  return response.data
}

export async function listTeams(): Promise<Team[]> {
  const response = await request<TeamCollectionResponse>('/team')

  return response.data
}

export async function getTeam(teamId: number): Promise<Team> {
  const response = await request<{ data: Team }>(`/team/${teamId}`)

  return response.data
}

export async function listPlayers(): Promise<Player[]> {
  const response = await request<PlayerCollectionResponse>('/players')

  return response.data
}
