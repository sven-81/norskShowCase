import { afterEach, describe, expect, it, vi } from 'vitest'
import { ManagerWordHttpAdapter } from '@/infrastructure/http/ManagerWordHttpAdapter'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn()
    }
}))

describe('ManagerWordHttpAdapter', () => {
    const adapter = new ManagerWordHttpAdapter()

    afterEach(() => {
        vi.clearAllMocks()
    })

    it('fetches all words and maps them to Word models', async () => {
        const rawData = [
            { id: 1, german: 'Hund', norsk: 'hund' },
            { id: 2, german: 'Katze', norsk: 'katt' }
        ]
        vi.mocked(httpClient.get).mockResolvedValue(rawData)

        const result = await adapter.getAll()

        expect(httpClient.get).toHaveBeenCalledWith(expect.stringContaining('/manage/words'))
        expect(result).toHaveLength(2)
        expect(result[0].id).toBe(1)
        expect(result[0].german).toBe('Hund')
        expect(result[0].norsk).toBe('hund')
        expect(result[1].id).toBe(2)
    })

    it('sends POST request with word data on add', async () => {
        vi.mocked(httpClient.post).mockResolvedValue(undefined)
        const wordData = { german: 'Baum', norsk: 'tre' }

        await adapter.add(wordData)

        expect(httpClient.post).toHaveBeenCalledWith(
            expect.stringContaining('/manage/words'),
            wordData
        )
    })

    it('sends PUT request with id and word data on update', async () => {
        vi.mocked(httpClient.put).mockResolvedValue(undefined)
        const wordData = { german: 'Baum', norsk: 'tre' }

        await adapter.update(5, wordData)

        expect(httpClient.put).toHaveBeenCalledWith(
            expect.stringContaining('/manage/words/5'),
            wordData
        )
    })

    it('sends DELETE request with id on remove', async () => {
        vi.mocked(httpClient.delete).mockResolvedValue(undefined)

        await adapter.remove(3)

        expect(httpClient.delete).toHaveBeenCalledWith(
            expect.stringContaining('/manage/words/3')
        )
    })
})

