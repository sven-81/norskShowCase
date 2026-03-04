export class User {
    readonly login: boolean
    readonly username: string
    readonly firstName: string
    readonly lastName: string
    readonly token: string
    readonly tokenType: string
    readonly expiresIn: number
    readonly scope: string

    constructor(
        login: boolean,
        username: string,
        firstName: string,
        lastName: string,
        token: string,
        tokenType: string,
        expiresIn: number,
        scope: string
    ) {
        this.login = login
        this.username = username
        this.firstName = firstName
        this.lastName = lastName
        this.token = token
        this.tokenType = tokenType
        this.expiresIn = expiresIn
        this.scope = scope
    }

    isManager(): boolean {
        return this.scope === 'is:manager'
    }

    static fromResponse(data: Record<string, any>, scope: string): User {
        return new User(
            data.login ?? true,
            data.username ?? '',
            data.firstName ?? '',
            data.lastName ?? '',
            data.token ?? '',
            data.tokenType ?? 'Bearer',
            data.expiresIn ?? 7200,
            scope
        )
    }
}

