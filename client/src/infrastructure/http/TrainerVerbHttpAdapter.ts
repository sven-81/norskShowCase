import type { TrainerVerbPort } from '@/domain/port/out/TrainerVerbPort'
import type { Verb } from '@/domain/model/Verb'
import { Verb as VerbModel } from '@/domain/model/Verb'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const route: string = api + '/train/verbs'

export class TrainerVerbHttpAdapter implements TrainerVerbPort {
    async getRandomVerb(): Promise<Verb> {
        const data = await httpClient.get(route)
        return VerbModel.fromResponse(data)
    }

    async markAsTrained(id: number): Promise<void> {
        await httpClient.patch(route + `/${id}`)
    }
}

