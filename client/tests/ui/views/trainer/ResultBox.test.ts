import { beforeEach, describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { setActivePinia } from 'pinia'
import ResultBox from '@/ui/views/trainer/ResultBox.vue'
import { useResultStore } from '@/infrastructure/store/trainingResult.store'

describe('ResultBox.vue', () => {
    let store: ReturnType<typeof useResultStore>

    function mountComponent() {
        const pinia = createTestingPinia({
            createSpy: vi.fn,
            stubActions: false
        })
        setActivePinia(pinia)
        store = useResultStore()

        return mount(ResultBox, {
            global: {
                plugins: [pinia]
            }
        })
    }

    it('shows nothing when type is empty', () => {
        const wrapper = mountComponent()

        expect(wrapper.find('#correct').exists()).toBe(false)
        expect(wrapper.find('#mistake').exists()).toBe(false)
    })

    it('shows correct result when type is "correct"', async () => {
        const wrapper = mountComponent()
        store.type = 'correct'
        store.resultMessage = 'Alles richtig! 🎉'

        await wrapper.vm.$nextTick()

        const correctDiv = wrapper.find('#correct')
        expect(correctDiv.exists()).toBe(true)
        expect(correctDiv.text()).toContain('Alles richtig!')
        expect(correctDiv.classes()).toContain('correct')
    })

    it('shows mistake result when type is "mistake"', async () => {
        const wrapper = mountComponent()
        store.type = 'mistake'
        store.resultMessage = '<div>Oh no 😢</div><div>Falsch</div>'

        await wrapper.vm.$nextTick()

        const mistakeDiv = wrapper.find('#mistake')
        expect(mistakeDiv.exists()).toBe(true)
        expect(mistakeDiv.classes()).toContain('mistake')
        expect(mistakeDiv.html()).toContain('Oh no')
    })
})

