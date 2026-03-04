import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useResultStore } from '@/infrastructure/store/trainingResult.store'

describe('ResultStore Tests', () => {
    let store

    beforeEach(() => {
        setActivePinia(createPinia())
        store = useResultStore()
    })

    afterEach(() => {
        vi.restoreAllMocks()
    })

    it('sets success result with a random smiley', async () => {
        await store.success()

        expect(store.resultMessage.startsWith('Alles richtig!')).toBe(true)
        expect(store.type).toBe('correct')
    })

    it('sets error result with a random sad smiley and validation message', async () => {
        const validationMessage = 'The input was wrong.'

        await store.error(validationMessage)

        expect(store.resultMessage.startsWith('<div>Oh no')).toBe(true)
        expect(store.resultMessage).toContain(validationMessage)
        expect(store.type).toBe('mistake')
    })

    it('clears result', () => {
        store.resultMessage = 'Some message'
        store.type = 'mistake'

        store.clear()

        expect(store.resultMessage).toBe('')
        expect(store.type).toBe('')
    })
})

