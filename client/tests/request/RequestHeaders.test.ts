import { describe, expect, it } from 'vitest'
import { getHeaders } from '@/request'

describe('RequestHeaders', () => {
  it('returns RequestHeaders properly', () => {
    const expected = {
      Version: 'HTTP/2',
      'Content-Type': 'application/json',
      Authorization: 'Bearer fakeBearer'
    }

    const headers = getHeaders('Bearer fakeBearer')

    expect(headers).toStrictEqual(expected)
  })
})
