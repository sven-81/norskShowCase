import { beforeEach, describe, expect, it, vi } from 'vitest'
import { shallowMount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import EditVerb from '@/views/manager/EditVerb.vue'
import { replaceSpecialChars } from '@/components/specialChars'

vi.mock('@/components/specialChars', () => ({
  replaceSpecialChars: vi.fn((val: string) =>
      val.replace(/å/g, 'aa').replace(/ø/g, 'oe').replace(/æ/g, 'ae')
  )
}))

describe('EditVerb.vue', () => {
  let wrapper: any
  let verbsStoreMock: any

  beforeEach(() => {
    wrapper = shallowMount(EditVerb, {
      global: {
        plugins: [createTestingPinia({
          createSpy: vi.fn,
          stubActions: false
        })]
      }
    })

    verbsStoreMock = wrapper.vm.verbsStore
    verbsStoreMock.delete = vi.fn()
    verbsStoreMock.clearError = vi.fn()
    verbsStoreMock.update = vi.fn(() => Promise.resolve())
    verbsStoreMock.error = ''
  })

  it('should sanitize individual verb field', () => {
    const verb = { norsk: 'båte' }
    wrapper.vm.sanitizeVerbField(verb, 'norsk')

    expect(replaceSpecialChars).toHaveBeenCalledWith('båte')
    expect(verb.norsk).toBe('baate')
  })

  it('should cache original verb on focus', () => {
    const verb = {
      id: 1,
      german: 'gehen',
      norsk: 'gå',
      norskPresent: 'går',
      norskPast: 'gikk',
      norskPastPerfect: 'har gått'
    }

    wrapper.vm.handleFocus(verb)

    expect(verbsStoreMock.clearError).toHaveBeenCalled()
    expect(wrapper.vm.oldVerb[1]).toEqual({
      german: 'gehen',
      norsk: 'gå',
      norskPresent: 'går',
      norskPast: 'gikk',
      norskPastPerfect: 'har gått'
    })
  })

  it('should not call update if nothing changed', async () => {
    const verb = {
      id: 2,
      german: 'sein',
      norsk: 'være',
      norskPresent: 'er',
      norskPast: 'var',
      norskPastPerfect: 'har vært'
    }

    wrapper.vm.oldVerb[2] = { ...verb }

    await wrapper.vm.doneEdit(verb)

    expect(verbsStoreMock.update).not.toHaveBeenCalled()
  })

  it('should call update if any field changed', async () => {
    const verb = {
      id: 3,
      german: 'haben',
      norsk: 'ha',
      norskPresent: 'har',
      norskPast: 'hadde',
      norskPastPerfect: 'har hatt'
    }

    wrapper.vm.oldVerb[3] = {
      ...verb,
      norskPastPerfect: 'hatt' // changed
    }

    await wrapper.vm.doneEdit(verb)

    expect(verbsStoreMock.update).toHaveBeenCalledWith(verb)
  })

  it('should restore verb fields if update fails', async () => {
    const verb = {
      id: 4,
      german: 'sehen',
      norsk: 'se',
      norskPresent: 'ser',
      norskPast: 'så',
      norskPastPerfect: 'har sett'
    }

    const original = {
      german: 'sehen',
      norsk: 'se',
      norskPresent: 'ser',
      norskPast: 'saa',
      norskPastPerfect: 'har set'
    }

    wrapper.vm.oldVerb[4] = { ...original }

    verbsStoreMock.update = vi.fn(() => Promise.reject(new Error('Update failed')))

    await wrapper.vm.doneEdit(verb)

    expect(verbsStoreMock.error).toBe('Update failed')

    expect(verb.norskPast,'set back to origin').toBe('saa')
    expect(verb.norskPastPerfect,'set back to origin').toBe('har set')
  })

  it('should call delete on verbsStore when delete button is clicked', async () => {
    const verb = {
      id: 5,
      german: 'machen',
      norsk: 'gjøre',
      norskPresent: 'gjør',
      norskPast: 'gjorde',
      norskPastPerfect: 'har gjort',
      isDeleting: false
    }

    wrapper = shallowMount(EditVerb, {
      global: {
        plugins: [createTestingPinia({
          createSpy: vi.fn,
          stubActions: false
        })]
      }
    })

    verbsStoreMock = wrapper.vm.verbsStore
    verbsStoreMock.computedFilteredVerbs = [verb]
    verbsStoreMock.delete = vi.fn()

    await wrapper.vm.$nextTick()

    const deleteButtons = wrapper.findAll('button.button-delete')
    expect(deleteButtons.length).toBeGreaterThan(0)

    await deleteButtons[0].trigger('click')

    expect(verbsStoreMock.delete).toHaveBeenCalledWith(5)
  })
})
