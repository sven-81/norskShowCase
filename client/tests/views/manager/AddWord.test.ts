import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import AddWord from '@/views/manager/AddWord.vue'
import { createTestingPinia } from '@pinia/testing'
import { useAlertStore, useManagerWordStore } from '@/stores'
import { createMemoryHistory, createRouter } from 'vue-router'

const router = createRouter({
    history: createMemoryHistory(),
    routes: [{path: '/:id', component: AddWord}]
})

vi.mock('@/components/specialChars', () => ({
    replaceSpecialChars: (str: string) => str.replace(/ä/g, 'a').replace(/ü/g, 'u').replace(/ö/g, 'o')
}))

describe('AddWord.vue', () => {
    let addMock: ReturnType<typeof vi.fn>
    let successMock: ReturnType<typeof vi.fn>
    let mapWordErrorMock: ReturnType<typeof vi.fn>

    beforeEach(async () => {
        addMock = vi.fn().mockResolvedValue(undefined)
        successMock = vi.fn()
        mapWordErrorMock = vi.fn()

        if (!router.isReady()) await router.isReady()
    })

    function mountWithStore() {
        const wrapper = mount(AddWord, {
            global: {
                plugins: [
                    router,
                    createTestingPinia({
                        stubActions: false,
                        createSpy: vi.fn
                    })
                ],
                stubs: {
                    'router-link': true
                }
            }
        })

        const wordStore = useManagerWordStore()
        wordStore.add = addMock

        const alertStore = useAlertStore()
        alertStore.success = successMock
        alertStore.mapWordError = mapWordErrorMock

        return wrapper
    }

    it('renders form fields and button', () => {
        const wrapper = mountWithStore()

        expect(wrapper.find('h2').text()).toBe('Wörter hinzufügen')
        expect(wrapper.findAll('input[type="text"]').length).toBe(2)

        expect(wrapper.find('input[name="german"]').exists()).toBe(true)
        expect(wrapper.find('input#norsk').exists()).toBe(true)

        expect(wrapper.find('button').text()).toBe('hinzufügen')
    })

    it('shows validation errors if required fields are empty', async () => {
        const wrapper = mountWithStore()

        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

        const errors = wrapper.findAll('.invalid-feedback')
        expect(errors.length).toBe(2)
        expect(errors[0].text()).toBe('Deutsch muss ausgefüllt sein')
        expect(errors[1].text()).toBe('Norwegisch muss ausgefüllt sein')

        expect(addMock).not.toHaveBeenCalled()
    })

    it('calls add with cleaned input and triggers success alert', async () => {
        const wrapper = mountWithStore()

        await wrapper.find('input[name="german"]').setValue('laufen')
        await wrapper.find('input#norsk').setValue('läufen') // ä -> a

        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

        expect(addMock).toHaveBeenCalledTimes(1)
        expect(addMock).toHaveBeenCalledWith({german: 'laufen', norsk: 'laufen'})

        expect(successMock).toHaveBeenCalledTimes(1)
        expect(successMock).toHaveBeenCalledWith('Das Wort "laufen" | "laufen" wurde hinzugefügt')
    })

    it('calls mapWordError if add rejects', async () => {
        addMock = vi.fn().mockRejectedValue(new Error('Fail'))
        const wrapper = mountWithStore()

        const wordStore = useManagerWordStore()
        wordStore.add = addMock

        await wrapper.find('input[name="german"]').setValue('laufen')
        await wrapper.find('input#norsk').setValue('laufen')

        await wrapper.find('form').trigger('submit.prevent')
        await flushPromises()
        await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

        expect(mapWordErrorMock).toHaveBeenCalledTimes(1)
    })

    it('shows spinner when submitting', async () => {
        const wrapper = mountWithStore()

        expect(wrapper.find('.spinner').isVisible()).toBe(false)

        wrapper.find('form').trigger('submit.prevent')
        await flushPromises()

        expect(wrapper.find('.spinner').exists()).toBe(true)
    })
})
