import { afterEach, describe, expect, it, vi } from 'vitest'
import { TrainerWordHttpAdapter } from '@/infrastructure/http/TrainerWordHttpAdapter'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        get: vi.fn(),
        patch: vi.fn()
    }
}))

describe('TrainerWordHttpAdapter', () => {
    const adapter = new TrainerWordHttpAdapter()

    afterEach(() => {
        vi.clearAllMocks()
    })

    it('fetches a random word and maps it to Word model', async () => {
        const rawData = { id: 5, german: 'Apfel', norsk: 'eple' }
        vi.mocked(httpClient.get).mockResolvedValue(rawData)

        const result = await adapter.getRandomWord()

        expect(httpClient.get).toHaveBeenCalledWith(expect.stringContaining('/train/words'))
        expect(result.id).toBe(5)
        expect(result.german).toBe('Apfel')
        expect(result.norsk).toBe('eple')
    })

    it('sends PATCH request with id on markAsTrained', async () => {
        vi.mocked(httpClient.patch).mockResolvedValue(undefined)

        await adapter.markAsTrained(5)

        expect(httpClient.patch).toHaveBeenCalledWith(
            expect.stringContaining('/train/words/5')
        )
    })
})

