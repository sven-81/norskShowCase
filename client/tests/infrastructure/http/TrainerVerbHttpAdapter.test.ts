import { afterEach, describe, expect, it, vi } from 'vitest'
import { TrainerVerbHttpAdapter } from '@/infrastructure/http/TrainerVerbHttpAdapter'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        get: vi.fn(),
        patch: vi.fn()
    }
}))

describe('TrainerVerbHttpAdapter', () => {
    const adapter = new TrainerVerbHttpAdapter()

    afterEach(() => {
        vi.clearAllMocks()
    })

    it('fetches a random verb and maps it to Verb model', async () => {
        const rawData = {
            id: 3,
            german: 'laufen',
            norsk: 'løpe',
            norskPresent: 'løper',
            norskPast: 'løp',
            norskPastPerfect: 'har løpt'
        }
        vi.mocked(httpClient.get).mockResolvedValue(rawData)

        const result = await adapter.getRandomVerb()

        expect(httpClient.get).toHaveBeenCalledWith(expect.stringContaining('/train/verbs'))
        expect(result.id).toBe(3)
        expect(result.german).toBe('laufen')
        expect(result.norsk).toBe('løpe')
        expect(result.norskPresent).toBe('løper')
        expect(result.norskPast).toBe('løp')
        expect(result.norskPastPerfect).toBe('har løpt')
    })

    it('sends PATCH request with id on markAsTrained', async () => {
        vi.mocked(httpClient.patch).mockResolvedValue(undefined)

        await adapter.markAsTrained(3)

        expect(httpClient.patch).toHaveBeenCalledWith(
            expect.stringContaining('/train/verbs/3')
        )
    })
})

