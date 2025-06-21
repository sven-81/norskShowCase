import { defineStore } from 'pinia'
import { fetchWrapper } from '@/request'
import { useResultStore } from '@/stores'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`
const verbsRoute: string = api + `/train/verbs`

export const useTrainerVerbStore = defineStore({
  id: 'trainingVerbs',
  state: () => ({
    verb: null as null | object,
    present: 'present',
    past: 'past',
    pastPerfect: 'pastPerfect',
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
        if (error.message == 'No records found in database for: verbs') {
          return 'In der Datenbank wurden keine Verben gefunden.'
        }
      }

      try {
        this.verb = await fetchWrapper.get(verbsRoute)
        this.id = this.verb.id
        this.german = this.verb.german
        this.norsk = this.verb.norsk
        this.present = this.verb.norskPresent
        this.past = this.verb.norskPast
        this.pastPerfect = this.verb.norskPastPerfect
      } catch (error: any) {
        this.verb = null
        this.errorMessage = mapError(error) || defaultErrorMessage
      } finally {
        this.loading = false
      }
    },
    async evaluate(norskInput) {
      const resultStore = useResultStore()

      const infinitiveInput = norskInput.infinitive.trim()
      const presentInput = norskInput.present.trim()
      const pastInput = norskInput.past.trim()
      const pastPerfectInput = norskInput.pastPerfect.trim()

      function infinitiveMatches() {
        return infinitiveInput.toLowerCase() === this.norsk.toLowerCase()
      }

      function presentMatches() {
        return presentInput.toLowerCase() === this.present.toLowerCase()
      }

      function pastMatches() {
        return pastInput.toLowerCase() === this.past.toLowerCase()
      }

      function pastPerfectMatches() {
        return pastPerfectInput.toLowerCase() === this.pastPerfect.toLowerCase()
      }

      function renameKeyToGerman(
        keyMappings: {
          infinitive: string
          present: string
          past: string
          pastPerfect: string
        },
        key: string
      ) {
        return keyMappings[key] || key
      }

      function mapCorrectNorsk(
        correctValues: {
          infinitive: any
          present: string
          past: string
          pastPerfect: string
        },
        key: string
      ) {
        return correctValues[key] || 'nicht vorhanden'
      }

      if (
        infinitiveMatches.call(this) &&
        presentMatches.call(this) &&
        pastMatches.call(this) &&
        pastPerfectMatches.call(this)
      ) {
        await resultStore.success()
        const id: number = this.id
        await fetchWrapper.patch(verbsRoute + `/${id}`)
      } else {
        const keyMappings = {
          infinitive: 'Imperativ',
          present: 'Gegenwart',
          past: 'Vergangenheit',
          pastPerfect: 'Plusquamperfekt'
        }

        const correctValues = {
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
    const mappedKey = renameKeyToGerman(keyMappings, key)
    const correctValue = mapCorrectNorsk(correctValues, key)
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
