import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Home from '@/views/HomeLayout.vue'
import { useAuthStore } from '@/stores'

describe('HomeLayout.vue', () => {
  let wrapper = null
  let store = null

  beforeEach(() => {
    wrapper = mount(Home, {
      global: {
        plugins: [
          createTestingPinia({
            createSpy: vi.fn
          })
        ]
      }
    })
    store = useAuthStore()
  })

  afterEach(() => {
    wrapper.unmount()
  })

  describe('if the user is not logged in', () => {
    it('displays "Jeg lærer norsk" without a name', () => {
      store.user = null

      wrapper.vm.$nextTick(() => {
        expect(wrapper.findAll('h1').length).toEqual(1)
        expect(wrapper.find('h1').text()).toMatch('Jeg lærer norsk')
      })
    })
  })

  describe('if the user is logged in', () => {
    it('displays "Jeg lærer norsk" with the user\'s first name', async () => {
      store.user = { firstName: 'Klaus' }

      await wrapper.vm.$nextTick()

      expect(wrapper.findAll('h1').length).toEqual(1)
      expect(wrapper.find('h1').text()).toMatch('Jeg lærer norsk, Klaus')
    })
  })
})
