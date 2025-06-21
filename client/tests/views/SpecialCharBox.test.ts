import {describe, expect, it} from 'vitest'
import {shallowMount} from '@vue/test-utils'
import SpecialCharBox from "@/views/SpecialCharBox.vue";

describe('SpecialCharBox.vue', () => {
    it('renders SpecialCharBox', () => {
        const wrapper = shallowMount(SpecialCharBox, {})

        const expected: string = "norwegische Sonderzeichen auf den Tasten: " +
            "ö => ø" +
            "ä => æ" +
            "ü => å" +
            "Ö => Ø" +
            "Ä => Æ" +
            "Ü => Å"
        expect(wrapper.text()).toMatch(expected)
    })
})