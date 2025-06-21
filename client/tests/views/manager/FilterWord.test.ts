import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import FilterWord from '@/views/manager/FilterWord.vue'
import { useManagerWordStore } from '@/stores'
import { replaceSpecialChars } from '@/components/specialChars'
import { ref } from 'vue'

vi.mock('@/stores', () => ({
  useManagerWordStore: vi.fn()
}))

vi.mock('@/components/specialChars', () => ({
  replaceSpecialChars: vi.fn((val) => val)
}))

describe('FilterWord.vue', () => {
  let updateSearchTerm: ReturnType<typeof vi.fn>
  let searchGermanRef = ref('')
  let searchNorskRef = ref('')

  beforeEach(() => {
    updateSearchTerm = vi.fn()
    searchGermanRef = ref('')
    searchNorskRef = ref('')

    ;(useManagerWordStore as unknown as vi.Mock).mockReturnValue({
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
    const wrapper = mount(FilterWord)
    const input = wrapper.find('input[name="german-search"]')

    await input.setValue('Haus')
    expect(input.element.value).toBe('Haus')
    expect(searchGermanRef.value).toBe('Haus')
  })

  it('binds Norsk input and transforms characters', async () => {
    const wrapper = mount(FilterWord)
    const input = wrapper.find('#filter-norsk')

    ;(replaceSpecialChars as vi.Mock).mockReturnValueOnce('converted')

    await input.setValue('år')
    expect(replaceSpecialChars).toHaveBeenCalledWith('år')
    expect(searchNorskRef.value).toBe('converted')
  })

  it('calls updateSearchTerm on German Enter', async () => {
    const wrapper = mount(FilterWord)
    const input = wrapper.find('input[name="german-search"]')

    await input.setValue('Buch')
    await input.trigger('keyup.enter')

    expect(updateSearchTerm).toHaveBeenCalledWith('Buch', 'DE')
  })

  it('calls updateSearchTerm on Norsk Enter', async () => {
    const wrapper = mount(FilterWord)
    const input = wrapper.find('#filter-norsk')

    ;(replaceSpecialChars as vi.Mock).mockReturnValueOnce('bok')

    await input.setValue('bok')
    await input.trigger('keyup.enter')

    expect(updateSearchTerm).toHaveBeenCalledWith('bok', 'NO')
  })

  it('clears search terms when clicking "Filter aufheben"', async () => {
    const wrapper = mount(FilterWord)
    const button = wrapper.find('button')

    await button.trigger('click')

    expect(updateSearchTerm).toHaveBeenCalledWith('', 'none')
  })
})
