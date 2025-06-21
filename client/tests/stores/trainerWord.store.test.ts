import { afterEach, beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useResultStore, useTrainerWordStore } from '@/stores'
import { fetchWrapper } from '@/request'
import sinon from 'sinon'

describe('trainerWordStore initial test', () => {
  let store = null

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useTrainerWordStore()
  })

  it('initializes with correct values', () => {
    expect(store.word).toBeNull()
    expect(store.inputNorsk).toBeNull()
    expect(store.errorMessage).toBe('')
    expect(store.loading).toBe(false)
  })
})

describe('trainerWordStore random tests', function () {
  let store
  let fetchWrapperGetStub

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useTrainerWordStore()
    fetchWrapperGetStub = sinon.stub(fetchWrapper, 'get')
  })

  afterEach(() => {
    fetchWrapperGetStub.restore()
  })

  it('should fetch random set of words to train', async () => {
    const mockWord = { id: '2', german: 'neu', norsk: 'næu' }
    const expected = { id: '2', german: 'neu', norsk: 'næu' }

    fetchWrapperGetStub.resolves(mockWord)

    await store.random() //needs await

    sinon.assert.calledOnceWithExactly(
      fetchWrapperGetStub,
      `${import.meta.env.VITE_BACKEND_URL}/train/words`
    )
    expect(store.word).toEqual(expected)
  })

  it('should handle error when fetching random set of words to train', async () => {
    const error = new Error('Failed to fetch word')

    fetchWrapperGetStub.rejects(error)

    await store.random() //needs await

    expect(store.errorMessage).toBe(
      'Ein unbekannter Fehler ist aufgetreten. \n Det oppstod en ukjent feil.'
    )
    expect(store.word).toBeNull()
  })

  it('sets specific error message for "No records found in database for: words"', async () => {
    const error = new Error('No records found in database for: words')
    fetchWrapperGetStub.rejects(error)

    await store.random() //needs await

    expect(store.errorMessage).toBe('In der Datenbank wurden keine Wörter gefunden.')
    expect(store.word).toBeNull()
  })
})

describe('trainerWordStore evaluate tests', () => {
  let store

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useTrainerWordStore()
  })

  it('should evaluate correctly when norskInput matches this.norsk', async () => {
    const norskInput = 'jeg inviterer'
    const resultStoreMock = sinon.stub(useResultStore(), 'success')
    const fetchWrapperPatchStub = sinon.stub(fetchWrapper, 'patch')

    store.id = 1
    store.word = { id: 1, german: 'ich lade', norsk: 'jeg inviterer' }
    store.norsk = 'jeg inviterer'

    await store.evaluate(norskInput)

    sinon.assert.calledOnce(resultStoreMock)
    sinon.assert.calledWith(
      fetchWrapperPatchStub,
      `${import.meta.env.VITE_BACKEND_URL}/train/words/1`
    )

    resultStoreMock.restore()
    fetchWrapperPatchStub.restore()
  })

  it('should handle error when norskInput does not match this.norsk', async () => {
    const norskInput = 'wrong input'
    const resultStoreMock = sinon.stub(useResultStore(), 'error')

    store.id = 1
    store.word = { id: 1, german: 'ich lade', norsk: 'jeg inviterer' }
    store.norsk = 'jeg inviterer'

    await store.evaluate(norskInput)

    sinon.assert.calledOnce(resultStoreMock)
    expect(
      resultStoreMock.calledWith(
        'Falsch => norsk:: wrong input<br />Richtig => norsk:: jeg inviterer'
      )
    ).toBe(true)

    resultStoreMock.restore()
  })
})
