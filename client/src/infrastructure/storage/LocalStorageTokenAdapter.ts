import type { TokenStoragePort } from '@/domain/port/out/TokenStoragePort'
import type { User } from '@/domain/model/User'

const USER_KEY = 'user'

export class LocalStorageTokenAdapter implements TokenStoragePort {
    saveUser(user: User): void {
        localStorage.setItem(USER_KEY, JSON.stringify(user))
    }

    getUser(): User | null {
        const raw = localStorage.getItem(USER_KEY)
        if (raw === null) {
            return null
        }
        try {
            return JSON.parse(raw)
        } catch {
            return null
        }
    }

    removeUser(): void {
        localStorage.removeItem(USER_KEY)
    }
}

