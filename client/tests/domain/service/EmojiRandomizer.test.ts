import { describe, expect, it } from 'vitest'
import { EmojiRandomizer } from '@/domain/service/EmojiRandomizer'

describe('EmojiRandomizer', () => {
    const successEmojis = [
        '😊', '😄', '👍', '🙌', '🎉', '😁', '😀', '😃', '😉', '😅',
        '😆', '😍', '😎', '😲', '😮', '😲', '🤓', '🤠', '🤩', '🥳',
        '👏', '👌', '🤝'
    ]
    const errorEmojis = [
        '😢', '😏', '😐', '😑', '😒', '😓', '😔', '😕', '😖', '😞',
        '😟', '😣', '😥', '😦', '😧', '😨', '😩', '😪', '😫', '😬',
        '😭', '😯', '😱', '😵', '😶', '😳', '🙄', '🙁', '🤔', '🤕',
        '🤣', '🤢', '🤪', '🤫', '🤭', '🥱', '🤯', '🥺', '🧐', '🙈',
        '🙈🙉🙊'
    ]

    it('returns a success emoji from the known list', () => {
        const emoji = EmojiRandomizer.getSuccessEmoji()

        expect(successEmojis).toContain(emoji)
    })

    it('returns an error emoji from the known list', () => {
        const emoji = EmojiRandomizer.getErrorEmoji()

        expect(errorEmojis).toContain(emoji)
    })

    it('returns a string for success emoji', () => {
        const emoji = EmojiRandomizer.getSuccessEmoji()

        expect(typeof emoji).toBe('string')
        expect(emoji.length).toBeGreaterThan(0)
    })

    it('returns a string for error emoji', () => {
        const emoji = EmojiRandomizer.getErrorEmoji()

        expect(typeof emoji).toBe('string')
        expect(emoji.length).toBeGreaterThan(0)
    })
})

