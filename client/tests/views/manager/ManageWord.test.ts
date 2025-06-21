import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import ManageWord from '@/views/manager/ManageWord.vue'
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
    useManagerWordStore: vi.fn(() => {
        return {
            getAll: vi.fn(),
            words: []
        }
    }),
    useAlertStore: vi.fn(() => ({
        showAlert: vi.fn()
    }))
}))

vi.mock('@/views/manager/AddWord.vue', () => ({
    default: {
        name: 'AddWord',
        template: '<div data-test="AddWord"></div>'
    }
}))
vi.mock('@/views/manager/EditWord.vue', () => ({
    default: {
        name: 'EditWord',
        template: '<div data-test="EditWord"></div>'
    }
}))
vi.mock('@/views/manager/FilterWord.vue', () => ({
    default: {
        name: 'FilterWord',
        template: '<div data-test="FilterWord"></div>'
    }
}))
vi.mock('@/views/manager/SpecialCharBox.vue', () => ({
    default: {
        name: 'SpecialCharBox',
        template: '<div id="specialChars">norwegische Sonderzeichen</div>'
    }
}))

describe('ManageWord.vue', () => {
    it('should mount and render correctly', () => {
        const wrapper = mount(ManageWord)
        expect(wrapper.find('h1').text()).toBe('Wörter verwalten')
    })

    it('should call getAll on mount', async () => {
        const wrapper = mount(ManageWord)
        await flushPromises()
        expect(wrapper.vm.wordsStore.getAll).toHaveBeenCalled()
    })


    it('should contain the required components', () => {
        const wrapper = mount(ManageWord)
        expect(wrapper.find('[id="specialChars"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="AddWord"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="EditWord"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="FilterWord"]').exists()).toBe(true)
    })
})
