import type { Word } from '@/domain/model/Word'

export interface ManagerWordPort {
    getAll(): Promise<Word[]>
    add(data: { german: string; norsk: string }): Promise<void>
    update(id: number, data: { german: string; norsk: string }): Promise<void>
    remove(id: number): Promise<void>
}

