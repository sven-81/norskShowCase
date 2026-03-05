import { describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import TrainingVerbsView from '@/ui/views/trainer/TrainingVerbsView.vue'

vi.mock('@/ui/views/trainer/VerbToTrain.vue', () => ({
    default: { name: 'VerbToTrain', template: '<div data-test="VerbToTrain"></div>' }
}))
vi.mock('@/ui/views/trainer/ResultBox.vue', () => ({
    default: { name: 'ResultBox', template: '<div data-test="ResultBox"></div>' }
}))
vi.mock('@/ui/components/SpecialCharBox.vue', () => ({
    default: { name: 'SpecialCharBox', template: '<div data-test="SpecialCharBox"></div>' }
}))
vi.mock('@/ui/views/trainer/VerbsFormBox.vue', () => ({
    default: { name: 'VerbsFormBox', template: '<div data-test="VerbsFormBox"></div>' }
}))

describe('TrainingVerbsView.vue', () => {
    it('renders all child components', () => {
        const wrapper = mount(TrainingVerbsView, {
            global: {
                plugins: [createTestingPinia({ createSpy: vi.fn })]
            }
        })

        expect(wrapper.find('[data-test="VerbToTrain"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="ResultBox"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="SpecialCharBox"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="VerbsFormBox"]').exists()).toBe(true)
    })
})

