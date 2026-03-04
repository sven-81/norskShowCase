import { describe, expect, it } from 'vitest'
import { User } from '@/domain/model/User'

describe('User', () => {
    it('creates a user with all properties', () => {
        const user = new User(true, 'sven', 'Sven', 'Duge', 'token123', 'Bearer', 7200, 'is:manager')

        expect(user.login).toBe(true)
        expect(user.username).toBe('sven')
        expect(user.firstName).toBe('Sven')
        expect(user.lastName).toBe('Duge')
        expect(user.token).toBe('token123')
        expect(user.tokenType).toBe('Bearer')
        expect(user.expiresIn).toBe(7200)
        expect(user.scope).toBe('is:manager')
    })

    it('isManager returns true if scope is "is:manager"', () => {
        const user = new User(true, 'sven', 'Sven', 'Duge', 'token', 'Bearer', 7200, 'is:manager')

        expect(user.isManager()).toBe(true)
    })

    it('isManager returns false if scope is not "is:manager"', () => {
        const user = new User(true, 'sven', 'Sven', 'Duge', 'token', 'Bearer', 7200, 'is:user')

        expect(user.isManager()).toBe(false)
    })

    it('creates from response data with all fields', () => {
        const data = {
            login: true,
            username: 'klaus',
            firstName: 'Klaus',
            lastName: 'Müller',
            token: 'jwt-token',
            tokenType: 'Bearer',
            expiresIn: 3600
        }

        const user = User.fromResponse(data, 'is:user')

        expect(user.login).toBe(true)
        expect(user.username).toBe('klaus')
        expect(user.firstName).toBe('Klaus')
        expect(user.lastName).toBe('Müller')
        expect(user.token).toBe('jwt-token')
        expect(user.tokenType).toBe('Bearer')
        expect(user.expiresIn).toBe(3600)
        expect(user.scope).toBe('is:user')
    })

    it('creates from response data with missing fields using defaults', () => {
        const data = {}

        const user = User.fromResponse(data, 'is:manager')

        expect(user.login).toBe(true)
        expect(user.username).toBe('')
        expect(user.firstName).toBe('')
        expect(user.lastName).toBe('')
        expect(user.token).toBe('')
        expect(user.tokenType).toBe('Bearer')
        expect(user.expiresIn).toBe(7200)
        expect(user.scope).toBe('is:manager')
    })
})

