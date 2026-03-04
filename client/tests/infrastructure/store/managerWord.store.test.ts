import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import { useManagerWordStore } from '@/infrastructure/store/managerWord.store'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn()
    }
}))

describe('managerWordStore - basics', () => {
    let store

    beforeEach(() => {
        store = useManagerWordStore(
            createTestingPinia({
                stubActions: false,
                createSpy: vi.fn
            })
        )
    })

    it('initializes with correct default values', () => {
        expect(store.words).toEqual([])
        expect(store.searchGerman).toBe('')
        expect(store.searchNorsk).toBe('')
        expect(store.loading).toBe(false)
        expect(store.error).toBeNull()
    })

    it('updateSearchTerm: updates search term for NO', () => {
        store.updateSearchTerm('foo', 'NO')
        expect(store.searchNorsk).toBe('foo')
        expect(store.searchGerman).toBe('')
    })

    it('updateSearchTerm: updates search term for DE', () => {
        store.updateSearchTerm('bar', 'DE')
        expect(store.searchGerman).toBe('bar')
        expect(store.searchNorsk).toBe('')
    })

    it('updateSearchTerm: clears search term for "none"', () => {
        store.searchGerman = 'test'
        store.searchNorsk = 'prøve'
        store.updateSearchTerm('ignored', 'none')
        expect(store.searchGerman).toBe('')
        expect(store.searchNorsk).toBe('')
    })
})

describe('managerWordStore - computedFilteredWords', () => {
    let store

    beforeEach(() => {
        store = useManagerWordStore(
            createTestingPinia({
                stubActions: false,
                createSpy: vi.fn
            })
        )

        store.words = [
            { id: 1, german: 'Apfel', norsk: 'eple' },
            { id: 2, german: 'Hund', norsk: 'hund' },
            { id: 3, german: 'Katze', norsk: 'katt' }
        ]
    })

    it('filters by german word', () => {
        store.searchGerman = 'hund'
        store.searchNorsk = ''
        expect(store.computedFilteredWords).toEqual([{ id: 2, german: 'Hund', norsk: 'hund' }])
    })

    it('filters by norsk word', () => {
        store.searchGerman = ''
        store.searchNorsk = 'eple'
        expect(store.computedFilteredWords).toEqual([{ id: 1, german: 'Apfel', norsk: 'eple' }])
    })

    it('returns empty if no match', () => {
        store.searchNorsk = 'xyz'
        expect(store.computedFilteredWords).toEqual([])
    })
})

describe('managerWordStore - actions with mocked httpClient', () => {
    let store

    beforeEach(() => {
        store = useManagerWordStore(
            createTestingPinia({
                stubActions: false,
                createSpy: vi.fn
            })
        )
    })

    afterEach(() => {
        vi.clearAllMocks()
    })

    it('fetches and sets words on getAll()', async () => {
        const mockWords = [{ id: 1, german: 'Apfel', norsk: 'eple' }]
        httpClient.get.mockResolvedValue(mockWords)

        await store.getAll()

        expect(httpClient.get).toHaveBeenCalledWith(expect.stringContaining('/manage/words'))
        expect(store.words).toEqual(mockWords)
        expect(store.loading).toBe(false)
    })

    it('sets error on failure on getAll()', async () => {
        httpClient.get.mockRejectedValue(new Error('Server Error'))

        await store.getAll()

        expect(store.error).toBe('Server Error')
        expect(store.loading).toBe(false)
    })

    it('posts and then fetches all on add()', async () => {
        const newWord = { german: 'Baum', norsk: 'tre' }
        httpClient.post.mockResolvedValue({})
        httpClient.get.mockResolvedValue([newWord])

        await store.add(newWord)

        expect(httpClient.post).toHaveBeenCalledWith(
            expect.stringContaining('/manage/words'),
            newWord
        )
        expect(httpClient.get).toHaveBeenCalled()
    })

    it('removes word after API call on delete()', async () => {
        store.words = [
            { id: 1, german: 'Apfel', norsk: 'eple' },
            { id: 2, german: 'Katze', norsk: 'katt' }
        ]
        httpClient.delete.mockResolvedValue({})

        await store.delete(1)

        expect(httpClient.delete).toHaveBeenCalledWith(expect.stringContaining('/1'))
        expect(store.words).toEqual([{ id: 2, german: 'Katze', norsk: 'katt' }])
    })

    it('sends PUT request when no duplicates exist on update()', async () => {
        store.words = [
            { id: 1, german: 'Alt', norsk: 'gammel' },
            { id: 2, german: 'Neu', norsk: 'ny' }
        ]
        const updatedWord = { id: 2, german: 'Neu', norsk: 'nytt' }
        httpClient.put.mockResolvedValue({})

        await store.update(updatedWord)

        expect(httpClient.put).toHaveBeenCalledWith(expect.stringContaining('/2'), {
            german: 'Neu',
            norsk: 'nytt'
        })
        expect(store.error).toBeNull()
    })

    it('throws error if duplicate german word exists on update()', async () => {
        store.words = [
            { id: 1, german: 'Alt', norsk: 'gammel' },
            { id: 2, german: 'Neu', norsk: 'ny' }
        ]
        const duplicateWord = { id: 3, german: 'Neu', norsk: 'noe annet' }

        await expect(store.update(duplicateWord)).rejects.toThrow(
            'Das deutsche Wort "Neu" ist bereits vorhanden.'
        )

        expect(httpClient.put).not.toHaveBeenCalled()
    })
})

