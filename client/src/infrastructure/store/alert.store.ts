import { defineStore } from 'pinia'
import type { Alert } from '@/domain/model/Alert'
import { ErrorMapper } from '@/domain/service/ErrorMapper'

interface AlertState {
    alert: { message: string; type: string } | null
}

export const useAlertStore = defineStore('alert', {
    state: (): AlertState => ({
        alert: null
    }),
    actions: {
        success(message: string): void {
            this.alert = { message, type: 'alert-success' }
        },
        mapAuthError(error: any): void {
            const message = ErrorMapper.mapAuthError(error)
            this.alert = { message, type: 'alert-danger' }
        },
        mapWordError(error: any): void {
            const message = ErrorMapper.mapWordError(error)
            this.alert = { message, type: 'alert-danger' }
        },
        mapVerbError(error: any): void {
            const message = ErrorMapper.mapVerbError(error)
            this.alert = { message, type: 'alert-danger' }
        },
        clear(): void {
            this.alert = null
        }
    }
})

