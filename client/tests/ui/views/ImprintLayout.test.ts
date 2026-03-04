import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ImprintLayout from '@/ui/layouts/ImprintLayout.vue'
describe('ImprintLayout.vue', () => {
    it('displays Impressum headline', () => {
        const wrapper = mount(ImprintLayout)
        expect(wrapper.find('h1').text()).toBe('Impressum')
    })

    it('displays imprint content text', () => {
        const wrapper = mount(ImprintLayout)
        expect(wrapper.text()).toContain('Dies ist eine private Website zum Lernen von norwegischen Vokabeln.')
    })

    it('displays contact email from environment', () => {
        const wrapper = mount(ImprintLayout)
        expect(wrapper.find('p.imprint').exists()).toBe(true)
    })
})
