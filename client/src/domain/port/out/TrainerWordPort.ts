import type { Word } from '@/domain/model/Word'

export interface TrainerWordPort {
    getRandomWord(): Promise<Word>
    markAsTrained(id: number): Promise<void>
}

