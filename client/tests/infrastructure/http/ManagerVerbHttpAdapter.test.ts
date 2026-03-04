import { afterEach, describe, expect, it, vi } from 'vitest'
import { ManagerVerbHttpAdapter } from '@/infrastructure/http/ManagerVerbHttpAdapter'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn()
    }
}))

describe('ManagerVerbHttpAdapter', () => {
    const adapter = new ManagerVerbHttpAdapter()

    afterEach(() => {
        vi.clearAllMocks()
    })

    it('fetches all verbs and maps them to Verb models', async () => {
        const rawData = [
            {
                id: 1,
                german: 'laufen',
                norsk: 'løpe',
                norskPresent: 'løper',
                norskPast: 'løp',
                norskPastPerfect: 'har løpt'
            }
        ]
        vi.mocked(httpClient.get).mockResolvedValue(rawData)

        const result = await adapter.getAll()

        expect(httpClient.get).toHaveBeenCalledWith(expect.stringContaining('/manage/verbs'))
        expect(result).toHaveLength(1)
        expect(result[0].id).toBe(1)
        expect(result[0].german).toBe('laufen')
        expect(result[0].norsk).toBe('løpe')
        expect(result[0].norskPresent).toBe('løper')
        expect(result[0].norskPast).toBe('løp')
        expect(result[0].norskPastPerfect).toBe('har løpt')
    })

    it('sends POST request with verb data on add', async () => {
        vi.mocked(httpClient.post).mockResolvedValue(undefined)
        const verbData = {
            german: 'spielen',
            norsk: 'spille',
            norskPresent: 'spiller',
            norskPast: 'spilte',
            norskPastPerfect: 'har spilt'
        }

        await adapter.add(verbData)

        expect(httpClient.post).toHaveBeenCalledWith(
            expect.stringContaining('/manage/verbs'),
            verbData
        )
    })

    it('sends PUT request with id and verb data on update', async () => {
        vi.mocked(httpClient.put).mockResolvedValue(undefined)
        const verbData = {
            german: 'spielen',
            norsk: 'spille',
            norskPresent: 'spiller',
            norskPast: 'spilte',
            norskPastPerfect: 'har spilt'
        }

        await adapter.update(7, verbData)

        expect(httpClient.put).toHaveBeenCalledWith(
            expect.stringContaining('/manage/verbs/7'),
            verbData
        )
    })

    it('sends DELETE request with id on remove', async () => {
        vi.mocked(httpClient.delete).mockResolvedValue(undefined)

        await adapter.remove(4)

        expect(httpClient.delete).toHaveBeenCalledWith(
            expect.stringContaining('/manage/verbs/4')
        )
    })
})

