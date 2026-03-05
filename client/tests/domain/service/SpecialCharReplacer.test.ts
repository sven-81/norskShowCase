import { describe, expect, it } from 'vitest'
import { replaceSpecialChars } from '@/domain/service/SpecialCharReplacer'

describe('SpecialCharReplacer', () => {
    it('replaces ä with æ', () => {
        expect(replaceSpecialChars('bäcker')).toBe('bæcker')
    })

    it('replaces Ä with Æ', () => {
        expect(replaceSpecialChars('Ärger')).toBe('Ærger')
    })

    it('replaces ö with ø', () => {
        expect(replaceSpecialChars('möchte')).toBe('møchte')
    })

    it('replaces Ö with Ø', () => {
        expect(replaceSpecialChars('Öl')).toBe('Øl')
    })

    it('replaces ü with å', () => {
        expect(replaceSpecialChars('über')).toBe('åber')
    })

    it('replaces Ü with Å', () => {
        expect(replaceSpecialChars('Über')).toBe('Åber')
    })

    it('replaces multiple umlauts in one string', () => {
        expect(replaceSpecialChars('öäü')).toBe('øæå')
    })

    it('does not change strings without umlauts', () => {
        expect(replaceSpecialChars('hund')).toBe('hund')
    })

    it('handles empty string', () => {
        expect(replaceSpecialChars('')).toBe('')
    })
})

