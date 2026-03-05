import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import ManageWordView from '@/ui/views/manager/ManageWordView.vue'
vi.mock('vue-router', () => ({
    useRoute: vi.fn(() => ({ path: '/', params: {}, query: {} })),
    useRouter: vi.fn(() => ({ push: vi.fn() }))
}))
vi.mock('@/infrastructure/store/managerWord.store', () => ({
    useManagerWordStore: vi.fn(() => ({
        getAll: vi.fn(),
        words: [],
        computedFilteredWords: [],
        searchGerman: '',
        searchNorsk: '',
        loading: false,
        error: null,
        updateSearchTerm: vi.fn(),
        clearError: vi.fn()
    }))
}))
vi.mock('@/infrastructure/store/alert.store', () => ({
    useAlertStore: vi.fn(() => ({
        showAlert: vi.fn()
    }))
}))
vi.mock('@/ui/views/manager/AddWord.vue', () => ({
    default: { name: 'AddWord', template: '<div data-test="AddWord"></div>' }
}))
vi.mock('@/ui/views/manager/EditWord.vue', () => ({
    default: { name: 'EditWord', template: '<div data-test="EditWord"></div>' }
}))
vi.mock('@/ui/views/manager/FilterWord.vue', () => ({
    default: { name: 'FilterWord', template: '<div data-test="FilterWord"></div>' }
}))
vi.mock('@/ui/components/SpecialCharBox.vue', () => ({
    default: { name: 'SpecialCharBox', template: '<div data-test="SpecialCharBox"></div>' }
}))
describe('ManageWordView.vue', () => {
    it('renders the page headline', async () => {
        const wrapper = mount(ManageWordView)
        await flushPromises()
        expect(wrapper.find('h1').text()).toBe('Wörter verwalten')
    })
    it('renders child components', async () => {
        const wrapper = mount(ManageWordView)
        await flushPromises()
        expect(wrapper.find('[data-test="AddWord"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="EditWord"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="FilterWord"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="SpecialCharBox"]').exists()).toBe(true)
    })
})
