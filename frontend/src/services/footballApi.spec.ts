import { beforeEach, describe, expect, it, vi } from 'vitest'
import { importCompetition, listCompetitions } from './footballApi'
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

    expect(result.syncQueued).toBe(false)
    expect(result.items[0]?.name).toBe('Premier League')
  })

  it('detects when the initial catalog sync was queued', async () => {
    vi.mocked(request).mockResolvedValue({ data: { status: 'queued' } })

    const result = await listCompetitions()

    expect(result).toEqual({ items: [], syncQueued: true })
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
})
