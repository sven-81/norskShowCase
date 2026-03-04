import { describe, expect, it } from 'vitest'
import { Alert } from '@/domain/model/Alert'

describe('Alert', () => {
    it('creates an alert with message and type', () => {
        const alert = new Alert('Erfolg', 'alert-success')

        expect(alert.message).toBe('Erfolg')
        expect(alert.type).toBe('alert-success')
    })

    it('creates a success alert via static factory', () => {
        const alert = Alert.success('Alles gut')

        expect(alert.message).toBe('Alles gut')
        expect(alert.type).toBe('alert-success')
    })

    it('creates a danger alert via static factory', () => {
        const alert = Alert.danger('Fehler aufgetreten')

        expect(alert.message).toBe('Fehler aufgetreten')
        expect(alert.type).toBe('alert-danger')
    })
})

