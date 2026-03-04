import type { Verb } from '@/domain/model/Verb'

export interface ManagerVerbPort {
    getAll(): Promise<Verb[]>
    add(data: {
        german: string
        norsk: string
        norskPresent: string
        norskPast: string
        norskPastPerfect: string
    }): Promise<void>
    update(id: number, data: {
        german: string
        norsk: string
        norskPresent: string
        norskPast: string
        norskPastPerfect: string
    }): Promise<void>
    remove(id: number): Promise<void>
}

