import { describe, expect, it } from 'vitest'
import { buildRequestHeaders } from '@/infrastructure/http/RequestHeaders'

describe('RequestHeaders', () => {
    it('builds headers with correct Authorization', () => {
        const headers = buildRequestHeaders('Bearer test-token')

        expect(headers['Authorization']).toBe('Bearer test-token')
    })

    it('builds headers with HTTP/2 version', () => {
        const headers = buildRequestHeaders('Bearer token')

        expect(headers['Version']).toBe('HTTP/2')
    })

    it('builds headers with application/json content type', () => {
        const headers = buildRequestHeaders('Bearer token')

        expect(headers['Content-Type']).toBe('application/json')
    })

    it('returns all three required headers', () => {
        const headers = buildRequestHeaders('Bearer abc')

        expect(Object.keys(headers)).toHaveLength(3)
        expect(headers).toHaveProperty('Version')
        expect(headers).toHaveProperty('Content-Type')
        expect(headers).toHaveProperty('Authorization')
    })
})

