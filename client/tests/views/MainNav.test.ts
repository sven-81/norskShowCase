import { beforeEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { useAuthStore } from '@/stores'
import { useRoute } from 'vue-router'
import MainNav from '@/views/MainNav.vue'

vi.mock('vue-router', () => ({
  useRoute: vi.fn()
}))

vi.mock('@/stores', () => ({
  useAuthStore: vi.fn()
}))

describe('MainNav', () => {
  let mockRoute: any
  let mockAuthStore: any

  beforeEach(() => {
    mockRoute = { path: '/' }
    ;(useRoute as any).mockReturnValue(mockRoute)

    mockAuthStore = {
      user: null,
      logout: vi.fn()
    }
    ;(useAuthStore as any).mockReturnValue(mockAuthStore)
  })

  it('renders login and register links if user is not logged in', () => {
    const wrapper = mount(MainNav)

    expect(wrapper.text()).toContain('Login')
    expect(wrapper.text()).toContain('Registrieren')
    expect(wrapper.text()).not.toContain('Logout')
  })

  it('renders logout link if user is logged in', () => {
    mockAuthStore.user = { scope: 'is:user' }

    const wrapper = mount(MainNav)

    expect(wrapper.text()).toContain('Logout')
    expect(wrapper.text()).not.toContain('Login')
  })

  it('renders manager section if user is manager', () => {
    mockAuthStore.user = { scope: 'is:manager' }

    const wrapper = mount(MainNav)

    expect(wrapper.text()).toContain('Manager')
    expect(wrapper.text()).toContain('Wörter')
    expect(wrapper.text()).toContain('Verben')
  })

  it('adds active class to menu items based on route path', async () => {
    mockAuthStore.user = { scope: 'is:manager' }
    mockRoute.path = '/manage/words'

    const wrapper = mount(MainNav)

    const activeItems = wrapper.findAll('.active')
    expect(activeItems.length).toBeGreaterThan(0)
    expect(activeItems.some((item) => item.text().includes('Wörter'))).toBe(true)
  })

  it('calls logout function on logout click', async () => {
    mockAuthStore.user = { scope: 'is:user' }

    const wrapper = mount(MainNav)
    await wrapper.get('li:has(a[href="/login"])').trigger('click')

    expect(mockAuthStore.logout).toHaveBeenCalled()
  })

  it('always renders imprint link', () => {
    const wrapper = mount(MainNav)
    expect(wrapper.text()).toContain('Impressum')
  })
})
