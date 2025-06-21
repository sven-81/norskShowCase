import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import Login from '@/views/auth/Login.vue'
import { createTestingPinia } from '@pinia/testing'
import { useAuthStore } from '@/stores'

vi.mock('@/components/CookieConsent.vue', () => ({
  default: {
    template: '<div class="cookie-consent-mock" />'
  }
}))

describe('Login.vue', () => {
  let loginMock: ReturnType<typeof vi.fn>

  beforeEach(() => {
    loginMock = vi.fn().mockResolvedValue(undefined)
  })

  function mountWithStore() {
    const wrapper = mount(Login, {
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

    const store = useAuthStore()
    store.login = loginMock

    return wrapper
  }

  it('renders login form correctly', () => {
    const wrapper = mountWithStore()
    expect(wrapper.find('h1').exists()).toBe(true)
    expect(wrapper.find('h1').text()).toBe('Login')
    expect(wrapper.find('input#username').exists()).toBe(true)
    expect(wrapper.find('input#password').exists()).toBe(true)
    expect(wrapper.find('button').text()).toBe('login')
    expect(wrapper.find('.cookie-consent-mock').exists()).toBe(true)
  })

  it('shows validation errors when fields are empty', async () => {
    const wrapper = mountWithStore()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise((resolve) => setTimeout(resolve, 10)) // needed by VeeValidate

    const errorMessages = wrapper.findAll('.invalid-feedback')
    expect(errorMessages.length,'checks if both fields exist').toBe(2)
    expect(errorMessages[0].text()).toBe('Benutzername fehlt')
    expect(errorMessages[1].text()).toBe('Passwort fehlt')

    expect(loginMock).not.toHaveBeenCalled()
  })

  it('calls login with correct data when form is valid', async () => {
    const wrapper = mountWithStore()

    const usernameInput = wrapper.find('input#username')
    const passwordInput = wrapper.find('input#password')

    await usernameInput.setValue('user')
    await passwordInput.setValue('pw')

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise((resolve) => setTimeout(resolve, 10)) // needed by VeeValidate

    expect(loginMock).toHaveBeenCalledTimes(1)
    expect(loginMock).toHaveBeenCalledWith('user', 'pw')
  })

  it('disables button and shows spinner while submitting', async () => {
    const wrapper = mountWithStore()

    let resolveLogin: () => void
    const loginPromise = new Promise<void>((resolve) => {
      resolveLogin = resolve
    })
    loginMock.mockReturnValueOnce(loginPromise)

    await wrapper.find('input#username').setValue('user')
    await wrapper.find('input#password').setValue('pw')

    // send form
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    // while login
    expect(wrapper.find('.spinner').isVisible()).toBe(true)
    expect(wrapper.find('button').attributes('disabled')).toBeDefined()

    resolveLogin!()
    await flushPromises()
    await new Promise((resolve) => setTimeout(resolve, 10)) // needed by VeeValidate
  })

  it('spinner and disabled button are initially not present', () => {
    const wrapper = mountWithStore()
    const spinner = wrapper.find('.spinner')
    expect(spinner.exists()).toBe(true)
    expect(spinner.isVisible()).toBe(false)
  })
})
