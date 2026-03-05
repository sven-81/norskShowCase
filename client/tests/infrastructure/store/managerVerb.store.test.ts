import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { useManagerVerbStore } from '@/infrastructure/store/managerVerb.store'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn()
    }
}))

describe('managerVerbStore', () => {
    let store: ReturnType<typeof useManagerVerbStore>

    beforeEach(() => {
        setActivePinia(createPinia())
        store = useManagerVerbStore()

        vi.mocked(httpClient.get).mockReset()
        vi.mocked(httpClient.post).mockReset()
        vi.mocked(httpClient.put).mockReset()
        vi.mocked(httpClient.delete).mockReset()
    })

    it('filters verbs by norsk', () => {
        store.verbs = [
            { id: 1, german: 'laufen', norsk: 'løpe', norskPresent: '', norskPast: '', norskPastPerfect: '' },
            { id: 2, german: 'singen', norsk: 'synge', norskPresent: '', norskPast: '', norskPastPerfect: '' }
        ]
        store.searchNorsk = 'lø'
        store.searchGerman = ''
        expect(store.computedFilteredVerbs.length).toBe(1)
        expect(store.computedFilteredVerbs[0].norsk).toBe('løpe')
    })

    it('filters verbs to German if searchNorsk is empty', () => {
        store.verbs = [
            { id: 1, german: 'laufen', norsk: 'løpe', norskPresent: '', norskPast: '', norskPastPerfect: '' },
            { id: 2, german: 'singen', norsk: 'synge', norskPresent: '', norskPast: '', norskPastPerfect: '' }
        ]
        store.searchNorsk = ''
        store.searchGerman = 'sing'
        expect(store.computedFilteredVerbs.length).toBe(1)
        expect(store.computedFilteredVerbs[0].german).toBe('singen')
    })

    it('sets correct search terms by updateSearchTerm', () => {
        store.updateSearchTerm('test', 'none')
        expect(store.searchGerman).toBe('')
        expect(store.searchNorsk).toBe('')

        store.updateSearchTerm('norwegisch', 'NO')
        expect(store.searchNorsk).toBe('norwegisch')
        expect(store.searchGerman).toBe('')

        store.updateSearchTerm('deutsch', 'DE')
        expect(store.searchGerman).toBe('deutsch')
        expect(store.searchNorsk).toBe('')
    })

    it('loads verbs and handles errors on getAll()', async () => {
        const verbs = [{ id: 1, german: 'laufen', norsk: 'løpe', norskPresent: '', norskPast: '', norskPastPerfect: '' }]
        vi.mocked(httpClient.get).mockResolvedValue(verbs)

        await store.getAll()

        expect(store.verbs).toEqual(verbs)
        expect(store.loading).toBe(false)
        expect(store.error).toBeNull()

        vi.mocked(httpClient.get).mockRejectedValue(new Error('Netzwerkfehler'))

        await store.getAll()

        expect(store.error).toBe('Netzwerkfehler')
        expect(store.loading).toBe(false)
    })

    it('adds verbs and calls getAll on add()', async () => {
        vi.mocked(httpClient.post).mockResolvedValue(undefined)
        const getAllSpy = vi.spyOn(store, 'getAll').mockResolvedValue(undefined)

        await store.add({ german: 'laufen', norsk: 'løpe', norskPresent: 'løper', norskPast: 'løp', norskPastPerfect: 'har løpt' })

        expect(httpClient.post).toHaveBeenCalled()
        expect(getAllSpy).toHaveBeenCalled()

        getAllSpy.mockRestore()
    })

    it('removes verb and calls httpClient.delete on delete()', async () => {
        store.verbs = [
            { id: 1, german: 'laufen', norsk: 'løpe', norskPresent: '', norskPast: '', norskPastPerfect: '' },
            { id: 2, german: 'singen', norsk: 'synge', norskPresent: '', norskPast: '', norskPastPerfect: '' }
        ]
        vi.mocked(httpClient.delete).mockResolvedValue(undefined)

        await store.delete(1)

        expect(httpClient.delete).toHaveBeenCalledWith(expect.stringContaining('/manage/verbs/1'))
        expect(store.verbs.length).toBe(1)
        expect(store.verbs[0].id).toBe(2)
    })

    it('throws error on duplicate on update()', async () => {
        store.verbs = [
            { id: 1, german: 'laufen', norsk: 'løpe', norskPresent: '', norskPast: '', norskPastPerfect: '' },
            { id: 2, german: 'singen', norsk: 'synge', norskPresent: '', norskPast: '', norskPastPerfect: '' }
        ]

        await expect(() =>
            store.update({ id: 2, german: 'laufen', norsk: 'løpe', norskPresent: '', norskPast: '', norskPastPerfect: '' })
        ).rejects.toThrow('Das Verb "laufen" ist bereits vorhanden.')
    })

    it('calls httpClient.put if the verb is valid on update()', async () => {
        store.verbs = [
            { id: 1, german: 'laufen', norsk: 'løpe', norskPresent: '', norskPast: '', norskPastPerfect: '' },
            { id: 2, german: 'singen', norsk: 'synge', norskPresent: '', norskPast: '', norskPastPerfect: '' }
        ]

        vi.mocked(httpClient.put).mockResolvedValue(undefined)
        store.error = 'Fehler'

        await store.update({ id: 2, german: 'tanzen', norsk: 'danse', norskPresent: 'danser', norskPast: 'danset', norskPastPerfect: 'har danset' })

        expect(store.error).toBeNull()
        expect(httpClient.put).toHaveBeenCalledWith(
            expect.stringContaining('/manage/verbs/2'),
            expect.objectContaining({ german: 'tanzen' })
        )
    })

    it('sets error to zero on clearError()', () => {
        store.error = 'Fehler'
        store.clearError()
        expect(store.error).toBeNull()
    })
})

