import { WordsFormBox } from '@/views/trainer'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { useTrainerWordStore } from '@/stores'

vi.mock('@/components/specialChars', () => ({
    replaceSpecialChars: (str: string) => str.replace(/ä/g, 'a').replace(/ö/g, 'o') // einfacher Mock
}))

describe('WordsFormBox.vue', () => {
    let evaluateMock: ReturnType<typeof vi.fn>

    beforeEach(() => {
        evaluateMock = vi.fn().mockResolvedValue(undefined)
    })

    function mountWithStore() {
        const wrapper = mount(WordsFormBox, {
            global: {
                plugins: [
                    createTestingPinia({
                        stubActions: false,
                        createSpy: vi.fn
                    })
                ]
            }
        })

        const store = useTrainerWordStore()
        store.evaluate = evaluateMock

        return wrapper
    }

    it('renders the form with norsk input and submit button', () => {
        const wrapper = mountWithStore()

        expect(wrapper.find('legend').text()).toBe('Wörtersammlung')
        const input = wrapper.find('input#norsk')
        expect(input.exists()).toBe(true)
        expect(input.attributes('placeholder')).toBe('Norsk')

        const button = wrapper.find('button[type="submit"]')
        expect(button.exists()).toBe(true)
        expect(button.text()).toBe('prüfen')
    })

    it('shows validation error if norsk input is empty and form submitted', async () => {
        const wrapper = mountWithStore()

        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        await new Promise(r => setTimeout(r, 10)) // needed by VeeValidate

        const error = wrapper.find('.invalid-feedback')
        expect(error.exists()).toBe(true)
        expect(error.text()).toBe('Bitte gebe oben das norwegische Wort ein.')

        expect(evaluateMock).not.toHaveBeenCalled()
    })

    it('calls evaluate with cleaned norsk input when form is submitted', async () => {
        const wrapper = mountWithStore()
        const input = wrapper.find('input#norsk')

        await input.setValue('kärö')
        await wrapper.find('form').trigger('submit.prevent')

        await flushPromises()
        await new Promise(r => setTimeout(r, 10)) // needed by VeeValidate

        expect(evaluateMock).toHaveBeenCalledTimes(1)
        expect(evaluateMock).toHaveBeenCalledWith('karo')
    })

    it('updates inputChar with replaced special characters on input', async () => {
        const wrapper = mountWithStore()
        const input = wrapper.find('input#norsk')

        await input.setValue('bär')

        expect((wrapper.vm as any).inputChar).toBe('bar')
    })
})
