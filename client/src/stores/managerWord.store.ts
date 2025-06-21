import { defineStore } from 'pinia'
import { fetchWrapper } from '@/request'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const wordsRoute: string = api + `/manage/words`

export const useManagerWordStore = defineStore({
  id: 'managedWords',
  state: () => ({
    words: [],
    searchGerman: '',
    searchNorsk: '',
    loading: false,
    error: null
  }),
  getters: {
    computedFilteredWords: (state) => {
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
    updateSearchTerm(newTerm, country) {
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
    async update(editedWordRecord) {
      this.clearError()

      function checkIfGermanWordAlreadyExists(id, normalizedGerman: string) {
        return this.words.some((word) => {
          const isSameId = word.id === id
          const normalizedWordGerman = word.german.trim()

          return !isSameId && normalizedWordGerman === normalizedGerman
        })
      }

      try {
        const { id, ...wordWithoutId } = editedWordRecord
        const german = editedWordRecord.german
        const normalizedGerman: string = german.trim()
        const duplicate = checkIfGermanWordAlreadyExists.call(this, id, normalizedGerman)

        if (duplicate) {
          throw new Error(`Das deutsche Wort "${german}" ist bereits vorhanden.`)
        }

        await fetchWrapper.put(wordsRoute + `/${id}`, { german, ...wordWithoutId })
      } catch (error) {
        this.error = error.message || 'Die bisherigen Wörter können nicht angezeigt werden.'
        throw error
      }
    },
    async getAll() {
      this.loading = true
      this.error = null
      try {
        this.words = await fetchWrapper.get(wordsRoute)
      } catch (error) {
        this.error = error.message || 'Die bisherigen Wörter können nicht angezeigt werden.'
      } finally {
        this.loading = false
      }
    },
    async delete(id) {
      // add isDeleting prop to word being deleted
      const word = this.words.find((x) => x.id === id)
      if (word) word.isDeleting = true

      await fetchWrapper.delete(wordsRoute + `/${id}`)

      // remove word from list after deleted
      this.words = this.words.filter((x) => x.id !== id)
    },
    async add(words) {
      await fetchWrapper.post(wordsRoute, words)
      await this.getAll()
    },
    clearError() {
      this.error = null
    }
  }
})
