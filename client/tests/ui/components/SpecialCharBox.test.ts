import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import SpecialCharBox from '@/ui/components/SpecialCharBox.vue'

describe('SpecialCharBox.vue', () => {
    it('displays the heading text', () => {
        const wrapper = mount(SpecialCharBox)
        expect(wrapper.text()).toContain('norwegische Sonderzeichen auf den Tasten:')
    })

    it('displays all six special char mappings', () => {
        const wrapper = mount(SpecialCharBox)
        const chars = wrapper.findAll('.specialChar')
        expect(chars).toHaveLength(6)
        expect(chars[0].text()).toBe('ö => ø')
        expect(chars[1].text()).toBe('ä => æ')
        expect(chars[2].text()).toBe('ü => å')
        expect(chars[3].text()).toBe('Ö => Ø')
        expect(chars[4].text()).toBe('Ä => Æ')
        expect(chars[5].text()).toBe('Ü => Å')
    })

    it('uses the correct container id and grid layout', () => {
        const wrapper = mount(SpecialCharBox)
        expect(wrapper.find('#specialChars').exists()).toBe(true)
        expect(wrapper.find('.grid').exists()).toBe(true)
    })
})
