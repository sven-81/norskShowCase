import type { ResultType } from '@/domain/shared/types'
import { EmojiRandomizer } from '@/domain/service/EmojiRandomizer'

export class TrainingResult {
    readonly message: string
    readonly type: ResultType

    constructor(message: string, type: ResultType) {
        this.message = message
        this.type = type
    }

    static success(): TrainingResult {
        const emoji = EmojiRandomizer.getSuccessEmoji()
        return new TrainingResult('Alles richtig! ' + emoji, 'correct')
    }

    static error(validationMessage: string): TrainingResult {
        const emoji = EmojiRandomizer.getErrorEmoji()
        const message = '<div>Oh no ' + emoji + '</div><div>' + validationMessage + '</div>'
        return new TrainingResult(message, 'mistake')
    }

    static empty(): TrainingResult {
        return new TrainingResult('', '')
    }
}

