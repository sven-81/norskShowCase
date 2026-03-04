export class EmojiRandomizer {
    private static readonly successEmojis: string[] = [
        '😊', '😄', '👍', '🙌', '🎉', '😁', '😀', '😃', '😉', '😅',
        '😆', '😍', '😎', '😲', '😮', '😲', '🤓', '🤠', '🤩', '🥳',
        '👏', '👌', '🤝'
    ]

    private static readonly errorEmojis: string[] = [
        '😢', '😏', '😐', '😑', '😒', '😓', '😔', '😕', '😖', '😞',
        '😟', '😣', '😥', '😦', '😧', '😨', '😩', '😪', '😫', '😬',
        '😭', '😯', '😱', '😵', '😶', '😳', '🙄', '🙁', '🤔', '🤕',
        '🤣', '🤢', '🤪', '🤫', '🤭', '🥱', '🤯', '🥺', '🧐', '🙈',
        '🙈🙉🙊'
    ]

    static getSuccessEmoji(): string {
        return EmojiRandomizer.random(EmojiRandomizer.successEmojis)
    }

    static getErrorEmoji(): string {
        return EmojiRandomizer.random(EmojiRandomizer.errorEmojis)
    }

    private static random(emojis: string[]): string {
        const index = Math.floor(Math.random() * emojis.length)
        return emojis[index] as string
    }
}

