import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { shallowMount } from '@vue/test-utils'
import Master from '@/views/Master.vue'
import { defineComponent } from 'vue'

describe('Master.vue', () => {
  let wrapper = null

  beforeEach(() => {
    vi.stubEnv('VITE_IMPRINT_MAIL', 'kontakt@example.com')

    wrapper = shallowMount(Master, {
      global: {
        stubs: {
          MainNav: defineComponent({
            name: 'MainNav',
            template: '<div class="stubbed-main-nav" />'
          }),
          Alert: defineComponent({
            name: 'Alert',
            template: '<div class="stubbed-alert" />'
          }),
          'router-view': defineComponent({
            name: 'RouterViewStub',
            template: '<div class="stubbed-router-view" />'
          })
        }
      }
    })
  })

  afterEach(() => {
    wrapper.unmount()
    vi.unstubAllEnvs()
  })

  it('renders correct header and footer elements', () => {
    const header = wrapper.find('header')
    expect(header.exists()).toBe(true)
    expect(header.find('h1').text()).toContain('NORSK')
    expect(header.find('h2').text()).toContain('Norwegisch lernen')

    const footer = wrapper.find('footer')
    expect(footer.exists()).toBe(true)
    const footerText = footer.find('p').text()
    expect(footerText).toContain('kontakt@example.com')
    expect(footerText).toContain('flag: publicdomainvectors.org')
  })

  it('renders stubbed MainNav, Alert, and router-view components', () => {
    expect(wrapper.find('.stubbed-main-nav').exists()).toBe(true)
    expect(wrapper.find('.stubbed-alert').exists()).toBe(true)
    expect(wrapper.find('.stubbed-router-view').exists()).toBe(true)
  })

  it('renders the norway flag image with correct alt text and source', () => {
    const image = wrapper.find('img.norway-flag')
    expect(image.exists()).toBe(true)
    expect(image.attributes('alt')).toBe('Norwegen Fahne')
    expect(image.attributes('src')).toBe('/src/assets/images/norway.svg')
  })
})
