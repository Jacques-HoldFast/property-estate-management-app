import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ConfirmDialog from '../ConfirmDialog.vue'

describe('ConfirmDialog', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const mountComponent = (props = {}) => {
    return mount(ConfirmDialog, {
      props: {
        isOpen: true,
        ...props
      }
    })
  }

  describe('Visibility', () => {
    it('should render when isOpen is true', () => {
      const wrapper = mountComponent({ isOpen: true })
      
      expect(wrapper.find('.confirm-overlay').exists()).toBe(true)
      expect(wrapper.find('.confirm-dialog').exists()).toBe(true)
    })

    it('should not render when isOpen is false', () => {
      const wrapper = mountComponent({ isOpen: false })
      
      expect(wrapper.find('.confirm-overlay').exists()).toBe(false)
    })
  })

  describe('Content Rendering', () => {
    it('should render default title and message', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.confirm-title').text()).toBe('Confirm Action')
      expect(wrapper.find('.confirm-message').text()).toBe('Are you sure you want to proceed?')
    })

    it('should render custom title and message', () => {
      const wrapper = mountComponent({
        title: 'Delete Item',
        message: 'This action cannot be undone.'
      })
      
      expect(wrapper.find('.confirm-title').text()).toBe('Delete Item')
      expect(wrapper.find('.confirm-message').text()).toBe('This action cannot be undone.')
    })

    it('should render details when provided', () => {
      const wrapper = mountComponent({
        details: 'Property: "Beautiful House"'
      })
      
      expect(wrapper.find('.confirm-details').exists()).toBe(true)
      expect(wrapper.find('.confirm-details').text()).toBe('Property: "Beautiful House"')
    })

    it('should not render details when not provided', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.confirm-details').exists()).toBe(false)
    })

    it('should render warning icon', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.warning-icon').exists()).toBe(true)
      expect(wrapper.find('.warning-icon').text()).toBe('⚠️')
    })
  })

  describe('Buttons', () => {
    it('should render cancel and confirm buttons', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.btn-cancel').exists()).toBe(true)
      expect(wrapper.find('.btn-confirm').exists()).toBe(true)
    })

    it('should show default button text', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('.btn-cancel').text()).toBe('Cancel')
      expect(wrapper.find('.btn-confirm').text()).toBe('Confirm')
    })

    it('should show custom confirm button text', () => {
      const wrapper = mountComponent({
        confirmText: 'Delete Forever'
      })
      
      expect(wrapper.find('.btn-confirm').text()).toBe('Delete Forever')
    })

    it('should show loading text when loading', () => {
      const wrapper = mountComponent({
        loading: true
      })
      
      expect(wrapper.find('.btn-confirm').text()).toBe('Deleting...')
    })

    it('should show custom loading text', () => {
      const wrapper = mountComponent({
        loading: true,
        confirmText: 'Remove'
      })
      
      expect(wrapper.find('.btn-confirm').text()).toBe('Deleting...')
    })
  })

  describe('Button States', () => {
    it('should disable buttons when loading', () => {
      const wrapper = mountComponent({ loading: true })
      
      expect(wrapper.find('.btn-cancel').attributes('disabled')).toBeDefined()
      expect(wrapper.find('.btn-confirm').attributes('disabled')).toBeDefined()
    })

    it('should enable buttons when not loading', () => {
      const wrapper = mountComponent({ loading: false })
      
      expect(wrapper.find('.btn-cancel').attributes('disabled')).toBeUndefined()
      expect(wrapper.find('.btn-confirm').attributes('disabled')).toBeUndefined()
    })

    it('should apply loading class to confirm button when loading', () => {
      const wrapper = mountComponent({ loading: true })
      
      expect(wrapper.find('.btn-confirm').classes()).toContain('btn-loading')
    })

    it('should not apply loading class when not loading', () => {
      const wrapper = mountComponent({ loading: false })
      
      expect(wrapper.find('.btn-confirm').classes()).not.toContain('btn-loading')
    })
  })

  describe('Event Handling', () => {
    it('should emit confirm when confirm button is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.btn-confirm').trigger('click')
      
      expect(wrapper.emitted('confirm')).toBeTruthy()
      expect(wrapper.emitted('confirm')).toHaveLength(1)
    })

    it('should emit cancel when cancel button is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.btn-cancel').trigger('click')
      
      expect(wrapper.emitted('cancel')).toBeTruthy()
      expect(wrapper.emitted('cancel')).toHaveLength(1)
    })

    it('should emit cancel when overlay is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.confirm-overlay').trigger('click')
      
      expect(wrapper.emitted('cancel')).toBeTruthy()
    })

    it('should not emit cancel when dialog content is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.confirm-dialog').trigger('click')
      
      expect(wrapper.emitted('cancel')).toBeFalsy()
    })

    it('should not emit cancel when loading and cancel button is clicked', async () => {
      const wrapper = mountComponent({ loading: true })
      
      await wrapper.find('.btn-cancel').trigger('click')
      
      expect(wrapper.emitted('cancel')).toBeFalsy()
    })

    it('should not emit cancel when loading and overlay is clicked', async () => {
      const wrapper = mountComponent({ loading: true })
      
      await wrapper.find('.confirm-overlay').trigger('click')
      
      expect(wrapper.emitted('cancel')).toBeFalsy()
    })
  })

  describe('Props Defaults', () => {
    it('should have correct default values', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.props('title')).toBe('Confirm Action')
      expect(wrapper.props('message')).toBe('Are you sure you want to proceed?')
      expect(wrapper.props('confirmText')).toBe('Confirm')
      expect(wrapper.props('loading')).toBe(false)
      expect(wrapper.props('details')).toBeUndefined()
    })
  })


  describe('Integration Scenarios', () => {
    it('should work with all props together', () => {
      const wrapper = mountComponent({
        isOpen: true,
        title: 'Delete Property',
        message: 'Are you sure you want to delete this property?',
        details: 'Property: "Beautiful 3BR House"',
        confirmText: 'Delete',
        loading: false
      })
      
      expect(wrapper.find('.confirm-title').text()).toBe('Delete Property')
      expect(wrapper.find('.confirm-message').text()).toBe('Are you sure you want to delete this property?')
      expect(wrapper.find('.confirm-details').text()).toBe('Property: "Beautiful 3BR House"')
      expect(wrapper.find('.btn-confirm').text()).toBe('Delete')
      expect(wrapper.find('.btn-confirm').attributes('disabled')).toBeUndefined()
    })

    it('should handle loading state correctly', () => {
      const wrapper = mountComponent({
        title: 'Delete Item',
        confirmText: 'Delete',
        loading: true
      })
      
      expect(wrapper.find('.btn-confirm').text()).toBe('Deleting...')
      expect(wrapper.find('.btn-confirm').attributes('disabled')).toBeDefined()
      expect(wrapper.find('.btn-cancel').attributes('disabled')).toBeDefined()
      expect(wrapper.find('.btn-confirm').classes()).toContain('btn-loading')
    })
  })
})