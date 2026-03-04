export interface AuthPort {
    login(username: string, password: string): Promise<any>
    register(data: { username: string; firstName: string; lastName: string; password: string }): Promise<void>
}

