import { mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import { setActivePinia } from 'pinia'
import VerbToTrain from '@/views/trainer/verbToTrain.vue'
import { useTrainerVerbStore } from '@/stores/trainerVerb.store'

describe('VerbToTrain.vue', () => {
  let wrapper
  let store

  beforeEach(() => {
    const pinia = createTestingPinia({
      createSpy: vi.fn,
      initialState: {
        trainerVerb: {
          verb: { id: 1, german: 'essen', norsk: 'spise' },
          german: 'essen',
          loading: false,
          errorMessage: ''
        }
      }
    })

    setActivePinia(pinia)
    store = useTrainerVerbStore()

    vi.spyOn(store, 'random')

    wrapper = mount(VerbToTrain, {
      global: {
        plugins: [pinia]
      }
    })
  })

  afterEach(() => {
    wrapper.unmount()
  })

  it('should display a loading spinner when loading is true', async () => {
    store.loading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('.spinner').exists()).toBe(true)
    expect(wrapper.text()).toContain('ich lade')
  })

  it('should display the german verb when verb exists', async () => {
    store.verb = { id: 1, german: 'essen', norsk: 'spise' }
    store.german = 'essen'
    store.loading = false

    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('essen')
  })

  it('should display an error message when errorMessage exists', async () => {
    store.errorMessage = 'Fehler beim Laden des Verbs'
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('⚠️ Fehler beim Laden des Verbs')
  })

  it('should call random method when component is mounted', () => {
    expect(store.random).toHaveBeenCalledTimes(1)
  })
})
