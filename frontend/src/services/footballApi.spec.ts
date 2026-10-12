import { beforeEach, describe, expect, it, vi } from 'vitest'
import {
  getCompetition,
  getTeam,
  importCompetition,
  listCompetitions,
  listPlayers,
  listTeams,
  syncCompetitions,
} from './footballApi'
import { request } from './httpClient'

vi.mock('./httpClient', () => ({
  request: vi.fn(),
}))

describe('footballApi', () => {
  beforeEach(() => {
    vi.mocked(request).mockReset()
  })

  it('maps a saved competition collection', async () => {
    vi.mocked(request).mockResolvedValue({
      data: [
        {
          id: 2021,
          name: 'Premier League',
          code: 'PL',
          emblem: null,
          import_status: 'completed',
          import_error: null,
          last_synced_at: '2026-10-12T03:00:00.000000Z',
        },
      ],
    })

    const result = await listCompetitions()

    expect(result.items[0]?.name).toBe('Premier League')
  })

  it('lists an empty saved competition collection without syncing', async () => {
    vi.mocked(request).mockResolvedValue({ data: [] })

    const result = await listCompetitions()

    expect(result).toEqual({ items: [] })
  })

  it('requests the initial catalog synchronization explicitly', async () => {
    vi.mocked(request).mockResolvedValue({ data: { status: 'queued' } })

    await syncCompetitions()

    expect(request).toHaveBeenCalledWith('/competitions/sync', { method: 'POST' })
  })

  it('gets a saved competition detail', async () => {
    vi.mocked(request).mockResolvedValue({
      data: {
        id: 2021,
        name: 'Premier League',
        code: 'PL',
        emblem: null,
        import_status: 'completed',
        import_error: null,
        last_synced_at: '2026-10-12T03:00:00.000000Z',
        import_progress: {
          total: 20,
          completed: 20,
          failed: 0,
          pending: 0,
          percentage: 100,
        },
      },
    })

    const result = await getCompetition(2021)

    expect(request).toHaveBeenCalledWith('/competitions/2021')
    expect(result.name).toBe('Premier League')
  })

  it('requests a competition import', async () => {
    vi.mocked(request).mockResolvedValue({
      data: { competition_id: 2021, status: 'queued' },
    })

    const result = await importCompetition(2021)

    expect(request).toHaveBeenCalledWith('/competitions/2021/import', {
      method: 'POST',
    })
    expect(result.status).toBe('queued')
  })

  it('lists saved teams', async () => {
    vi.mocked(request).mockResolvedValue({
      data: [
        { id: 64, name: 'Liverpool FC', short_name: 'Liverpool', crest: null },
      ],
    })

    const result = await listTeams()

    expect(request).toHaveBeenCalledWith('/team')
    expect(result[0]?.name).toBe('Liverpool FC')
  })

  it('gets a saved team with its players', async () => {
    vi.mocked(request).mockResolvedValue({
      data: {
        id: 64,
        name: 'Liverpool FC',
        short_name: 'Liverpool',
        crest: null,
        players: [
          {
            id: 117,
            name: 'Mohamed Salah',
            position: 'Right Winger',
            shirt_number: 11,
            date_of_birth: '1992-06-15',
            nationality: 'Egypt',
          },
        ],
      },
    })

    const result = await getTeam(64)

    expect(request).toHaveBeenCalledWith('/team/64')
    expect(result.players?.[0]?.name).toBe('Mohamed Salah')
  })

  it('lists saved players', async () => {
    vi.mocked(request).mockResolvedValue({
      data: [
        {
          id: 117,
          name: 'Mohamed Salah',
          position: 'Right Winger',
          shirt_number: 11,
          date_of_birth: '1992-06-15',
          nationality: 'Egypt',
        },
      ],
    })

    const result = await listPlayers()

    expect(request).toHaveBeenCalledWith('/players')
    expect(result[0]?.shirt_number).toBe(11)
  })
})
