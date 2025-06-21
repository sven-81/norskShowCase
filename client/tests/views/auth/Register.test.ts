import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import Register from '@/views/auth/Register.vue'
import { createTestingPinia } from '@pinia/testing'
import { useUserStore } from '@/stores'

vi.mock('@/components/CookieConsent.vue', () => ({
  default: {
    template: '<div class="cookie-consent-mock" />'
  }
}))

describe('Register.vue', () => {
  let registerMock: ReturnType<typeof vi.fn>

  beforeEach(() => {
    registerMock = vi.fn().mockResolvedValue(undefined)
  })

  function mountWithStore() {
    const wrapper = mount(Register, {
      global: {
        plugins: [
          createTestingPinia({
            stubActions: false,
            createSpy: vi.fn
          })
        ],
        stubs: {
          'router-link': {
            template: '<a><slot /></a>'
          }
        }
      }
    })

    const store = useUserStore()
    store.register = registerMock

    return wrapper
  }

  it('renders registration form correctly', () => {
    const wrapper = mountWithStore()
    expect(wrapper.find('h1').text()).toBe('Registrierung')
    expect(wrapper.find('input[name="firstName"]').exists()).toBe(true)
    expect(wrapper.find('input[name="lastName"]').exists()).toBe(true)
    expect(wrapper.find('input[name="username"]').exists()).toBe(true)
    expect(wrapper.find('input[name="password"]').exists()).toBe(true)
    expect(wrapper.find('button.button-primary').text()).toBe('registrieren')
  })

  it('shows validation errors when fields are empty', async () => {
    const wrapper = mountWithStore()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise(r => setTimeout(r, 10)) // is needed by VeeValidate

    const errors = wrapper.findAll('.invalid-feedback')
    expect(errors.length,'at least 4 fields').toBeGreaterThanOrEqual(4)
    expect(errors[0].text()).toBe('Vorname fehlt')
    expect(errors[1].text()).toBe('Nachname fehlt')
    expect(errors[2].text()).toBe('Benutzername fehlt')
    expect(errors[3].text()).toBe('Passwort fehlt')

    expect(registerMock).not.toHaveBeenCalled()
  })

  it('calls register with correct data when form is valid', async () => {
    const wrapper = mountWithStore()

    await wrapper.find('input[name="firstName"]').setValue('Max')
    await wrapper.find('input[name="lastName"]').setValue('Mustermann')
    await wrapper.find('input[name="username"]').setValue('max123')
    await wrapper.find('input[name="password"]').setValue('Abcd1234!@#$5678')

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise(r => setTimeout(r, 10)) // needed by VeeValidate

    expect(registerMock).toHaveBeenCalledTimes(1)
    expect(registerMock).toHaveBeenCalledWith({
      firstName: 'Max',
      lastName: 'Mustermann',
      username: 'max123',
      password: 'Abcd1234!@#$5678'
    })
  })

  it('spinner is initially hidden', () => {
    const wrapper = mountWithStore()
    const spinner = wrapper.find('.spinner')
    expect(spinner.exists()).toBe(true)
    expect(spinner.isVisible()).toBe(false)
  })

  it('shows spinner and disables button while submitting', async () => {
    const wrapper = mountWithStore()

    let resolveRegister: () => void
    const registerPromise = new Promise<void>(resolve => (resolveRegister = resolve))
    registerMock.mockReturnValueOnce(registerPromise)

    await wrapper.find('input[name="firstName"]').setValue('Max')
    await wrapper.find('input[name="lastName"]').setValue('Mustermann')
    await wrapper.find('input[name="username"]').setValue('max123')
    await wrapper.find('input[name="password"]').setValue('Abcd1234!@#$5678')

    await wrapper.find('form').trigger('submit.prevent')

    const spinner = wrapper.find('.spinner')
    expect(spinner.exists()).toBe(true)
    expect(spinner.isVisible()).toBe(true)
    expect(wrapper.find('button.button-primary').attributes('disabled')).toBeDefined()

    resolveRegister!()
    await flushPromises()
    await new Promise(r => setTimeout(r, 10)) // needed by VeeValidate
  })
})
