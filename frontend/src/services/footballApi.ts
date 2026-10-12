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

export async function importCompetition(competitionId: number): Promise<ImportCompetitionResponse['data']> {
  const response = await request<ImportCompetitionResponse>(
    `/competitions/${competitionId}/import`,
    { method: 'POST' },
  )

  return response.data
}
