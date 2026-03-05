import { useAlertStore } from '@/infrastructure/store/alert.store'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { shallowMount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import AlertComponent from '@/ui/components/AlertComponent.vue'

describe('AlertComponent.vue', () => {
    let wrapper: ReturnType<typeof shallowMount>
    let store: ReturnType<typeof useAlertStore>

    beforeEach(() => {
        wrapper = shallowMount(AlertComponent, {
            global: {
                plugins: [
                    createTestingPinia({
                        createSpy: vi.fn
                    })
                ]
            }
        })
        store = useAlertStore()
    })

    afterEach(() => {
        wrapper.unmount()
    })

    it('displays alert with message if alert is present', async () => {
        store.alert = { message: 'Test alert', type: 'alert-success' }
        await wrapper.vm.$nextTick()

        const alertDiv = wrapper.find('.alert-success')
        expect(alertDiv.exists()).toBe(true)
        expect(alertDiv.text()).toContain('Test alert')
    })

    it('displays dismiss button', async () => {
        store.alert = { message: 'Test alert', type: 'alert-success' }
        await wrapper.vm.$nextTick()

        const closeButton = wrapper.find('.close')
        expect(closeButton.exists()).toBe(true)
        expect(closeButton.text()).toBe('×')
    })

    it('clears alert when dismiss button is clicked', async () => {
        store.alert = { message: 'Dismiss me', type: 'alert-danger' }
        await wrapper.vm.$nextTick()

        const closeButton = wrapper.find('.close')
        await closeButton.trigger('click')

        expect(store.clear).toHaveBeenCalled()
    })

    it('shows no alert if alert is null', () => {
        store.alert = null
        expect(wrapper.find('.alert-success').exists()).toBe(false)
        expect(wrapper.find('.alert-danger').exists()).toBe(false)
    })
})

