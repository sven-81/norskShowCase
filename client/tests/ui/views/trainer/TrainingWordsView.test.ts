import { describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import TrainingWordsView from '@/ui/views/trainer/TrainingWordsView.vue'

vi.mock('@/ui/views/trainer/WordToTrain.vue', () => ({
    default: { name: 'WordToTrain', template: '<div data-test="WordToTrain"></div>' }
}))
vi.mock('@/ui/views/trainer/ResultBox.vue', () => ({
    default: { name: 'ResultBox', template: '<div data-test="ResultBox"></div>' }
}))
vi.mock('@/ui/components/SpecialCharBox.vue', () => ({
    default: { name: 'SpecialCharBox', template: '<div data-test="SpecialCharBox"></div>' }
}))
vi.mock('@/ui/views/trainer/WordsFormBox.vue', () => ({
    default: { name: 'WordsFormBox', template: '<div data-test="WordsFormBox"></div>' }
}))

describe('TrainingWordsView.vue', () => {
    it('renders all child components', () => {
        const wrapper = mount(TrainingWordsView, {
            global: {
                plugins: [createTestingPinia({ createSpy: vi.fn })]
            }
        })

        expect(wrapper.find('[data-test="WordToTrain"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="ResultBox"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="SpecialCharBox"]').exists()).toBe(true)
        expect(wrapper.find('[data-test="WordsFormBox"]').exists()).toBe(true)
    })
})

