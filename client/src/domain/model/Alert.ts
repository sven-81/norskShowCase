import type { AlertType } from '@/domain/shared/types'

export class Alert {
    readonly message: string
    readonly type: AlertType

    constructor(message: string, type: AlertType) {
        this.message = message
        this.type = type
    }

    static success(message: string): Alert {
        return new Alert(message, 'alert-success')
    }

    static danger(message: string): Alert {
        return new Alert(message, 'alert-danger')
    }
}

