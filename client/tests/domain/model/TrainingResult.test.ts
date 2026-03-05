import { describe, expect, it } from 'vitest'
import { TrainingResult } from '@/domain/model/TrainingResult'

describe('TrainingResult', () => {
    it('creates a success result with emoji', () => {
        const result = TrainingResult.success()

        expect(result.message.startsWith('Alles richtig!')).toBe(true)
        expect(result.type).toBe('correct')
    })

    it('creates an error result with validation message and emoji', () => {
        const result = TrainingResult.error('Falsch eingegeben')

        expect(result.message).toContain('Oh no')
        expect(result.message).toContain('Falsch eingegeben')
        expect(result.type).toBe('mistake')
    })

    it('creates an empty result', () => {
        const result = TrainingResult.empty()

        expect(result.message).toBe('')
        expect(result.type).toBe('')
    })

    it('creates a result with constructor', () => {
        const result = new TrainingResult('Test Nachricht', 'correct')

        expect(result.message).toBe('Test Nachricht')
        expect(result.type).toBe('correct')
    })
})

