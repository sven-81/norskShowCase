import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useResultStore } from '@/stores'

describe('ResultStore Tests', () => {
  let store

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useResultStore()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('sets success result with a random smiley', () => {
    const smileys = [
      '😊',
      '😄',
      '👍',
      '🙌',
      '🎉',
      '😁',
      '😀',
      '😃',
      '😉',
      '😅',
      '😆',
      '😍',
      '😎',
      '😲',
      '😮',
      '😲',
      '🤓',
      '🤠',
      '🤩',
      '🥳',
      '👏',
      '👌',
      '🤝'
    ]

    const randomSmiley = smileys[0]

    vi.stubGlobal('random', () => randomSmiley)

    store.success()

    expect(store.result.message.startsWith('Alles richtig!')).toBe(true)
    expect(store.result.type).toBe('correct')
  })

  it('sets error result with a random sad smiley and validation message', () => {
    const smileys = [
      '😢',
      '😏',
      '😐',
      '😑',
      '😒',
      '😓',
      '😔',
      '😕',
      '😖',
      '😞',
      '😟',
      '😣',
      '😥',
      '😦',
      '😧',
      '😨',
      '😩',
      '😪',
      '😫',
      '😬',
      '😭',
      '😯',
      '😱',
      '😵',
      '😶',
      '😳',
      '🙄',
      '🙁',
      '🤔',
      '🤕',
      '🤣',
      '🤢',
      '🤪',
      '🤫',
      '🤭',
      '🥱',
      '🤯',
      '🥺',
      '🧐',
      '🙈',
      '🙈🙉🙊'
    ]

    const randomSadSmiley = smileys[0]
    const validationMessage = 'The input was wrong.'

    vi.stubGlobal('random', () => randomSadSmiley)

    store.error(validationMessage)

    expect(store.result.message.startsWith('<div>Oh no')).toBe(true)
    expect(store.result.type).toBe('mistake')
  })

  it('clears result', () => {
    store.result = { message: 'Some message', type: 'mistake' }

    store.clear()

    expect(store.result.message).toBe('')
    expect(store.result.type).toBe('')
  })
})
