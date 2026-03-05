export class ErrorMapper {

    static mapAuthError(error: any): string {
        if (!error.status) {
            return `Unbekannter Fehler: ${error}`
        }

        switch (error.status) {
            case 400:
                return ErrorMapper.mapAuthBadRequest(error)
            case 401:
                return 'Diese Zugangsdaten sind nicht gültig.'
            case 403:
                return 'Der User ist nicht freigeschaltet.'
            case 404:
                return 'Etwas ist schiefgelaufen. Die Seite kann nicht gefunden werden.'
            case 409:
                return 'Etwas ist schiefgelaufen. Probiere einen anderen Usernamen.'
            case 422:
                return ErrorMapper.mapAuthUnprocessableData(error)
            case 500:
                return 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
            default:
                return `Fehler: ${error.message.toString()} (Status: ${error.status})`
        }
    }

    static mapWordError(error: any): string {
        if (!error.status) {
            return `Unbekannter Fehler: ${error}`
        }

        switch (error.status) {
            case 409:
                return 'Das Wort existiert schon in der Datenbank.'
            case 500:
                return ErrorMapper.mapWordServerError(error)
            default:
                return `Fehler: ${error.message.toString()} (Status: ${error.status})`
        }
    }

    static mapVerbError(error: any): string {
        if (!error.status) {
            return `Unbekannter Fehler: ${error}`
        }

        switch (error.status) {
            case 409:
                return 'Das Verb existiert schon in der Datenbank.'
            case 500:
                return ErrorMapper.mapVerbServerError(error)
            default:
                return `Fehler: ${error.message.toString()} (Status: ${error.status})`
        }
    }

    private static mapAuthBadRequest(error: any): string {
        let addition = ''
        if (error.message === 'Missing required parameter: userName') {
            addition = ' Der Benutzername darf nicht leer sein'
        }
        return 'Ungültige Eingaben.' + addition
    }

    private static mapAuthUnprocessableData(error: any): string {
        if (error.message === 'The password must be at least 12 characters long.') {
            return 'Das Passwort muss mindestens 12 Zeichen lang sein.'
        }
        if (error.message === "Password contains invalid characters: ' or &.") {
            return "Das Passwort enthält ungültige Zeichen: ' oder &."
        }
        return 'Die Daten können nicht verarbeitet werden. Bitte prüfen. ' + error.message
    }

    private static mapWordServerError(error: any): string {
        if (error.message === 'German has at least two chars.') {
            return 'Das deutsche Wort muss mindestens zwei Zeichen lang sein.'
        }
        return 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
    }

    private static mapVerbServerError(error: any): string {
        if (error.message === 'German has at least two chars.') {
            return 'Das deutsche Verb muss mindestens zwei Zeichen lang sein.'
        }
        return 'Etwas ist schiefgelaufen. Der Server antwortet nicht.'
    }
}

