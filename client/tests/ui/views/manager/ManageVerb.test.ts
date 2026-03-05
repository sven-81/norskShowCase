import { flushPromises, mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import ManageVerbView from '@/ui/views/manager/ManageVerbView.vue'
vi.mock('vue-router', () => ({
    useRoute: vi.fn(() => ({ path: '/', params: {}, query: {} })),
    useRouter: vi.fn(() => ({ push: vi.fn() }))
}))
vi.mock('@/infrastructure/store/managerVerb.store', () => ({
    useManagerVerbStore: vi.fn(() => ({
        getAll: vi.fn(),
        verbs: [],
        computedFilteredVerbs: [],
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
vi.mock('@/ui/views/manager/AddVerb.vue', () => ({
    default: { name: 'AddVerb', template: '<div data-test="AddVerb"></div>' }
}))
vi.mock('@/ui/views/manager/EditVerb.vue', () => ({
    default: { name: 'EditVerb', template: '<div data-test="EditVerb"></div>' }
}))
vi.mock('@/ui/views/manager/FilterVerb.vue', () => ({
    default: { name: 'FilterVerb', template: '<div data-test="FilterVerb"></div>' }
}))
vi.mock('@/ui/components/SpecialCharBox.vue', () => ({
    default: { name: 'SpecialCharBox', template: '<div data-test="SpecialCharBox"></div>' }
}))
describe('ManageVerbView.vue', () => {
    it('renders the page headline', async () => {
        const wrapper = mount(ManageVerbView)
        await flushPromises()
        expect(wrapper.find('h1').text()).toBe('Verben verwalten')
    })
    it('renders child components', async () => {
        const wrapper = mount(ManageVerbView)
        await flushPromises()
        expect(wrapper.find('[data-test="AddVerb"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="EditVerb"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="FilterVerb"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="SpecialCharBox"]').exists()).toBe(true)
    })
})
