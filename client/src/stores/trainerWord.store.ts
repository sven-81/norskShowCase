import { defineStore } from 'pinia'
import { fetchWrapper } from '@/request'
import { useResultStore } from '@/stores'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const wordsRoute: string = api + `/train/words`

export const useTrainerWordStore = defineStore({
  id: 'trainingWords',
  state: () => ({
    word: null as null | object,
    errorMessage: '' as string,
    loading: false,
    inputNorsk: null
  }),
  actions: {
    async random() {
      this.loading = true
      this.errorMessage = ''

      const defaultErrorMessage: string =
        'Ein unbekannter Fehler ist aufgetreten. \n Det oppstod en ukjent feil.'

      function mapError(error) {
        if (error.message == 'No records found in database for: words') {
          return 'In der Datenbank wurden keine Wörter gefunden.'
        }
      }

      try {
        this.word = await fetchWrapper.get(wordsRoute)
        this.id = this.word.id
        this.german = this.word.german
        this.norsk = this.word.norsk
      } catch (error: any) {
        this.word = null
        this.errorMessage = mapError(error) || defaultErrorMessage
      } finally {
        this.loading = false
      }
    },
    async evaluate(norskInput) {
      const resultStore = useResultStore()

      if (norskInput.toLowerCase() === this.norsk.toLowerCase()) {
        this.inputNorsk = norskInput
        await resultStore.success()
        const id = this.id
        await fetchWrapper.patch(wordsRoute + `/${id}`)
      } else {
        const message: string =
          'Falsch => norsk:: ' + norskInput + '<br />' + 'Richtig => norsk:: ' + this.norsk
        await resultStore.error(message)
      }

      await this.random()
    }
  }
})
