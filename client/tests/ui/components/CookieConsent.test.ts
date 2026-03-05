import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import CookieConsent from '@/ui/components/CookieConsent.vue'
import Cookies from 'js-cookie'

vi.mock('js-cookie', () => ({
    default: {
        get: vi.fn(),
        set: vi.fn()
    }
}))

describe('CookieConsent.vue', () => {
    afterEach(() => {
        vi.clearAllMocks()
    })

    it('shows consent popup when no cookie is set', async () => {
        vi.mocked(Cookies.get).mockReturnValue(undefined)

        const wrapper = mount(CookieConsent)
        await wrapper.vm.$nextTick()

        expect(wrapper.find('.cookie-popup').exists()).toBe(true)
        expect(wrapper.text()).toContain('Wir verwenden Cookies für die Benutzerverwaltung.')
    })

    it('hides consent popup when cookie is already set', async () => {
        vi.mocked(Cookies.get).mockReturnValue('true')

        const wrapper = mount(CookieConsent)
        await wrapper.vm.$nextTick()

        expect(wrapper.find('.cookie-popup').exists()).toBe(false)
    })

    it('sets cookie and hides popup on accept click', async () => {
        vi.mocked(Cookies.get).mockReturnValue(undefined)

        const wrapper = mount(CookieConsent)
        await wrapper.vm.$nextTick()

        await wrapper.find('button').trigger('click')
        await wrapper.vm.$nextTick()

        expect(Cookies.set).toHaveBeenCalledWith('cookieConsent', 'true', { expires: 30, secure: true, sameSite: 'Strict' })
        expect(wrapper.find('.cookie-popup').exists()).toBe(false)
    })

    it('displays the accept button with correct text', async () => {
        vi.mocked(Cookies.get).mockReturnValue(undefined)

        const wrapper = mount(CookieConsent)
        await wrapper.vm.$nextTick()

        expect(wrapper.find('button.accept').text()).toBe('Ich stimme zu')
    })
})

