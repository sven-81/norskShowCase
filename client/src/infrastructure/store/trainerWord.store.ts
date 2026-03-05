import { defineStore } from 'pinia'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const wordsRoute: string = api + '/train/words'

interface TrainerWordState {
    word: any | null
    id: number | null
    german: string
    norsk: string
    errorMessage: string
    loading: boolean
    inputNorsk: string | null
}

export const useTrainerWordStore = defineStore('trainingWords', {
    state: (): TrainerWordState => ({
        word: null,
        id: null,
        german: '',
        norsk: '',
        errorMessage: '',
        loading: false,
        inputNorsk: null
    }),
    actions: {
        async random(): Promise<void> {
            this.loading = true
            this.errorMessage = ''

            const defaultErrorMessage: string =
                'Ein unbekannter Fehler ist aufgetreten. \n Det oppstod en ukjent feil.'

            try {
                this.word = await httpClient.get(wordsRoute)
                this.id = this.word.id
                this.german = this.word.german
                this.norsk = this.word.norsk
            } catch (error: any) {
                this.word = null
                if (error.message === 'No records found in database for: words') {
                    this.errorMessage = 'In der Datenbank wurden keine Wörter gefunden.'
                } else {
                    this.errorMessage = defaultErrorMessage
                }
            } finally {
                this.loading = false
            }
        },
        async evaluate(norskInput: string): Promise<void> {
            const { useResultStore } = await import('@/infrastructure/store/trainingResult.store')
            const resultStore = useResultStore()

            if (norskInput.toLowerCase() === this.norsk.toLowerCase()) {
                this.inputNorsk = norskInput
                await resultStore.success()
                const id = this.id
                await httpClient.patch(wordsRoute + `/${id}`)
            } else {
                const message: string =
                    'Falsch => norsk:: ' + norskInput + '<br />' + 'Richtig => norsk:: ' + this.norsk
                await resultStore.error(message)
            }

            await this.random()
        }
    }
})

