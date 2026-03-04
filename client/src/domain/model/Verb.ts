import type { VerbInput, VerbEvaluationDetail } from '@/domain/shared/types'

export class Verb {
    readonly id: number
    readonly german: string
    readonly norsk: string
    readonly norskPresent: string
    readonly norskPast: string
    readonly norskPastPerfect: string

    constructor(
        id: number,
        german: string,
        norsk: string,
        norskPresent: string,
        norskPast: string,
        norskPastPerfect: string
    ) {
        this.id = id
        this.german = german
        this.norsk = norsk
        this.norskPresent = norskPresent
        this.norskPast = norskPast
        this.norskPastPerfect = norskPastPerfect
    }

    evaluate(input: VerbInput): { isCorrect: boolean; details: VerbEvaluationDetail[] } {
        const infinitiveCorrect = input.infinitive.toLowerCase() === this.norsk.toLowerCase()
        const presentCorrect = input.present.toLowerCase() === this.norskPresent.toLowerCase()
        const pastCorrect = input.past.toLowerCase() === this.norskPast.toLowerCase()
        const pastPerfectCorrect = input.pastPerfect.toLowerCase() === this.norskPastPerfect.toLowerCase()

        const isCorrect = infinitiveCorrect && presentCorrect && pastCorrect && pastPerfectCorrect

        const details: VerbEvaluationDetail[] = [
            { form: 'Imperativ', input: input.infinitive, correct: this.norsk },
            { form: 'Gegenwart', input: input.present, correct: this.norskPresent },
            { form: 'Vergangenheit', input: input.past, correct: this.norskPast },
            { form: 'Plusquamperfekt', input: input.pastPerfect, correct: this.norskPastPerfect }
        ]

        return { isCorrect, details }
    }

    static fromResponse(data: {
        id: number
        german: string
        norsk: string
        norskPresent: string
        norskPast: string
        norskPastPerfect: string
    }): Verb {
        return new Verb(
            data.id,
            data.german,
            data.norsk,
            data.norskPresent,
            data.norskPast,
            data.norskPastPerfect
        )
    }
}

