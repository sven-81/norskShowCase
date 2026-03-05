import type { Verb } from '@/domain/model/Verb'

export interface TrainerVerbPort {
    getRandomVerb(): Promise<Verb>
    markAsTrained(id: number): Promise<void>
}

