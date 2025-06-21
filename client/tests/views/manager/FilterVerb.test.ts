import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import FilterVerb from '@/views/manager/FilterVerb.vue'
import { useManagerVerbStore } from '@/stores'
import { replaceSpecialChars } from '@/components/specialChars'
import { ref } from 'vue'

vi.mock('@/stores', () => ({
  useManagerVerbStore: vi.fn()
}))

vi.mock('@/components/specialChars', () => ({
  replaceSpecialChars: vi.fn((val) => val)
}))

describe('FilterVerb.vue', () => {
  let updateSearchTerm: ReturnType<typeof vi.fn>
  let searchGermanRef = ref('')
  let searchNorskRef = ref('')

  beforeEach(() => {
    updateSearchTerm = vi.fn()
    searchGermanRef = ref('')
    searchNorskRef = ref('')

    ;(useManagerVerbStore as unknown as vi.Mock).mockReturnValue({
      get searchGerman() {
        return searchGermanRef.value
      },
      set searchGerman(val) {
        searchGermanRef.value = val
      },
      get searchNorsk() {
        return searchNorskRef.value
      },
      set searchNorsk(val) {
        searchNorskRef.value = val
      },
      updateSearchTerm
    })
  })

  it('binds German input to store', async () => {
    const wrapper = mount(FilterVerb)
    const input = wrapper.find('input[name="german-search"]')

    await input.setValue('laufen')
    expect(input.element.value).toBe('laufen')
    expect(searchGermanRef.value).toBe('laufen')
  })

  it('binds Norsk input and transforms characters', async () => {
    const wrapper = mount(FilterVerb)
    const input = wrapper.find('#filter-norsk')

    ;(replaceSpecialChars as vi.Mock).mockReturnValueOnce('converted')

    await input.setValue('löp')
    expect(replaceSpecialChars).toHaveBeenCalledWith('löp')
    expect(searchNorskRef.value).toBe('converted')
  })

  it('calls updateSearchTerm on German Enter', async () => {
    const wrapper = mount(FilterVerb)
    const input = wrapper.find('input[name="german-search"]')

    await input.setValue('laufen')
    await input.trigger('keyup.enter')

    expect(updateSearchTerm).toHaveBeenCalledWith('laufen', 'DE')
  })

  it('calls updateSearchTerm on Norsk Enter', async () => {
    const wrapper = mount(FilterVerb)
    const input = wrapper.find('#filter-norsk')

    ;(replaceSpecialChars as vi.Mock).mockReturnValueOnce('løp')

    await input.setValue('løp')
    await input.trigger('keyup.enter')

    expect(updateSearchTerm).toHaveBeenCalledWith('løp', 'NO')
  })

  it('clears search terms when clicking "Filter aufheben"', async () => {
    const wrapper = mount(FilterVerb)
    const button = wrapper.find('button')

    await button.trigger('click')

    expect(updateSearchTerm).toHaveBeenCalledWith('', 'none')
  })
})
