import { useAlertStore } from '@/stores'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { shallowMount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Alert from '@/components/Alert.vue'

describe('Alert.vue', () => {
  let wrapper: ReturnType<typeof shallowMount>
  let store: ReturnType<typeof useAlertStore>

  beforeEach(() => {
    wrapper = shallowMount(Alert, {
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
    store.$patch({ alert: { message: 'positive', type: 'alert-success' } })
    await wrapper.vm.$nextTick() // Wait for reactivity

    const pTags = wrapper.findAll('p')
    expect(pTags.length).toBe(1)
    expect(pTags[0].text()).toBe('positive')
  })

  it('shows no alert if alert is null', async () => {
    store.$patch({ alert: null })
    await wrapper.vm.$nextTick() // Wait for reactivity

    expect(wrapper.findAll('p').length).toBe(0)
    expect(wrapper.find('.alert').exists()).toBe(false)
  })
})
