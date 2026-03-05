import { defineStore } from 'pinia'
import { TrainingResult } from '@/domain/model/TrainingResult'

interface ResultState {
    resultMessage: string
    type: string
}

export const useResultStore = defineStore('trainingResult', {
    state: (): ResultState => ({
        resultMessage: '',
        type: ''
    }),
    actions: {
        async success(): Promise<void> {
            const result = TrainingResult.success()
            this.type = result.type
            this.resultMessage = result.message
        },
        async error(message: string): Promise<void> {
            const result = TrainingResult.error(message)
            this.type = result.type
            this.resultMessage = result.message
        },
        clear(): void {
            const result = TrainingResult.empty()
            this.resultMessage = result.message
            this.type = result.type
        }
    }
})

