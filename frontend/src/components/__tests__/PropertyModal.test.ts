import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import PropertyModal from '../PropertyModal.vue'

const mockProperty = {
  title: 'Test Property',
  description: 'A test property description',
  type: 'House',
  price: 2500000,
  bedrooms: 3,
  bathrooms: 2,
  parking_spaces: 2,
  size_sqm: 150,
  address: '123 Test Street',
  city: 'Cape Town',
  province: 'Western Cape',
  postal_code: '8001',
  latitude: null,
  longitude: null,
  status: 'available',
  is_featured: false
}

describe('PropertyModal', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const mountComponent = (props = {}) => {
    return mount(PropertyModal, {
      props: {
        isOpen: true,
        loading: false,
        ...props
      },
      global: {
        stubs: {
          Building2: true,
          X: true,
          Save: true,
          Loader2: true
        }
      }
    })
  }

  describe('Modal Visibility', () => {
    it('should render when isOpen is true', () => {
      const wrapper = mountComponent({ isOpen: true })
      
      expect(wrapper.find('.modal-overlay').exists()).toBe(true)
      expect(wrapper.find('.modal-content').exists()).toBe(true)
    })

    it('should not render when isOpen is false', () => {
      const wrapper = mountComponent({ isOpen: false })
      
      expect(wrapper.find('.modal-overlay').exists()).toBe(false)
    })

    it('should emit close when overlay is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.modal-overlay').trigger('click')
      
      expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('should not emit close when modal content is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.modal-content').trigger('click')
      
      expect(wrapper.emitted('close')).toBeFalsy()
    })

    it('should emit close when close button is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.close-btn').trigger('click')
      
      expect(wrapper.emitted('close')).toBeTruthy()
    })
  })

  describe('Modal Header', () => {
    it('should show "Add New Property" header in create mode', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.find('h2').text()).toBe('Add New Property')
      expect(wrapper.find('.header-subtitle').text()).toBe('Create a new property listing')
    })

    it('should show "Edit Property" header in edit mode', () => {
      const wrapper = mountComponent({ editProperty: mockProperty })
      
      expect(wrapper.find('h2').text()).toBe('Edit Property')
      expect(wrapper.find('.header-subtitle').text()).toBe('Update property information')
    })
  })

  describe('Form Fields', () => {
    it('should render all required form fields', () => {
      const wrapper = mountComponent()
      
      // Basic Information
      expect(wrapper.find('#title').exists()).toBe(true)
      expect(wrapper.find('#description').exists()).toBe(true)
      expect(wrapper.find('#type').exists()).toBe(true)
      expect(wrapper.find('#status').exists()).toBe(true)
      
      // Property Details
      expect(wrapper.find('#price').exists()).toBe(true)
      expect(wrapper.find('#size_sqm').exists()).toBe(true)
      expect(wrapper.find('#bedrooms').exists()).toBe(true)
      expect(wrapper.find('#bathrooms').exists()).toBe(true)
      expect(wrapper.find('#parking_spaces').exists()).toBe(true)
      
      // Location
      expect(wrapper.find('#address').exists()).toBe(true)
      expect(wrapper.find('#city').exists()).toBe(true)
      expect(wrapper.find('#province').exists()).toBe(true)
      expect(wrapper.find('#postal_code').exists()).toBe(true)
    })

    it('should have correct property type options', () => {
      const wrapper = mountComponent()
      
      const typeSelect = wrapper.find('#type')
      const options = typeSelect.findAll('option')
      
      const expectedTypes = ['', 'House', 'Apartment', 'Townhouse', 'Villa', 'Duplex', 'Penthouse', 'Studio']
      expect(options).toHaveLength(expectedTypes.length)
      
      options.forEach((option, index) => {
        expect(option.attributes('value')).toBe(expectedTypes[index])
      })
    })

    it('should have correct status options', () => {
      const wrapper = mountComponent()
      
      const statusSelect = wrapper.find('#status')
      const options = statusSelect.findAll('option')
      
      const expectedStatuses = ['available', 'under_offer', 'sold', 'rented']
      expect(options).toHaveLength(expectedStatuses.length)
      
      options.forEach((option, index) => {
        expect(option.attributes('value')).toBe(expectedStatuses[index])
      })
    })

    it('should populate South African cities', () => {
      const wrapper = mountComponent()
      
      const citySelect = wrapper.find('#city')
      const options = citySelect.findAll('option')
      
      // Should have empty option + cities
      expect(options.length).toBeGreaterThan(1)
      expect(options[0].attributes('value')).toBe('')
      expect(options[1].text()).toBe('Cape Town')
      expect(options[2].text()).toBe('Johannesburg')
    })
  })

  describe('Form Functionality', () => {
    it('should start with empty form in create mode', () => {
      const wrapper = mountComponent()
      
      expect(wrapper.vm.form.title).toBe('')
      expect(wrapper.vm.form.description).toBe('')
      expect(wrapper.vm.form.type).toBe('')
      expect(wrapper.vm.form.price).toBe(null)
      expect(wrapper.vm.form.status).toBe('available')
      expect(wrapper.vm.form.is_featured).toBe(false)
    })

    it('should populate form in edit mode', async () => {
      const wrapper = mountComponent({ editProperty: mockProperty })
      
      await wrapper.vm.$nextTick()
      
      expect(wrapper.find('#title').exists()).toBe(true)
      expect(wrapper.find('#description').exists()).toBe(true)
      expect(wrapper.find('#type').exists()).toBe(true)
      expect(wrapper.find('#city').exists()).toBe(true)
    })

    it('should update province when city is selected', async () => {
      const wrapper = mountComponent()
      
      const citySelect = wrapper.find('#city')
      await citySelect.setValue('Cape Town')
      
      expect(wrapper.vm.form.province).toBe('Western Cape')
    })

    it('should update form data when input values change', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('#title').setValue('New Property Title')
      await wrapper.find('#description').setValue('New description')
      await wrapper.find('#price').setValue(3000000)
      
      expect(wrapper.vm.form.title).toBe('New Property Title')
      expect(wrapper.vm.form.description).toBe('New description')
      expect(wrapper.vm.form.price).toBe(3000000)
    })

    it('should reset form when modal is closed', async () => {
      const wrapper = mountComponent()
      
      // Set some form data
      await wrapper.find('#title').setValue('Test Title')
      expect(wrapper.vm.form.title).toBe('Test Title')
      
      // Close modal
      await wrapper.find('.close-btn').trigger('click')
      
      expect(wrapper.vm.form.title).toBe('')
    })
  })

  describe('Form Submission', () => {
    it('should emit submit with form data when form is submitted', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('#title').setValue('Test Property')
      await wrapper.find('#description').setValue('Test Description')
      await wrapper.find('#type').setValue('House')
      await wrapper.find('#price').setValue(2000000)
      
      await wrapper.find('form').trigger('submit.prevent')
      
      expect(wrapper.emitted('submit')).toBeTruthy()
      expect(wrapper.emitted('submit')?.[0]?.[0]).toMatchObject({
        title: 'Test Property',
        description: 'Test Description',
        type: 'House',
        price: 2000000
      })
    })

    it('should show loading state when loading is true', () => {
      const wrapper = mountComponent({ loading: true })
      
      const submitButton = wrapper.find('.btn-submit')
      expect(submitButton.attributes('disabled')).toBeDefined()
      expect(submitButton.text()).toContain('Creating...')
    })

    it('should show different loading text in edit mode', () => {
      const wrapper = mountComponent({ 
        loading: true, 
        editProperty: mockProperty 
      })
      
      const submitButton = wrapper.find('.btn-submit')
      expect(submitButton.text()).toContain('Updating...')
    })

    it('should emit close when cancel button is clicked', async () => {
      const wrapper = mountComponent()
      
      await wrapper.find('.btn-cancel').trigger('click')
      
      expect(wrapper.emitted('close')).toBeTruthy()
    })
  })

  describe('Edit Mode Behavior', () => {
    it('should detect edit mode correctly', () => {
      const createWrapper = mountComponent()
      expect(createWrapper.vm.isEditMode).toBe(false)
      
      const editWrapper = mountComponent({ editProperty: mockProperty })
      expect(editWrapper.vm.isEditMode).toBe(true)
    })

    it('should show correct button text in edit mode', () => {
      const editWrapper = mountComponent({ editProperty: mockProperty })
      
      const submitButton = editWrapper.find('.btn-submit')
      expect(submitButton.text()).toContain('Update Property')
    })

    it('should repopulate form when editProperty changes', async () => {
      const wrapper = mountComponent()
      
      // Initially empty
      expect(wrapper.vm.form.title).toBe('')
      
      // Update with edit property
      await wrapper.setProps({ editProperty: mockProperty })
      
      expect(wrapper.vm.form.title).toBe(mockProperty.title)
      expect(wrapper.vm.form.price).toBe(mockProperty.price)
    })
  })

  describe('Watchers', () => {
    it('should reset form when modal opens in create mode', async () => {
      const wrapper = mountComponent({ isOpen: false })
      
      // Set some form data
      wrapper.vm.form.title = 'Test'
      
      // Open modal
      await wrapper.setProps({ isOpen: true })
      
      expect(wrapper.vm.form.title).toBe('')
    })

    it('should populate form when modal opens in edit mode', async () => {
      const wrapper = mountComponent({ 
        isOpen: false,
        editProperty: mockProperty
      })
      
      // Open modal
      await wrapper.setProps({ isOpen: true })
      
      expect(wrapper.vm.form.title).toBe(mockProperty.title)
    })

    it('should reset form when modal closes', async () => {
      const wrapper = mountComponent({ isOpen: true })
      
      // Set some form data
      wrapper.vm.form.title = 'Test'
      
      // Close modal
      await wrapper.setProps({ isOpen: false })
      
      expect(wrapper.vm.form.title).toBe('')
    })
  })
})