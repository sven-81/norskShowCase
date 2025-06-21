import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAlertStore } from '@/stores'

describe('AlertStore', () => {
  let store = null

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useAlertStore()
  })

  it('initializes with correct values', () => {
    expect(store.alert).toEqual(null)
  })

  it('clears store', () => {
    store.success('yeah')
    store.clear()
    expect(store.alert).toEqual(null)
  })

  it('alerts success', () => {
    store.success('yeah')
    expect(store.alert).toEqual({
      message: 'yeah',
      type: 'alert-success'
    })
  })

  it('can map unknown auth error (non-object)', () => {
    store.mapAuthError('neah')
    expect(store.alert).toEqual({
      message: 'Unbekannter Fehler: neah',
      type: 'alert-danger'
    })
  })

  const authErrorCases = [
    {
      error: { status: 400, message: 'Missing required parameter: userName' },
      expected: 'Ungültige Eingaben. Der Benutzername darf nicht leer sein'
    },
    {
      error: { status: 401, message: 'Unauthorized' },
      expected: 'Diese Zugangsdaten sind nicht gültig.'
    },
    {
      error: { status: 403, message: 'Forbidden' },
      expected: 'Der User ist nicht freigeschaltet.'
    },
    {
      error: { status: 404, message: 'Not Found' },
      expected: 'Etwas ist schiefgelaufen. Die Seite kann nicht gefunden werden.'
    },
    {
      error: { status: 409, message: 'Conflict' },
      expected: 'Etwas ist schiefgelaufen. Probiere einen anderen Usernamen.'
    },
    {
      error: { status: 422, message: 'The password must be at least 12 characters long.' },
      expected: 'Das Passwort muss mindestens 12 Zeichen lang sein.'
    },
    {
      error: { status: 422, message: "Password contains invalid characters: ' or &." },
      expected: "Das Passwort enthält ungültige Zeichen: ' oder &."
    },
    {
      error: { status: 500, message: 'Server error' },
      expected: 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
    },
    {
      error: { status: 418, message: "I'm a teapot" },
      expected: "Fehler: I'm a teapot (Status: 418)"
    }
  ]

  authErrorCases.forEach(({ error, expected }) => {
    it(`maps auth error: status ${error.status}`, () => {
      store.mapAuthError(error)
      expect(store.alert).toEqual({
        message: expected,
        type: 'alert-danger'
      })
    })
  })

  const wordErrorCases = [
    {
      error: { status: 409, message: 'Conflict' },
      expected: 'Das Wort existiert schon in der Datenbank.'
    },
    {
      error: { status: 500, message: 'German has at least two chars.' },
      expected: 'Das deutsche Wort muss mindestens zwei Zeichen lang sein.'
    },
    {
      error: { status: 500, message: 'Internal error' },
      expected: 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
    },
    {
      error: { status: 418, message: "I'm a teapot" },
      expected: "Fehler: I'm a teapot (Status: 418)"
    }
  ]

  wordErrorCases.forEach(({ error, expected }) => {
    it(`maps word error: status ${error.status}, message "${error.message}"`, () => {
      store.mapWordError(error)
      expect(store.alert).toEqual({
        message: expected,
        type: 'alert-danger'
      })
    })
  })

  const verbErrorCases = [
    {
      error: { status: 409, message: 'Conflict' },
      expected: 'Das Verb existiert schon in der Datenbank.'
    },
    {
      error: { status: 500, message: 'German has at least two chars.' },
      expected: 'Das deutsche Verb muss mindestens zwei Zeichen lang sein.'
    },
    {
      error: { status: 500, message: 'Something else' },
      expected: 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
    },
    {
      error: { status: 418, message: "I'm a teapot" },
      expected: "Fehler: I'm a teapot (Status: 418)"
    }
  ]

  verbErrorCases.forEach(({ error, expected }) => {
    it(`maps verb error: status ${error.status}, message "${error.message}"`, () => {
      store.mapVerbError(error)
      expect(store.alert).toEqual({
        message: expected,
        type: 'alert-danger'
      })
    })
  })

  it('maps unknown auth error (non-object)', () => {
    store.mapAuthError('neah')
    expect(store.alert).toEqual({
      message: 'Unbekannter Fehler: neah',
      type: 'alert-danger'
    })
  })

  it('maps unknown word error (non-object)', () => {
    store.mapWordError('word error')
    expect(store.alert).toEqual({
      message: 'Unbekannter Fehler: word error',
      type: 'alert-danger'
    })
  })

  it('maps unknown verb error (non-object)', () => {
    store.mapVerbError('verb error')
    expect(store.alert).toEqual({
      message: 'Unbekannter Fehler: verb error',
      type: 'alert-danger'
    })
  })

  it('maps auth error with missing status property (object)', () => {
    store.mapAuthError({ message: 'no status' })
    expect(store.alert).toEqual({
      message: 'Unbekannter Fehler: [object Object]',
      type: 'alert-danger'
    })
  })

  it('maps word error with missing status property (object)', () => {
    store.mapWordError({ message: 'no status' })
    expect(store.alert).toEqual({
      message: 'Unbekannter Fehler: [object Object]',
      type: 'alert-danger'
    })
  })

  it('maps verb error with missing status property (object)', () => {
    store.mapVerbError({ message: 'no status' })
    expect(store.alert).toEqual({
      message: 'Unbekannter Fehler: [object Object]',
      type: 'alert-danger'
    })
  })

  it('maps 422 auth error with unknown message', () => {
    const error = { status: 422, message: 'Some other validation error' }
    store.mapAuthError(error)
    expect(store.alert).toEqual({
      message:
        'Die Daten können nicht verarbeitet werden. Bitte prüfen. Some other validation error',
      type: 'alert-danger'
    })
  })
})
