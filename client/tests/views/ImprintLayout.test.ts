import { describe, expect, it , beforeEach, afterEach,vi} from 'vitest'
import { mount } from '@vue/test-utils'
import Imprint from '@/views/ImprintLayout.vue'

describe('ImprintLayout.vue', () => {
    let wrapper = null

    beforeEach(() => {
        vi.stubEnv('VITE_IMPRINT_MAIL', 'kontakt@example.com')

        wrapper = mount(Imprint, {
            global: {
                plugins: []
            }
        })
    })

    afterEach(() => {
        wrapper.unmount()
        vi.unstubAllEnvs()
    })

    it('should render the imprint heading', () => {
        expect(wrapper.find('h1').text()).toBe('Impressum')
    })

    it('should render the contact email correctly', () => {
        const mail = 'kontakt@example.com'
        expect(wrapper.text()).toContain(mail)
    })

    it('should render the imprint description correctly', () => {
        expect(wrapper.text()).toContain('Dies ist eine private Website zum Lernen von norwegischen Vokabeln.')
        expect(wrapper.text()).toContain('Deine Freischaltung erfolgt nach Registrierung manuell.')
        expect(wrapper.text()).toContain('Wir verwenden Cookies, damit du dich registrieren und einloggen kannst.')
        expect(wrapper.text()).toContain('Willst du Auskunft über deine gespeicherten Daten oder möchtest deine Daten gelöscht haben, schreibe mich einfach an.')
    })
})
