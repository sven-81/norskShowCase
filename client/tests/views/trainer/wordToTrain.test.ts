import { mount } from '@vue/test-utils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'
import { setActivePinia } from 'pinia'
import WordToTrain from '@/views/trainer/wordToTrain.vue'
import { useTrainerWordStore } from '@/stores/trainerWord.store'

describe('WordToTrain.vue', () => {
  let wrapper
  let store

  beforeEach(() => {
    const pinia = createTestingPinia({
      createSpy: vi.fn,
      initialState: {
        trainerWord: {
          word: { id: 1, german: 'einladen', norsk: 'invitere' },
          loading: false,
          errorMessage: ''
        }
      }
    })

    setActivePinia(pinia)
    store = useTrainerWordStore()

    vi.spyOn(store, 'random')

    wrapper = mount(WordToTrain, {
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

  it('should display the german word when word exists', async () => {
    store.word = { id: 1, german: 'einladen', norsk: 'invitere' }
    store.loading = false

    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('einladen')
  })

  it('should display an error message when errorMessage exists', async () => {
    store.errorMessage = 'Fehler beim Laden des Wortes'
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('⚠️ Fehler beim Laden des Wortes')
  })

  it('should call random method when component is mounted', () => {
    expect(store.random).toHaveBeenCalledTimes(1)
  })
})
