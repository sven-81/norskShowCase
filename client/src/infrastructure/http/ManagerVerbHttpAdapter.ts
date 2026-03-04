import type { ManagerVerbPort } from '@/domain/port/out/ManagerVerbPort'
import type { Verb } from '@/domain/model/Verb'
import { Verb as VerbModel } from '@/domain/model/Verb'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const route: string = api + '/manage/verbs'

export class ManagerVerbHttpAdapter implements ManagerVerbPort {
    async getAll(): Promise<Verb[]> {
        const data = await httpClient.get(route)
        return data.map((item: any) => VerbModel.fromResponse(item))
    }

    async add(verbData: {
        german: string
        norsk: string
        norskPresent: string
        norskPast: string
        norskPastPerfect: string
    }): Promise<void> {
        await httpClient.post(route, verbData)
    }

    async update(id: number, verbData: {
        german: string
        norsk: string
        norskPresent: string
        norskPast: string
        norskPastPerfect: string
    }): Promise<void> {
        await httpClient.put(route + `/${id}`, verbData)
    }

    async remove(id: number): Promise<void> {
        await httpClient.delete(route + `/${id}`)
    }
}

