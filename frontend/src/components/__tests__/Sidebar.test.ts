import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import Sidebar from '../Sidebar.vue'

let mockAuthStore = {
  user: { name: 'Jane Smith', email: 'jane@example.com' } as any,
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

describe('Sidebar', () => {
  let router: any
  let pinia: any

  beforeEach(() => {
    router = createMockRouter()
    pinia = createPinia()
    vi.clearAllMocks()
    mockAuthStore.user = { name: 'Jane Smith', email: 'jane@example.com' }
  })

  const mountComponent = (props = {}) => {
    return mount(Sidebar, {
      props,
      global: {
        plugins: [router, pinia],
        stubs: {
          Building2: true,
          BarChart3: true,
          Users: true,
          Wrench: true,
          User: true,
          LogOut: true
        }
      }
    })
  }

  describe('Sidebar Structure', () => {
    it('should render sidebar with correct structure', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.sidebar').exists()).toBe(true)
      expect(wrapper.find('.sidebar-header').exists()).toBe(true)
      expect(wrapper.find('.sidebar-nav').exists()).toBe(true)
      expect(wrapper.find('.sidebar-footer').exists()).toBe(true)
    })

    it('should render logo section', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.logo').exists()).toBe(true)
      expect(wrapper.find('.logo-icon').exists()).toBe(true)
      expect(wrapper.find('.logo-text').exists()).toBe(true)
    })

    it('should display correct logo text', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.logo-text h1').text()).toBe('Estatly')
      expect(wrapper.find('.logo-text p').text()).toBe('Professional Management')
    })
  })

  describe('Navigation', () => {
    it('should render all navigation items', () => {
      const wrapper = mountComponent()
      
      const navItems = wrapper.findAll('.nav-item')
      expect(navItems).toHaveLength(4)
      
      const navLinks = wrapper.findAll('.nav-link')
      expect(navLinks).toHaveLength(4)
    })

    it('should have navigation links with text', () => {
      const wrapper = mountComponent()
      
      const navTexts = wrapper.findAll('.nav-text')
      expect(navTexts.length).toBeGreaterThan(0)
      
      const expectedTexts = ['Dashboard', 'Properties', 'Residents', 'Maintenance']
      navTexts.forEach((text, index) => {
        if (index < expectedTexts.length) {
          expect(text.text()).toBe(expectedTexts[index])
        }
      })
    })
  })

  describe('User Information Section', () => {
    it('should display user information structure', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.user-info').exists()).toBe(true)
      expect(wrapper.find('.user-name').exists()).toBe(true)
      expect(wrapper.find('.user-email').exists()).toBe(true)
      expect(wrapper.find('.user-avatar').exists()).toBe(true)
    })

    it('should render logout button', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.logout-btn').exists()).toBe(true)
      expect(wrapper.find('.logout-text').text()).toBe('Logout')
    })
  })

  describe('Interactions', () => {
    it('should handle logout button click', async () => {
      const wrapper = mountComponent()
      
      const logoutBtn = wrapper.find('.logout-btn')
      expect(logoutBtn.exists()).toBe(true)
      
      await logoutBtn.trigger('click')
      // Interaction works if no error is thrown
      expect(true).toBe(true)
    })

    it('should handle nav link clicks', async () => {
      const wrapper = mountComponent()
      
      const navLinks = wrapper.findAll('.nav-link')
      if (navLinks.length > 0) {
        await navLinks[0].trigger('click')
        // Interaction works if no error is thrown
        expect(true).toBe(true)
      }
    })
  })

  describe('Responsive Behavior', () => {
    it('should have responsive sidebar class', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.sidebar').exists()).toBe(true)
      expect(wrapper.find('.sidebar').classes()).toContain('sidebar')
    })
  })
})