import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ResultBox from '@/views/trainer/ResultBox.vue'
import { useResultStore } from '@/stores'

describe('ResultBox.vue', () => {
  let wrapper
  let store

  afterEach(() => {
    wrapper.unmount()
  })

  describe('renders with no result', () => {
    beforeEach(() => {
      wrapper = mount(ResultBox, {
        global: {
          plugins: [createTestingPinia({ createSpy: vi.fn })]
        }
      })
      store = useResultStore()
    })

    it('should not render correct or mistake', () => {
      expect(wrapper.find('#correct').exists()).toBe(false)
      expect(wrapper.find('#mistake').exists()).toBe(false)
    })
  })

  describe('renders correct result properly', () => {
    beforeEach(async () => {
      wrapper = mount(ResultBox, {
        global: {
          plugins: [createTestingPinia({ createSpy: vi.fn })]
        }
      })
      store = useResultStore()
      store.$patch({
        result: {
          message: 'fine',
          type: 'correct'
        }
      })
      await flushPromises()
    })

    it('should render correct message and class', () => {
      const correctDiv = wrapper.find('#correct')
      expect(correctDiv.exists()).toBe(true)
      expect(correctDiv.classes()).toContain('correct')
      expect(correctDiv.text()).toBe('fine')
      expect(wrapper.find('#mistake').exists()).toBe(false)
    })
  })

  describe('renders mistake result properly', () => {
    beforeEach(async () => {
      wrapper = mount(ResultBox, {
        global: {
          plugins: [createTestingPinia({ createSpy: vi.fn })]
        }
      })
      store = useResultStore()
      store.$patch({
        result: {
          message: 'möööp',
          type: 'mistake'
        }
      })
      await flushPromises()
    })

    it('should render mistake message and class', () => {
      const mistakeDiv = wrapper.find('#mistake')
      expect(mistakeDiv.exists()).toBe(true)
      expect(mistakeDiv.classes()).toContain('mistake')
      expect(mistakeDiv.find('ul').text()).toContain('möööp')
      expect(wrapper.find('#correct').exists()).toBe(false)
    })
  })
})

describe('ResultBox.vue - dynamic result switching', () => {
  let wrapper
  let store

  beforeEach(async () => {
    wrapper = mount(ResultBox, {
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })]
      }
    })

    store = useResultStore()
    store.$patch({
      result: {
        message: 'Good job!',
        type: 'correct'
      }
    })

    await flushPromises()
  })

  afterEach(() => {
    wrapper.unmount()
  })

  it('updates DOM when result changes from correct to mistake', async () => {
    // initial state: correct
    expect(wrapper.find('#correct').exists()).toBe(true)
    expect(wrapper.find('#correct').text()).toContain('Good job!')
    expect(wrapper.find('#mistake').exists()).toBe(false)

    // update to mistake
    store.result = {
      message: '<li>Error 1</li><li>Error 2</li>',
      type: 'mistake'
    }

    await flushPromises()

    // new state: mistake
    expect(wrapper.find('#correct').exists()).toBe(false)
    const mistakeDiv = wrapper.find('#mistake')
    expect(mistakeDiv.exists()).toBe(true)
    expect(mistakeDiv.find('ul').html()).toContain('<li>Error 1</li>')
    expect(mistakeDiv.find('ul').html()).toContain('<li>Error 2</li>')
  })
})
