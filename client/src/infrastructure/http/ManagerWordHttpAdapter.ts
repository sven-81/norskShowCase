import type { ManagerWordPort } from '@/domain/port/out/ManagerWordPort'
import type { Word } from '@/domain/model/Word'
import { Word as WordModel } from '@/domain/model/Word'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const route: string = api + '/manage/words'

export class ManagerWordHttpAdapter implements ManagerWordPort {
    async getAll(): Promise<Word[]> {
        const data = await httpClient.get(route)
        return data.map((item: any) => WordModel.fromResponse(item))
    }

    async add(wordData: { german: string; norsk: string }): Promise<void> {
        await httpClient.post(route, wordData)
    }

    async update(id: number, wordData: { german: string; norsk: string }): Promise<void> {
        await httpClient.put(route + `/${id}`, wordData)
    }

    async remove(id: number): Promise<void> {
        await httpClient.delete(route + `/${id}`)
    }
}

