import { defineStore } from 'pinia'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const verbsRoute: string = api + '/train/verbs'

interface TrainerVerbState {
    verb: any | null
    id: number | null
    german: string
    norsk: string
    present: string
    past: string
    pastPerfect: string
    errorMessage: string
    loading: boolean
    inputNorsk: string | null
}

export const useTrainerVerbStore = defineStore('trainingVerbs', {
    state: (): TrainerVerbState => ({
        verb: null,
        id: null,
        german: '',
        norsk: '',
        present: 'present',
        past: 'past',
        pastPerfect: 'pastPerfect',
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
                this.verb = await httpClient.get(verbsRoute)
                this.id = this.verb.id
                this.german = this.verb.german
                this.norsk = this.verb.norsk
                this.present = this.verb.norskPresent
                this.past = this.verb.norskPast
                this.pastPerfect = this.verb.norskPastPerfect
            } catch (error: any) {
                this.verb = null
                if (error.message === 'No records found in database for: verbs') {
                    this.errorMessage = 'In der Datenbank wurden keine Verben gefunden.'
                } else {
                    this.errorMessage = defaultErrorMessage
                }
            } finally {
                this.loading = false
            }
        },
        async evaluate(norskInput: {
            infinitive: string
            present: string
            past: string
            pastPerfect: string
        }): Promise<void> {
            const { useResultStore } = await import('@/infrastructure/store/trainingResult.store')
            const resultStore = useResultStore()

            const infinitiveInput = norskInput.infinitive.trim()
            const presentInput = norskInput.present.trim()
            const pastInput = norskInput.past.trim()
            const pastPerfectInput = norskInput.pastPerfect.trim()

            const infinitiveCorrect = infinitiveInput.toLowerCase() === this.norsk.toLowerCase()
            const presentCorrect = presentInput.toLowerCase() === this.present.toLowerCase()
            const pastCorrect = pastInput.toLowerCase() === this.past.toLowerCase()
            const pastPerfectCorrect = pastPerfectInput.toLowerCase() === this.pastPerfect.toLowerCase()

            if (infinitiveCorrect && presentCorrect && pastCorrect && pastPerfectCorrect) {
                await resultStore.success()
                const id: number = this.id as number
                await httpClient.patch(verbsRoute + `/${id}`)
            } else {
                const keyMappings: Record<string, string> = {
                    infinitive: 'Imperativ',
                    present: 'Gegenwart',
                    past: 'Vergangenheit',
                    pastPerfect: 'Plusquamperfekt'
                }

                const correctValues: Record<string, string> = {
                    infinitive: this.norsk,
                    present: this.present,
                    past: this.past,
                    pastPerfect: this.pastPerfect
                }

                const message: string = `
  <table>
    <tr><th>Form</th><th>Falsch</th><th>Richtig</th></tr>
${Object.entries(norskInput)
    .map(([key, value]) => {
        const mappedKey = keyMappings[key] || key
        const correctValue = correctValues[key] || 'nicht vorhanden'
        return `<tr><td>${mappedKey}</td><td>${value}</td><td>${correctValue}</td></tr>`
    })
    .join('')}
  </table>
  <br />
`
                await resultStore.error(message)
            }

            await this.random()
        }
    }
})

