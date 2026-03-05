import { describe, expect, it } from 'vitest'
import { Verb } from '@/domain/model/Verb'

describe('Verb', () => {
    const verb = new Verb(1, 'spielen', 'spille', 'spiller', 'spilte', 'har spilt')

    it('evaluates all correct answers as correct', () => {
        const result = verb.evaluate({
            infinitive: 'spille',
            present: 'spiller',
            past: 'spilte',
            pastPerfect: 'har spilt'
        })

        expect(result.isCorrect).toBe(true)
    })

    it('evaluates case-insensitively', () => {
        const result = verb.evaluate({
            infinitive: 'Spille',
            present: 'SPILLER',
            past: 'Spilte',
            pastPerfect: 'Har Spilt'
        })

        expect(result.isCorrect).toBe(true)
    })

    it('evaluates incorrect answer as wrong', () => {
        const result = verb.evaluate({
            infinitive: 'wrong',
            present: 'spiller',
            past: 'spilte',
            pastPerfect: 'har spilt'
        })

        expect(result.isCorrect).toBe(false)
    })

    it('returns details with form names', () => {
        const result = verb.evaluate({
            infinitive: 'spille',
            present: 'spiller',
            past: 'spilte',
            pastPerfect: 'har spilt'
        })

        expect(result.details).toHaveLength(4)
        expect(result.details[0].form).toBe('Imperativ')
        expect(result.details[1].form).toBe('Gegenwart')
        expect(result.details[2].form).toBe('Vergangenheit')
        expect(result.details[3].form).toBe('Plusquamperfekt')
    })

    it('creates from response data', () => {
        const verb = Verb.fromResponse({
            id: 99,
            german: 'lesen',
            norsk: 'lese',
            norskPresent: 'leser',
            norskPast: 'leste',
            norskPastPerfect: 'har lest'
        })

        expect(verb.id).toBe(99)
        expect(verb.german).toBe('lesen')
        expect(verb.norsk).toBe('lese')
        expect(verb.norskPresent).toBe('leser')
    })
})

