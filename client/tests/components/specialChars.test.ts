import { describe, expect, it } from 'vitest'
import { replaceSpecialChars } from '@/components/specialChars'

describe('specialChars.ts', () => {
  it('replaces all german special chars to norwegian correctly', () => {
    const input = 'äöü ÄÖÜ'
    const expected = 'æøå ÆØÅ'

    expect(replaceSpecialChars(input)).toBe(expected)
  })

  it('does not touch any normal chars', () => {
    const input = 'abc xyz 123 !?ß'

    expect(replaceSpecialChars(input)).toBe(input)
  })

  it('works on mixed string', () => {
    const input = 'Grüß Äpfel & Öl über ÖPNV'
    const expected = 'Gråß Æpfel & Øl åber ØPNV'

    expect(replaceSpecialChars(input)).toBe(expected)
  })

  it('returns empty string on empty string', () => {
    expect(replaceSpecialChars('')).toBe('')
  })

  it('does not mutate the original string', () => {
    const input = 'Äpfel'
    const copy = input.slice()
    replaceSpecialChars(input)
    expect(input).toBe(copy)
  })

  it('replaces lowercase umlauts only in lowercase', () => {
    expect(replaceSpecialChars('äöü')).toBe('æøå')
  })

  it('replaces uppercase umlauts only in uppercase', () => {
    expect(replaceSpecialChars('ÄÖÜ')).toBe('ÆØÅ')
  })

  it('replaces multiple occurrences', () => {
    const input = 'ääää ÄÄÄÄ'
    const expected = 'ææææ ÆÆÆÆ'
    expect(replaceSpecialChars(input)).toBe(expected)
  })

  it('does not replace similar but normal letters', () => {
    expect(replaceSpecialChars('a o u A O U')).toBe('a o u A O U')
  })
})
