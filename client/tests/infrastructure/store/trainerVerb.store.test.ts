import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { useTrainerVerbStore } from '@/infrastructure/store/trainerVerb.store'
import { httpClient } from '@/infrastructure/http/HttpClient'
import { useResultStore } from '@/infrastructure/store/trainingResult.store'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        get: vi.fn(),
        patch: vi.fn()
    }
}))

vi.mock('@/infrastructure/store/trainingResult.store', async () => {
    const actual = await vi.importActual<any>('@/infrastructure/store/trainingResult.store')
    return {
        ...actual,
        useResultStore: vi.fn()
    }
})

describe('trainerVerb.store', () => {
    let store: ReturnType<typeof useTrainerVerbStore>
    let resultStoreMock: any

    beforeEach(() => {
        setActivePinia(createPinia())
        store = useTrainerVerbStore()

        resultStoreMock = {
            success: vi.fn().mockResolvedValue(undefined),
            error: vi.fn().mockResolvedValue(undefined)
        }
        ;(useResultStore as unknown as vi.Mock).mockReturnValue(resultStoreMock)

        vi.mocked(httpClient.get).mockReset()
        vi.mocked(httpClient.patch).mockReset()
    })

    describe('random', () => {
        it('loads random verb and sets State', async () => {
            const verbData = {
                id: 1,
                german: 'laufen',
                norsk: 'løpe',
                norskPresent: 'løper',
                norskPast: 'løp',
                norskPastPerfect: 'har løpt'
            }
            vi.mocked(httpClient.get).mockResolvedValue(verbData)

            await store.random()

            expect(store.loading).toBe(false)
            expect(store.errorMessage).toBe('')
            expect(store.verb).toEqual(verbData)
            expect(store.id).toBe(verbData.id)
            expect(store.german).toBe(verbData.german)
            expect(store.norsk).toBe(verbData.norsk)
            expect(store.present).toBe(verbData.norskPresent)
            expect(store.past).toBe(verbData.norskPast)
            expect(store.pastPerfect).toBe(verbData.norskPastPerfect)
        })

        it('handles errors correctly and sets errorMessage', async () => {
            const error = new Error('No records found in database for: verbs')
            vi.mocked(httpClient.get).mockRejectedValue(error)

            await store.random()

            expect(store.loading).toBe(false)
            expect(store.verb).toBeNull()
            expect(store.errorMessage).toBe('In der Datenbank wurden keine Verben gefunden.')
        })

        it('sets default errorMessage for unknown error', async () => {
            const error = new Error('Some other error')
            vi.mocked(httpClient.get).mockRejectedValue(error)

            await store.random()

            expect(store.loading).toBe(false)
            expect(store.verb).toBeNull()
            expect(store.errorMessage).toBe(
                'Ein unbekannter Fehler ist aufgetreten. \n Det oppstod en ukjent feil.'
            )
        })
    })

    describe('evaluate', () => {
        beforeEach(() => {
            store.id = 1
            store.norsk = 'løpe'
            store.present = 'løper'
            store.past = 'løp'
            store.pastPerfect = 'har løpt'
        })

        it('calls success and patched API if input is correct', async () => {
            const input = {
                infinitive: 'løpe',
                present: 'løper',
                past: 'løp',
                pastPerfect: 'har løpt'
            }

            await store.evaluate(input)

            expect(resultStoreMock.success).toHaveBeenCalled()
            expect(httpClient.patch).toHaveBeenCalledWith(expect.stringContaining('/train/verbs/1'))
        })

        it('calls error with HTML table if input is incorrect', async () => {
            const input = {
                infinitive: 'gå',
                present: 'går',
                past: 'gikk',
                pastPerfect: 'har gått'
            }

            await store.evaluate(input)

            expect(resultStoreMock.error).toHaveBeenCalled()
            const messageArg = resultStoreMock.error.mock.calls[0][0]
            expect(messageArg).toContain('<table>')
            expect(messageArg).toContain('Imperativ')
            expect(messageArg).toContain('Falsch')
            expect(messageArg).toContain('Richtig')
            expect(messageArg, 'wrong input word').toContain('gå')
            expect(messageArg, 'correct word').toContain('løpe')
        })

        it('calls random at the end of evaluate', async () => {
            const spyRandom = vi.spyOn(store, 'random').mockResolvedValue(undefined)
            const input = {
                infinitive: 'wrong',
                present: 'wrong',
                past: 'wrong',
                pastPerfect: 'wrong'
            }

            await store.evaluate(input)
            expect(spyRandom).toHaveBeenCalled()
            spyRandom.mockRestore()
        })
    })
})

