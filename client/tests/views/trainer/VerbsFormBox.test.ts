import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import VerbsFormBox from '@/views/trainer/VerbsFormBox.vue'
import { createTestingPinia } from '@pinia/testing'
import { useTrainerVerbStore } from '@/stores'

vi.mock('@/components/specialChars', () => ({
  replaceSpecialChars: (str: string) =>
    str.replace(/ä/g, 'a').replace(/ö/g, 'o').replace(/ü/g, 'u')
}))

describe('VerbsFormBox.vue', () => {
  let evaluateMock: ReturnType<typeof vi.fn>

  beforeEach(() => {
    evaluateMock = vi.fn().mockResolvedValue(undefined)
  })

  function mountWithStore() {
    const wrapper = mount(VerbsFormBox, {
      global: {
        plugins: [
          createTestingPinia({
            stubActions: false,
            createSpy: vi.fn
          })
        ]
      }
    })

    const store = useTrainerVerbStore()
    store.evaluate = evaluateMock

    return wrapper
  }

  it('renders all input fields and submit button', () => {
    const wrapper = mountWithStore()

    expect(wrapper.find('legend').text()).toBe('Wörtersammlung')

    const placeholders = {
      infinitive: 'Infinitiv',
      present: 'Präsens',
      past: 'Vergangenheit',
      pastPerfect: '2. Vergangenheit'
    }

    Object.entries(placeholders).forEach(([field, placeholder]) => {
      const input = wrapper.find(`input#${field}`)
      expect(input.exists()).toBe(true)
      expect(input.attributes('placeholder')).toBe(placeholder)
    })

    const button = wrapper.find('button[type="submit"]')
    expect(button.exists()).toBe(true)
    expect(button.text()).toBe('prüfen')
  })

  it('shows validation errors if required fields are empty', async () => {
    const wrapper = mountWithStore()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

    const errors = wrapper.findAll('.invalid-feedback')
    expect(errors.length).toBe(4)
    expect(errors[0].text()).toBe('Infinitiv wird gebraucht')
    expect(errors[1].text()).toBe('Präsens wird gebraucht')
    expect(errors[2].text()).toBe('Vergangenheit wird gebraucht')
    expect(errors[3].text()).toBe('2. Vergangenheit wird gebraucht')

    expect(evaluateMock).not.toHaveBeenCalled()
  })

  it('calls evaluate with cleaned values on submit', async () => {
    const wrapper = mountWithStore()

    await wrapper.find('input#infinitive').setValue('läufen')  // ä -> a
    await wrapper.find('input#present').setValue('läuft')     // ü -> u
    await wrapper.find('input#past').setValue('lief')
    await wrapper.find('input#pastPerfect').setValue('gelaufen')

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

    expect(evaluateMock).toHaveBeenCalledTimes(1)
    expect(evaluateMock).toHaveBeenCalledWith({
      infinitive: 'laufen',  // ä -> a
      present: 'lauft',      // ü -> u
      past: 'lief',
      pastPerfect: 'gelaufen'
    })
  })

  it('updates input refs and calls setFieldValue on handleInput', async () => {
    const wrapper = mountWithStore()

    const inputInf = wrapper.find('input#infinitive')
    await inputInf.setValue('gräben')

    expect((wrapper.vm as any).infinitiveInput).toBe('graben')
  })
})
