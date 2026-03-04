import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { LocalStorageTokenAdapter } from '@/infrastructure/storage/LocalStorageTokenAdapter'
import { User } from '@/domain/model/User'

function createLocalStorageMock(): Storage {
    const store: Record<string, string> = {}
    return {
        getItem: vi.fn((key: string) => store[key] ?? null),
        setItem: vi.fn((key: string, value: string) => { store[key] = value }),
        removeItem: vi.fn((key: string) => { delete store[key] }),
        clear: vi.fn(() => { Object.keys(store).forEach((k) => delete store[k]) }),
        get length() { return Object.keys(store).length },
        key: vi.fn((_index: number) => null)
    }
}

describe('LocalStorageTokenAdapter', () => {
    let adapter: LocalStorageTokenAdapter
    let storageMock: Storage

    beforeEach(() => {
        storageMock = createLocalStorageMock()
        vi.stubGlobal('localStorage', storageMock)
        adapter = new LocalStorageTokenAdapter()
    })

    afterEach(() => {
        vi.restoreAllMocks()
    })

    it('saves a user to localStorage', () => {
        const user = new User(true, 'sven', 'Sven', 'Duge', 'token', 'Bearer', 7200, 'is:manager')

        adapter.saveUser(user)

        expect(storageMock.setItem).toHaveBeenCalledWith('user', JSON.stringify(user))
    })

    it('retrieves a stored user from localStorage', () => {
        const user = new User(true, 'klaus', 'Klaus', 'Müller', 'jwt', 'Bearer', 3600, 'is:user')
        storageMock.setItem('user', JSON.stringify(user))

        const result = adapter.getUser()

        expect(result).not.toBeNull()
        expect(result!.username).toBe('klaus')
    })

    it('returns null when no user is stored', () => {
        const result = adapter.getUser()

        expect(result).toBeNull()
    })

    it('returns null when stored data is invalid JSON', () => {
        storageMock.setItem('user', 'ungültiger-json{{{')

        const result = adapter.getUser()

        expect(result).toBeNull()
    })

    it('removes user from localStorage', () => {
        const user = new User(true, 'sven', 'Sven', 'Duge', 'token', 'Bearer', 7200, 'is:user')
        adapter.saveUser(user)

        adapter.removeUser()

        expect(storageMock.removeItem).toHaveBeenCalledWith('user')
    })
})

