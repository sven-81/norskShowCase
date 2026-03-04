import type { User } from '@/domain/model/User'

export interface TokenStoragePort {
    saveUser(user: User): void
    getUser(): User | null
    removeUser(): void
}

