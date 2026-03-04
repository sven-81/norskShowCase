import { defineStore } from 'pinia'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const wordsRoute: string = api + '/manage/words'

interface WordRecord {
    id: number
    german: string
    norsk: string
    isDeleting?: boolean
}

interface ManagerWordState {
    words: WordRecord[]
    searchGerman: string
    searchNorsk: string
    loading: boolean
    error: string | null
}

export const useManagerWordStore = defineStore('managedWords', {
    state: (): ManagerWordState => ({
        words: [],
        searchGerman: '',
        searchNorsk: '',
        loading: false,
        error: null
    }),
    getters: {
        computedFilteredWords: (state): WordRecord[] => {
            if (state.searchNorsk.length) {
                return state.words.filter((word) =>
                    word.norsk.toLowerCase().includes(state.searchNorsk.toLowerCase())
                )
            }
            return state.words.filter((word) =>
                word.german.toLowerCase().includes(state.searchGerman.toLowerCase())
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
        async update(editedWordRecord: WordRecord): Promise<void> {
            this.clearError()

            const { id, ...wordWithoutId } = editedWordRecord
            const german = editedWordRecord.german
            const normalizedGerman: string = german.trim()

            const duplicate = this.words.some((word) => {
                const isSameId = word.id === id
                const normalizedWordGerman = word.german.trim()
                return !isSameId && normalizedWordGerman === normalizedGerman
            })

            if (duplicate) {
                const error = new Error(`Das deutsche Wort "${german}" ist bereits vorhanden.`)
                this.error = error.message
                throw error
            }

            try {
                await httpClient.put(wordsRoute + `/${id}`, { german, norsk: wordWithoutId.norsk })
            } catch (error: any) {
                this.error = error.message || 'Die bisherigen Wörter können nicht angezeigt werden.'
                throw error
            }
        },
        async getAll(): Promise<void> {
            this.loading = true
            this.error = null
            try {
                this.words = await httpClient.get(wordsRoute)
            } catch (error: any) {
                this.error = error.message || 'Die bisherigen Wörter können nicht angezeigt werden.'
            } finally {
                this.loading = false
            }
        },
        async delete(id: number): Promise<void> {
            const word = this.words.find((x) => x.id === id)
            if (word) {
                word.isDeleting = true
            }

            await httpClient.delete(wordsRoute + `/${id}`)
            this.words = this.words.filter((x) => x.id !== id)
        },
        async add(words: { german: string; norsk: string }): Promise<void> {
            await httpClient.post(wordsRoute, words)
            await this.getAll()
        },
        clearError(): void {
            this.error = null
        }
    }
})

