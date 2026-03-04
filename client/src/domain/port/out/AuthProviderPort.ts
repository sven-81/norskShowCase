export interface AuthProviderPort {
    getToken(): string | null
    logout(): void
}

