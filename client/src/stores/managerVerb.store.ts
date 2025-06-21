import { defineStore } from 'pinia'
import { fetchWrapper } from '@/request'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const verbsRoute: string = api + `/manage/verbs`

export const useManagerVerbStore = defineStore({
  id: 'managedVerbs',
  state: () => ({
    verbs: [],
    searchGerman: '',
    searchNorsk: '',
    loading: false,
    error: null
  }),
  getters: {
    computedFilteredVerbs: (state) => {
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
    async update(editedVerbRecord) {
      this.clearError()

      function checkIfGermanVerbAlreadyExists(id, normalizedGerman: string) {
        return this.verbs.some((verb) => {
          const isSameId = verb.id === id
          const normalizedVerbGerman = verb.german.trim().toLowerCase()

          return !isSameId && normalizedVerbGerman === normalizedGerman
        })
      }

      try {
        const { id, ...verbWithoutId } = editedVerbRecord
        const german = editedVerbRecord.german
        const normalizedGerman: string = german.trim().toLowerCase()
        const duplicate = checkIfGermanVerbAlreadyExists.call(this, id, normalizedGerman)

        if (duplicate) {
          throw new Error(`Das Verb "${german}" ist bereits vorhanden.`)
        }

        await fetchWrapper.put(verbsRoute + `/${id}`, { german, ...verbWithoutId })
      } catch (error) {
        this.error = error.message || 'Die bisherigen Verben können nicht angezeigt werden.'
        throw error
      }
    },
    async getAll() {
      this.loading = true
      this.error = null
      try {
        this.verbs = await fetchWrapper.get(verbsRoute)
      } catch (error) {
        this.error = error.message || 'Fehler beim Laden'
      } finally {
        this.loading = false
      }
    },
    async delete(id) {
      // add isDeleting prop to word being deleted
      const verb = this.verbs.find((x) => x.id === id)
      if (verb) verb.isDeleting = true

      await fetchWrapper.delete(verbsRoute + `/${id}`)

      // remove verb from list after deleted
      this.verbs = this.verbs.filter((x) => x.id !== id)
    },
    async add(verbs) {
      await fetchWrapper.post(verbsRoute, verbs)
      await this.getAll()
    },
    clearError() {
      this.error = null
    }
  }
})
