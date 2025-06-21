import { mount } from '@vue/test-utils'
import trainingVerbs from '@/views/trainer/trainingVerbs.vue'
import { ResultBox, SpecialCharBox, VerbsFormBox, VerbToTrain } from '@/views/trainer'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'

describe('VerbsTraining.vue', () => {
  let wrapper

  beforeEach(() => {
    wrapper = mount(trainingVerbs, {
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })]
      }
    })
  })

  afterEach(() => {
    wrapper.unmount()
  })

  describe('renders all child components', () => {
    it('should render VerbToTrain component', () => {
      expect(wrapper.findComponent(VerbToTrain).exists()).toBe(true)
    })

    it('should render ResultBox component', () => {
      expect(wrapper.findComponent(ResultBox).exists()).toBe(true)
    })

    it('should render SpecialCharBox component', () => {
      expect(wrapper.findComponent(SpecialCharBox).exists()).toBe(true)
    })

    it('should render VerbsFormBox component', () => {
      expect(wrapper.findComponent(VerbsFormBox).exists()).toBe(true)
    })
  })

  describe('interaction between components', () => {
    it('should handle custom events from VerbToTrain', async () => {
      const verbToTrain = wrapper.findComponent(VerbToTrain)

      await verbToTrain.vm.$emit('trainVerb', 'laufen')

      expect(wrapper.findComponent(ResultBox).exists()).toBe(true)
    })
  })

  describe('user interaction tests', () => {
    it('should update the value when input in VerbsFormBox changes', async () => {
      const verbsFormBox = wrapper.findComponent(VerbsFormBox)
      const input = verbsFormBox.find('input')

      await input.setValue('springen')

      expect(input.element.value).toBe('springen')
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
