export class Word {
    readonly id: number
    readonly german: string
    readonly norsk: string

    constructor(id: number, german: string, norsk: string) {
        this.id = id
        this.german = german
        this.norsk = norsk
    }

    matches(input: string): boolean {
        return input.toLowerCase() === this.norsk.toLowerCase()
    }

    static fromResponse(data: { id: number; german: string; norsk: string }): Word {
        return new Word(data.id, data.german, data.norsk)
    }
}

