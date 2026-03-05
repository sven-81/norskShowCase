export type AlertType = 'alert-success' | 'alert-danger'
export type ResultType = 'correct' | 'mistake' | ''

export interface VerbInput {
    readonly infinitive: string
    readonly present: string
    readonly past: string
    readonly pastPerfect: string
}

export interface VerbEvaluationDetail {
    readonly form: string
    readonly input: string
    readonly correct: string
}

