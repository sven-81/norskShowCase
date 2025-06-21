import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import AddVerb from '@/views/manager/AddVerb.vue'
import { createTestingPinia } from '@pinia/testing'
import { useAlertStore, useManagerVerbStore } from '@/stores'
import { createMemoryHistory, createRouter } from 'vue-router'
import { nextTick } from 'vue'

vi.mock('@/components/specialChars', () => ({
  replaceSpecialChars: (str: string) => str.replace(/ä/g, 'a').replace(/ü/g, 'u').replace(/ö/g, 'o')
}))

let router

describe('AddVerb.vue', () => {
  let addMock: ReturnType<typeof vi.fn>
  let successMock: ReturnType<typeof vi.fn>
  let mapVerbErrorMock: ReturnType<typeof vi.fn>

  beforeEach(async () => {
    router = createRouter({
      history: createMemoryHistory(),
      routes: [{ path: '/:id', component: AddVerb }]
    })
    await router.push('/123')
    await router.isReady()

    addMock = vi.fn().mockResolvedValue(undefined)
    successMock = vi.fn()
    mapVerbErrorMock = vi.fn()
  })

  function mountWithStore() {
    const wrapper = mount(AddVerb, {
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

    const verbStore = useManagerVerbStore()
    verbStore.add = addMock

    const alertStore = useAlertStore()
    alertStore.success = successMock
    alertStore.mapVerbError = mapVerbErrorMock

    return wrapper
  }

  it('renders the form with all fields and button', () => {
    const wrapper = mountWithStore()

    expect(wrapper.find('h2').text()).toBe('Verben hinzufügen')
    expect(wrapper.findAll('input[type="text"]').length).toBe(5)

    expect(wrapper.find('input[name="german"]').exists()).toBe(true)
    expect(wrapper.find('input[name="norsk"]').exists()).toBe(true)
    expect(wrapper.find('input[name="norskPresent"]').exists()).toBe(true)
    expect(wrapper.find('input[name="norskPast"]').exists()).toBe(true)
    expect(wrapper.find('input[name="norskPastPerfect"]').exists()).toBe(true)

    expect(wrapper.find('button').text()).toBe('hinzufügen')
  })

  it('shows validation errors if required fields are empty', async () => {
    const wrapper = mountWithStore()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

    const errors = wrapper.findAll('.invalid-feedback')
    expect(errors.length).toBe(5)
    expect(errors[0].text()).toBe('Deutsch muss ausgefüllt sein')
    expect(errors[1].text()).toBe('Norwegischer Infinitiv muss ausgefüllt sein')
    expect(errors[2].text()).toBe('Norwegisch Präsens muss ausgefüllt sein')
    expect(errors[3].text()).toBe('Norwegisch Vergangenheit muss ausgefüllt sein')
    expect(errors[4].text()).toBe('Norwegisch 2. Vergangenheit muss ausgefüllt sein')

    expect(addMock).not.toHaveBeenCalled()
  })

  it('calls add with cleaned values and triggers success alert', async () => {
    const wrapper = mountWithStore()

    await wrapper.find('input[name="german"]').setValue('laufen')
    await wrapper.find('input[name="norsk"]').setValue('läufen') // ä -> a
    await wrapper.find('input[name="norskPresent"]').setValue('läuft') // ü -> u
    await wrapper.find('input[name="norskPast"]').setValue('lief')
    await wrapper.find('input[name="norskPastPerfect"]').setValue('gelaufen')

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

    expect(addMock).toHaveBeenCalledTimes(1)
    expect(addMock).toHaveBeenCalledWith({
      german: 'laufen',
      norsk: 'laufen',
      norskPresent: 'lauft',
      norskPast: 'lief',
      norskPastPerfect: 'gelaufen'
    })

    expect(successMock).toHaveBeenCalledTimes(1)
    expect(successMock).toHaveBeenCalledWith('Das Verb "laufen" | "laufen" wurde hinzugefügt')
  })

  it('calls mapVerbError if add rejects', async () => {
    addMock = vi.fn().mockRejectedValue(new Error('Fail'))
    const wrapper = mountWithStore()

    const verbStore = useManagerVerbStore()
    verbStore.add = addMock

    await wrapper.find('input[name="german"]').setValue('laufen')
    await wrapper.find('input[name="norsk"]').setValue('laufen')
    await wrapper.find('input[name="norskPresent"]').setValue('läuft')
    await wrapper.find('input[name="norskPast"]').setValue('lief')
    await wrapper.find('input[name="norskPastPerfect"]').setValue('gelaufen')

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()
    await new Promise((r) => setTimeout(r, 10)) // needed by VeeValidate

    expect(mapVerbErrorMock).toHaveBeenCalledTimes(1)
  })

  it('shows spinner when submitting', async () => {
    const wrapper = mountWithStore()

    expect(wrapper.find('.spinner').isVisible()).toBe(false)

    const submitPromise = wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.find('.spinner').exists()).toBe(true)

    await submitPromise
    await flushPromises()
    await wrapper.vm.$nextTick()

    expect(wrapper.find('.spinner').isVisible()).toBe(false)
  })

  it('updates norsk ref value when replaceSpecialChars changes it', async () => {
    const wrapper = mountWithStore()
    wrapper.vm.norsk = 'läufen' // ä -> a

    await nextTick()
    expect(wrapper.vm.norsk).toBe('laufen')
  })

  it('does NOT update norsk ref value when replaceSpecialChars returns same value', async () => {
    const wrapper = mountWithStore()
    wrapper.vm.norsk = 'laufen' // no special chars

    await nextTick()
    expect(wrapper.vm.norsk).toBe('laufen')
  })

  it('updates norskPresent ref value when replaceSpecialChars changes it', async () => {
    const wrapper = mountWithStore()
    wrapper.vm.norskPresent = 'läuft' // ü -> u

    await nextTick()
    expect(wrapper.vm.norskPresent).toBe('lauft')
  })

  it('updates norskPast ref value when replaceSpecialChars changes it', async () => {
    const wrapper = mountWithStore()
    wrapper.vm.norskPast = 'päste' // ä -> a

    await nextTick()
    expect(wrapper.vm.norskPast).toBe('paste')
  })

  it('updates norskPastPerfect ref value when replaceSpecialChars changes it', async () => {
    const wrapper = mountWithStore()
    wrapper.vm.norskPastPerfect = 'gespürt' // ü -> u

    await nextTick()
    expect(wrapper.vm.norskPastPerfect).toBe('gespurt')
  })
})
