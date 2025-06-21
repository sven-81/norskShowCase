import { afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import CookieConsent from '@/components/CookieConsent.vue'
import Cookies from 'js-cookie'
import sinon from 'sinon'
import { nextTick } from 'vue'

describe('CookieConsent.vue', () => {
  let getStub: sinon.SinonStub
  let setStub: sinon.SinonStub

  beforeEach(() => {
    getStub = sinon.stub(Cookies, 'get')
    setStub = sinon.stub(Cookies, 'set')
  })

  afterEach(() => {
    getStub.restore()
    setStub.restore()
  })

  it('shows pop-up if no cookie is set', () => {
    getStub.returns(undefined)
    const wrapper = mount(CookieConsent)
    expect(wrapper.find('.cookie-popup').exists()).toBe(true)
    // initial state of refs
    expect(wrapper.vm.cookieConsentGiven).toBe(false)
  })

  it('does not show a pop-up if cookie is set', async () => {
    getStub.returns('true')
    const wrapper = mount(CookieConsent)

    await nextTick()

    expect(wrapper.find('.cookie-popup').exists()).toBe(false)
    // initial state of refs
    expect(wrapper.vm.cookieConsentGiven).toBe(true)
  })

  it('accepts cookies and hides pop-up', async () => {
    getStub.returns(undefined)
    const wrapper = mount(CookieConsent)

    await wrapper.find('button.accept').trigger('click')

    sinon.assert.calledOnceWithExactly(setStub, 'cookieConsent', 'true', {
      expires: 30,
      secure: true,
      sameSite: 'Strict'
    })

    expect(wrapper.vm.cookieConsentGiven).toBe(true)
    expect(wrapper.find('.cookie-popup').exists()).toBe(false)
  })

  it('accepts cookies by directly calling the function', () => {
    getStub.returns(undefined)
    const wrapper = mount(CookieConsent)
    wrapper.vm.acceptCookies()
    // initial state of refs
    expect(wrapper.vm.cookieConsentGiven).toBe(true)
    sinon.assert.calledOnce(setStub)
  })

  it('reacts robustly to unexpected cookie values', async () => {
    getStub.returns('random-value')
    const wrapper = mount(CookieConsent)

    await nextTick()

    // should show pop-up because value is not "true"
    expect(wrapper.find('.cookie-popup').exists()).toBe(true)
    // initial state of refs
    expect(wrapper.vm.cookieConsentGiven).toBe(false)
  })
})
