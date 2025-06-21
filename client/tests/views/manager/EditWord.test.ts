import { beforeEach, describe, expect, it, vi } from 'vitest'
import { shallowMount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import EditWords from '@/views/manager/EditWord.vue'
import { replaceSpecialChars } from '@/components/specialChars'

vi.mock('@/components/specialChars', () => ({
  replaceSpecialChars: vi.fn((val: string) =>
    val.replace(/å/g, 'aa').replace(/ø/g, 'oe').replace(/æ/g, 'ae')
  )
}))

describe('EditWords.vue', () => {
  let wrapper: any
  let wordsStoreMock: any

  beforeEach(() => {
    wrapper = shallowMount(EditWords, {
      global: {
        plugins: [
          createTestingPinia({
            createSpy: vi.fn,
            stubActions: false
          })
        ]
      }
    })

    wordsStoreMock = wrapper.vm.wordsStore

    wordsStoreMock.delete = vi.fn()
    wordsStoreMock.update = vi.fn().mockResolvedValue(undefined)
    wordsStoreMock.clearError = vi.fn()
  })

  it('replaces special characters in norsk input', () => {
    const word = { id: 1, norsk: '' }
    const inputEvent = { target: { value: 'blåbær' } }

    wrapper.vm.onNorskInput(word, inputEvent)

    expect(replaceSpecialChars).toHaveBeenCalledWith('blåbær')
    expect(word.norsk).toBe('blaabaer')
  })

  it('caches original word on focus and clears error', () => {
    const word = { id: 1, german: 'Haus', norsk: 'hus' }

    wrapper.vm.handleFocus(word)

    expect(wordsStoreMock.clearError).toHaveBeenCalled()
    expect(wrapper.vm.oldWord[1]).toEqual({ german: 'Haus', norsk: 'hus' })
  })

  it('does not call update if no changes were made', async () => {
    const word = { id: 2, german: 'Tisch', norsk: 'bord' }
    wrapper.vm.oldWord[2] = { german: 'Tisch', norsk: 'bord' }

    await wrapper.vm.doneEdit(word)

    expect(wordsStoreMock.update).not.toHaveBeenCalled()
    expect(wrapper.vm.oldWord[2]).toBeUndefined()
  })

  it('calls update if values changed and deletes cached original afterwards', async () => {
    const word = { id: 3, german: 'Baum', norsk: 'tre' }
    wrapper.vm.oldWord[3] = { german: 'Baum', norsk: 'træ' }

    await wrapper.vm.doneEdit(word)

    expect(wordsStoreMock.update).toHaveBeenCalledWith(word)
    expect(wrapper.vm.oldWord[3]).toBeUndefined()
  })

  it('restores original values and sets error if update fails', async () => {
    const word = { id: 4, german: 'Buch', norsk: 'bok' }
    wrapper.vm.oldWord[4] = { german: 'Buch', norsk: 'bok_old' }

    wordsStoreMock.update = vi.fn(() => Promise.reject(new Error('Update failed')))

    await wrapper.vm.doneEdit(word)

    expect(wordsStoreMock.error).toBe('Update failed')
    expect(word.german).toBe('Buch')
    expect(word.norsk).toBe('bok_old')
    expect(wrapper.vm.oldWord[4]).toBeUndefined()
  })

  it('calls delete action when delete button is clicked', async () => {
    const word = { id: 5, german: 'Fenster', norsk: 'vindu', isDeleting: false }
    wordsStoreMock.computedFilteredWords = [word]

    // Re-mount with edited props
    wrapper = shallowMount(EditWords, {
      global: {
        plugins: [
          createTestingPinia({
            createSpy: vi.fn,
            stubActions: false
          })
        ]
      }
    })

    wordsStoreMock = wrapper.vm.wordsStore
    wordsStoreMock.computedFilteredWords = [word]
    wordsStoreMock.delete = vi.fn()

    await wrapper.vm.$nextTick()

    const deleteButtons = wrapper.findAll('button.button-delete')
    expect(deleteButtons.length).toBeGreaterThan(0)

    await deleteButtons[0].trigger('click')

    expect(wordsStoreMock.delete).toHaveBeenCalledWith(5)
  })

  it('renders error message when wordsStore.error is set', async () => {
    wordsStoreMock.error = 'Test error'
    await wrapper.vm.$nextTick()

    expect(wrapper.html()).toContain('Test error')
  })

  it('renders loading spinner when wordsStore.loading is true', async () => {
    wordsStoreMock.loading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('.spinner').exists()).toBe(true)
  })
})
