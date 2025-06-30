import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import Toast from '../Toast.vue'

describe('Toast', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  const mountComponent = (props = {}) => {
    return mount(Toast, {
      props: {
        title: 'Test Title',
        ...props
      }
    })
  }

  describe('Rendering', () => {
    it('should render toast with title', () => {
      const wrapper = mountComponent({ title: 'Success Message' })
      
      expect(wrapper.find('.toast').exists()).toBe(true)
      expect(wrapper.find('.toast-title').text()).toBe('Success Message')
    })

    it('should render toast with message when provided', () => {
      const wrapper = mountComponent({ 
        title: 'Success',
        message: 'Operation completed successfully'
      })
      
      expect(wrapper.find('.toast-message').exists()).toBe(true)
      expect(wrapper.find('.toast-message').text()).toBe('Operation completed successfully')
    })

    it('should not render message element when message is not provided', () => {
      const wrapper = mountComponent({ title: 'Success' })
      
      expect(wrapper.find('.toast-message').exists()).toBe(false)
    })

    it('should be visible by default', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.toast').exists()).toBe(true)
    })
  })

  describe('Toast Types', () => {
    it('should apply success type class and icon', () => {
      const wrapper = mountComponent({ type: 'success' })
      
      expect(wrapper.find('.toast-success').exists()).toBe(true)
      expect(wrapper.find('.toast-icon').text()).toBe('✓')
    })

    it('should apply error type class and icon', () => {
      const wrapper = mountComponent({ type: 'error' })
      
      expect(wrapper.find('.toast-error').exists()).toBe(true)
      expect(wrapper.find('.toast-icon').text()).toBe('✗')
    })

    it('should apply warning type class and icon', () => {
      const wrapper = mountComponent({ type: 'warning' })
      
      expect(wrapper.find('.toast-warning').exists()).toBe(true)
      expect(wrapper.find('.toast-icon').text()).toBe('⚠')
    })

    it('should apply info type class and icon by default', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.toast-info').exists()).toBe(true)
      expect(wrapper.find('.toast-icon').text()).toBe('ℹ')
    })
  })

  describe('Close Functionality', () => {
    it('should render close button', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.toast-close').exists()).toBe(true)
      expect(wrapper.find('.toast-close').text()).toBe('×')
    })

    it('should emit close event when close button is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.toast-close').trigger('click')
      
      // Advance time by transition delay
      vi.advanceTimersByTime(300)
      await wrapper.vm.$nextTick()
      
      expect(wrapper.emitted('close')).toBeTruthy()
    })
  })

  describe('Auto Close', () => {
    it('should auto close after default duration when autoClose is true', async () => {
      const wrapper = mountComponent({ autoClose: true })
      
      expect(wrapper.find('.toast').exists()).toBe(true)
      
      // Advance time by default duration (5000ms)
      vi.advanceTimersByTime(5000)
      await wrapper.vm.$nextTick()
      
      // Advance time by transition duration (300ms)
      vi.advanceTimersByTime(300)
      await wrapper.vm.$nextTick()
      
      expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('should auto close after custom duration', async () => {
      const wrapper = mountComponent({ 
        autoClose: true,
        duration: 2000
      })
      
      expect(wrapper.find('.toast').exists()).toBe(true)
      
      // Should close after custom duration
      vi.advanceTimersByTime(2000)
      await wrapper.vm.$nextTick()
      
      vi.advanceTimersByTime(300)
      await wrapper.vm.$nextTick()
      
      expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('should not auto close when autoClose is false', async () => {
      const wrapper = mountComponent({ autoClose: false })
      
      expect(wrapper.find('.toast').exists()).toBe(true)
      
      // Advance time past default duration
      vi.advanceTimersByTime(10000)
      await wrapper.vm.$nextTick()
      
      expect(wrapper.find('.toast').exists()).toBe(true)
      expect(wrapper.emitted('close')).toBeFalsy()
    })
  })

  describe('Props Defaults', () => {
    it('should have correct default prop values', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.props('type')).toBe('info')
      expect(wrapper.props('duration')).toBe(5000)
      expect(wrapper.props('autoClose')).toBe(true)
    })
  })

  describe('Integration', () => {
    it('should work with all props together', () => {
      const wrapper = mountComponent({
        type: 'error',
        title: 'Error Occurred',
        message: 'Something went wrong',
        duration: 1000,
        autoClose: true
      })
      
      expect(wrapper.find('.toast-error').exists()).toBe(true)
      expect(wrapper.find('.toast-title').text()).toBe('Error Occurred')
      expect(wrapper.find('.toast-message').text()).toBe('Something went wrong')
      expect(wrapper.find('.toast-icon').text()).toBe('✗')
    })
  })
})