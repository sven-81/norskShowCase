import { defineStore } from 'pinia'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const verbsRoute: string = api + '/manage/verbs'

interface VerbRecord {
    id: number
    german: string
    norsk: string
    norskPresent: string
    norskPast: string
    norskPastPerfect: string
    isDeleting?: boolean
}

interface ManagerVerbState {
    verbs: VerbRecord[]
    searchGerman: string
    searchNorsk: string
    loading: boolean
    error: string | null
}

export const useManagerVerbStore = defineStore('managedVerbs', {
    state: (): ManagerVerbState => ({
        verbs: [],
        searchGerman: '',
        searchNorsk: '',
        loading: false,
        error: null
    }),
    getters: {
        computedFilteredVerbs: (state): VerbRecord[] => {
            if (state.searchNorsk.length) {
                return state.verbs.filter((verb) =>
                    verb.norsk.toLowerCase().includes(state.searchNorsk.toLowerCase())
                )
            }
            return state.verbs.filter((verb) =>
                verb.german.toLowerCase().includes(state.searchGerman.toLowerCase())
            )
        }
    },
    actions: {
        updateSearchTerm(newTerm: string, country: string): void {
            if (country === 'none') {
                this.searchNorsk = ''
                this.searchGerman = ''
            } else if (country === 'NO') {
                this.searchNorsk = newTerm
                this.searchGerman = ''
            } else {
                this.searchGerman = newTerm
                this.searchNorsk = ''
            }
        },
        async update(editedVerbRecord: VerbRecord): Promise<void> {
            this.clearError()

            const { id, ...verbWithoutId } = editedVerbRecord
            const german = editedVerbRecord.german
            const normalizedGerman: string = german.trim().toLowerCase()

            const duplicate = this.verbs.some((verb) => {
                const isSameId = verb.id === id
                const normalizedVerbGerman = verb.german.trim().toLowerCase()
                return !isSameId && normalizedVerbGerman === normalizedGerman
            })

            if (duplicate) {
                const error = new Error(`Das Verb "${german}" ist bereits vorhanden.`)
                this.error = error.message
                throw error
            }

            try {
                await httpClient.put(verbsRoute + `/${id}`, verbWithoutId)
            } catch (error: any) {
                this.error = error.message || 'Die bisherigen Verben können nicht angezeigt werden.'
                throw error
            }
        },
        async getAll(): Promise<void> {
            this.loading = true
            this.error = null
            try {
                this.verbs = await httpClient.get(verbsRoute)
            } catch (error: any) {
                this.error = error.message || 'Fehler beim Laden'
            } finally {
                this.loading = false
            }
        },
        async delete(id: number): Promise<void> {
            const verb = this.verbs.find((x) => x.id === id)
            if (verb) {
                verb.isDeleting = true
            }

            await httpClient.delete(verbsRoute + `/${id}`)
            this.verbs = this.verbs.filter((x) => x.id !== id)
        },
        async add(verbs: {
            german: string
            norsk: string
            norskPresent: string
            norskPast: string
            norskPastPerfect: string
        }): Promise<void> {
            await httpClient.post(verbsRoute, verbs)
            await this.getAll()
        },
        clearError(): void {
            this.error = null
        }
    }
})

