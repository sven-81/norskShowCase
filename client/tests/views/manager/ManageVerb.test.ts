import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import ManageVerb from '@/views/manager/ManageVerb.vue'
import { createMemoryHistory, createRouter } from 'vue-router'

vi.mock('vue-router', () => ({
  useRoute: vi.fn(() => ({
    path: '/',
    params: {},
    query: {}
  })),
  useRouter: vi.fn(() => ({
    push: vi.fn()
  })),
  createRouter: vi.fn(() =>
    createRouter({
      history: createMemoryHistory(),
      routes: []
    })
  )
}))

vi.mock('@/stores', () => ({
    useManagerVerbStore: vi.fn(() => {
        return {
            getAll: vi.fn(),
            verbs: []
        }
    }),
    useAlertStore: vi.fn(() => ({
        showAlert: vi.fn()
    }))
}))

vi.mock('@/views/manager/AddVerb.vue', () => ({
    default: {
        name: 'AddVerb',
        template: '<div data-test="AddVerb"></div>'
    }
}))
vi.mock('@/views/manager/EditVerb.vue', () => ({
    default: {
        name: 'EditVerb',
        template: '<div data-test="EditVerb"></div>'
    }
}))
vi.mock('@/views/manager/FilterVerb.vue', () => ({
    default: {
        name: 'FilterVerb',
        template: '<div data-test="FilterVerb"></div>'
    }
}))
vi.mock('@/views/manager/SpecialCharBox.vue', () => ({
    default: {
        name: 'SpecialCharBox',
        template: '<div id="specialChars">norwegische Sonderzeichen</div>'
    }
}))

describe('ManageVerb.vue', () => {
    it('should mount and render correctly', () => {
        const wrapper = mount(ManageVerb)
        expect(wrapper.find('h1').text()).toBe('Verben verwalten')
    })

    it('should call getAll on mount', async () => {
        const wrapper = mount(ManageVerb)
        await flushPromises()
        expect(wrapper.vm.verbsStore.getAll).toHaveBeenCalled()
    })


    it('should contain the required components', () => {
        const wrapper = mount(ManageVerb)
        expect(wrapper.find('[id="specialChars"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="AddVerb"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="EditVerb"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="FilterVerb"]').exists()).toBe(true)
    })
})
