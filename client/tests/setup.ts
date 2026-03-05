import { vi } from 'vitest'

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

if (typeof globalThis.localStorage === 'undefined' || typeof globalThis.localStorage.getItem !== 'function') {
    Object.defineProperty(globalThis, 'localStorage', {
        value: createLocalStorageMock(),
        writable: true,
        configurable: true
    })
}

