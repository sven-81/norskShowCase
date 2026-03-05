import type { TrainerWordPort } from '@/domain/port/out/TrainerWordPort'
import type { Word } from '@/domain/model/Word'
import { Word as WordModel } from '@/domain/model/Word'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const route: string = api + '/train/words'

export class TrainerWordHttpAdapter implements TrainerWordPort {
    async getRandomWord(): Promise<Word> {
        const data = await httpClient.get(route)
        return WordModel.fromResponse(data)
    }

    async markAsTrained(id: number): Promise<void> {
        await httpClient.patch(route + `/${id}`)
    }
}

