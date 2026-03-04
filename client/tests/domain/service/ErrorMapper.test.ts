import { describe, expect, it } from 'vitest'
import { ErrorMapper } from '@/domain/service/ErrorMapper'

describe('ErrorMapper', () => {
    describe('mapAuthError', () => {
        it('maps 401 to credentials message', () => {
            expect(ErrorMapper.mapAuthError({ status: 401, message: 'x' }))
                .toBe('Diese Zugangsdaten sind nicht gültig.')
        })

        it('maps unknown error without status', () => {
            expect(ErrorMapper.mapAuthError('neah')).toBe('Unbekannter Fehler: neah')
        })

        it('maps 422 with password length message', () => {
            expect(ErrorMapper.mapAuthError({
                status: 422,
                message: 'The password must be at least 12 characters long.'
            })).toBe('Das Passwort muss mindestens 12 Zeichen lang sein.')
        })
    })

    describe('mapWordError', () => {
        it('maps 409 to duplicate message', () => {
            expect(ErrorMapper.mapWordError({ status: 409, message: 'Conflict' }))
                .toBe('Das Wort existiert schon in der Datenbank.')
        })

        it('maps 500 with German chars message', () => {
            expect(ErrorMapper.mapWordError({ status: 500, message: 'German has at least two chars.' }))
                .toBe('Das deutsche Wort muss mindestens zwei Zeichen lang sein.')
        })
    })

    describe('mapVerbError', () => {
        it('maps 409 to duplicate message', () => {
            expect(ErrorMapper.mapVerbError({ status: 409, message: 'Conflict' }))
                .toBe('Das Verb existiert schon in der Datenbank.')
        })

        it('maps 500 with German chars message', () => {
            expect(ErrorMapper.mapVerbError({ status: 500, message: 'German has at least two chars.' }))
                .toBe('Das deutsche Verb muss mindestens zwei Zeichen lang sein.')
        })
    })
})

