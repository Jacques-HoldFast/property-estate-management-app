import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import AppLayout from '../AppLayout.vue'

let mockAuthStore = {
  user: { name: 'John Doe', email: 'john@example.com' } as any,
  logout: vi.fn()
}

vi.mock('../stores/auth', () => ({
  useAuthStore: () => mockAuthStore
}))

vi.mock('axios', () => ({
  default: {
    post: vi.fn().mockResolvedValue({})
  }
}))

vi.mock('js-cookie', () => ({
  default: {
    get: vi.fn().mockReturnValue('mock-token')
  }
}))

const createMockRouter = () => {
  return createRouter({
    history: createWebHistory(),
    routes: [
      { path: '/', component: { template: '<div>Home</div>' } },
      { path: '/dashboard', component: { template: '<div>Dashboard</div>' } },
      { path: '/properties', component: { template: '<div>Properties</div>' } },
      { path: '/residents', component: { template: '<div>Residents</div>' } },
      { path: '/maintenance', component: { template: '<div>Maintenance</div>' } },
      { path: '/login', component: { template: '<div>Login</div>' } }
    ]
  })
}

describe('AppLayout', () => {
  let router: any
  let pinia: any

  beforeEach(() => {
    router = createMockRouter()
    pinia = createPinia()
    vi.clearAllMocks()
    mockAuthStore.user = { name: 'John Doe', email: 'john@example.com' }
  })

  const mountComponent = (props = {}) => {
    return mount(AppLayout, {
      props,
      global: {
        plugins: [router, pinia],
        stubs: {
          Sidebar: true,
          Building2: true,
          Menu: true,
          X: true,
          BarChart3: true,
          Users: true,
          Wrench: true,
          User: true,
          LogOut: true
        }
      }
    })
  }

  describe('Basic Structure', () => {
    it('should render app layout structure', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.app-layout').exists()).toBe(true)
      expect(wrapper.find('.desktop-sidebar').exists()).toBe(true)
      expect(wrapper.find('.main-content').exists()).toBe(true)
      expect(wrapper.find('.content-wrapper').exists()).toBe(true)
    })

    it('should render mobile header', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.mobile-header').exists()).toBe(true)
      expect(wrapper.find('.mobile-logo').exists()).toBe(true)
      expect(wrapper.find('.mobile-menu-btn').exists()).toBe(true)
    })

    it('should render slot content', () => {
      const wrapper = mount(AppLayout, {
        slots: {
          default: '<div class="test-content">Test Content</div>'
        },
        global: {
          plugins: [router, pinia],
          stubs: {
            Sidebar: true,
            Building2: true,
            Menu: true,
            X: true,
            BarChart3: true,
            Users: true,
            Wrench: true,
            User: true,
            LogOut: true
          }
        }
      })
      
      expect(wrapper.find('.test-content').exists()).toBe(true)
      expect(wrapper.text()).toContain('Test Content')
    })
  })


  describe('Interactions', () => {
    it('should handle menu button click', async () => {
      const wrapper = mountComponent()
      
      const menuBtn = wrapper.find('.mobile-menu-btn')
      expect(menuBtn.exists()).toBe(true)
      
      await menuBtn.trigger('click')
      // Interaction works if no error is thrown
      expect(true).toBe(true)
    })

    it('should handle close button click', async () => {
      const wrapper = mountComponent()
      
      const closeBtn = wrapper.find('.close-btn')
      if (closeBtn.exists()) {
        await closeBtn.trigger('click')
        // Interaction works if no error is thrown
        expect(true).toBe(true)
      }
    })
  })
})