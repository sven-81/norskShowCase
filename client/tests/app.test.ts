import { beforeEach, describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import App from '@/App.vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'

const DummyView = {
  template: '<div>Dummy View</div>'
}

const routes = [
  {
    path: '/',
    component: DummyView
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

const pinia = createPinia()

let wrapper

beforeEach(async () => {
  import.meta.env.VITE_IMPRINT_MAIL = 'test@example.com'

  router.push('/')
  await router.isReady()
  wrapper = mount(App, {
    global: {
      plugins: [router, pinia]
    }
  })
})
describe('App.vue', () => {
  it('renders master layout without errors', async () => {
    expect(wrapper.html()).toContain('Dummy View')
    expect(wrapper.findComponent({ name: 'Master' }).exists()).toBe(true)
  })

  it('fits structure-snapshot', async () => {
    expect(wrapper.html()).toMatchSnapshot()
  })

  it('can import mail', async () => {
    expect(wrapper.html()).toContain('mailto:test@example.com')
  })
})
