import { describe, expect, it } from 'vitest'
import { Word } from '@/domain/model/Word'

describe('Word', () => {
    it('matches correct input case-insensitively', () => {
        const word = new Word(1, 'Hund', 'hund')

        expect(word.matches('hund')).toBe(true)
        expect(word.matches('Hund')).toBe(true)
        expect(word.matches('HUND')).toBe(true)
    })

    it('does not match incorrect input', () => {
        const word = new Word(1, 'Hund', 'hund')

        expect(word.matches('katt')).toBe(false)
        expect(word.matches('')).toBe(false)
    })

    it('creates from response data', () => {
        const word = Word.fromResponse({ id: 42, german: 'Katze', norsk: 'katt' })

        expect(word.id).toBe(42)
        expect(word.german).toBe('Katze')
        expect(word.norsk).toBe('katt')
    })
})

