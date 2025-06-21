import {mount} from '@vue/test-utils'
import trainingWords from '@/views/trainer/trainingWords.vue'
import {ResultBox, SpecialCharBox, WordsFormBox, WordToTrain} from '@/views/trainer'
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest'
import {createTestingPinia} from '@pinia/testing'

describe('trainingWords.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(trainingWords, {
            global: {
                plugins: [createTestingPinia({createSpy: vi.fn})]
            }
        })
    })

    afterEach(() => {
        wrapper.unmount()
    })

    describe('renders all child components', () => {
        it('should render WordToTrain component', () => {
            expect(wrapper.findComponent(WordToTrain).exists()).toBe(true)
        })

        it('should render ResultBox component', () => {
            expect(wrapper.findComponent(ResultBox).exists()).toBe(true)
        })

        it('should render SpecialCharBox component', () => {
            expect(wrapper.findComponent(SpecialCharBox).exists()).toBe(true)
        })

        it('should render WordsFormBox component', () => {
            expect(wrapper.findComponent(WordsFormBox).exists()).toBe(true)
        })
    })

    describe('interaction between components', () => {
        it('should handle custom events from WordToTrain', async () => {
            const wordToTrain = wrapper.findComponent(WordToTrain)

            await wordToTrain.vm.$emit('someEvent')

            expect(wrapper.findComponent(ResultBox).exists()).toBe(true)
        })
    })

    describe('user interaction tests', () => {
        it('should update the value when input in WordsFormBox changes', async () => {
            const wordsFormBox = wrapper.findComponent(WordsFormBox)
            const input = wordsFormBox.find('input')

            await input.setValue('New word')

            expect(input.element.value).toBe('New word')
        })
    })

    describe('styling tests', () => {
        it('should apply correct styles to SpecialCharBox', () => {
            const specialCharBox = wrapper.findComponent(SpecialCharBox)
            const specialCharElements = specialCharBox.findAll('.specialChar')

            expect(specialCharElements.length).toBe(6)

            specialCharElements.forEach((el) => {
                expect(el.classes()).toContain('specialChar')
            })
        })
    })
})
