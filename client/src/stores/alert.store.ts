import { defineStore } from 'pinia'

export const useAlertStore = defineStore({
  id: 'alert',
  state: () => ({
    alert: null
  }),
  actions: {
    success(message) {
      this.alert = { message, type: 'alert-success' }
    },
    mapAuthError(error) {
      let message: string = ''

      function handleBadRequests() {
        let addition: string = ''
        if (error.message == 'Missing required parameter: userName') {
          addition = ' Der Benutzername darf nicht leer sein'
        }
        message = 'Ungültige Eingaben.' + addition
      }

      function handleUnprocessableData() {
        if (error.message == 'The password must be at least 12 characters long.') {
          message = 'Das Passwort muss mindestens 12 Zeichen lang sein.'
        } else if (error.message == "Password contains invalid characters: ' or &.") {
          message = "Das Passwort enthält ungültige Zeichen: ' oder &."
        } else {
          message = 'Die Daten können nicht verarbeitet werden. Bitte prüfen. ' + error.message
        }
      }

      if (error.status) {
        switch (error.status) {
          case 400:
            handleBadRequests()
            break
          case 401:
            message = 'Diese Zugangsdaten sind nicht gültig.'
            break
          case 403:
            message = 'Der User ist nicht freigeschaltet.'
            break
          case 404:
            message = 'Etwas ist schiefgelaufen. Die Seite kann nicht gefunden werden.'
            break
          case 409:
            message = 'Etwas ist schiefgelaufen. Probiere einen anderen Usernamen.'
            break
          case 422:
            handleUnprocessableData()
            break
          case 500:
            message = 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
            break
          default:
            message = `Fehler: ${error.message.toString()} (Status: ${error.status})`
        }
      } else {
        message = `Unbekannter Fehler: ${error}`
      }

      this.alert = { message, type: 'alert-danger' }
    },
    mapWordError(error) {
      let message: string = ''

      function handleServerError() {
        if (error.message == 'German has at least two chars.') {
          message = 'Das deutsche Wort muss mindestens zwei Zeichen lang sein.'
        } else {
          message = 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
        }
      }

      if (error.status) {
        switch (error.status) {
          case 409:
            message = 'Das Wort existiert schon in der Datenbank.'
            break
          case 500:
            handleServerError()
            break
          default:
            message = `Fehler: ${error.message.toString()} (Status: ${error.status})`
        }
      } else {
        message = `Unbekannter Fehler: ${error}`
      }

      this.alert = { message, type: 'alert-danger' }
    },
    mapVerbError(error) {
      let message: string = ''

      function handleServerError() {
        if (error.message == 'German has at least two chars.') {
          message = 'Das deutsche Verb muss mindestens zwei Zeichen lang sein.'
        } else {
          message = 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
        }
      }

      if (error.status) {
        switch (error.status) {
          case 409:
            message = 'Das Verb existiert schon in der Datenbank.'
            break
          case 500:
            handleServerError()
            break
          default:
            message = `Fehler: ${error.message.toString()} (Status: ${error.status})`
        }
      } else {
        message = `Unbekannter Fehler: ${error}`
      }

      this.alert = { message, type: 'alert-danger' }
    },
    clear() {
      this.alert = null
    }
  }
})
