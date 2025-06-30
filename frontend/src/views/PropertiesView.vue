<template>
  <AppLayout>
    <div class="properties">
      <header class="properties-header">
        <div class="header-content">
          <div class="header-icon">
            <Building2 :size="32" class="building-icon" />
          </div>
          <div class="header-text">
            <h1>Properties</h1>
            <p class="properties-subtitle">Manage your property portfolio</p>
          </div>
        </div>
        <div class="properties-actions">
          <div class="properties-stats">
            <BarChart3 :size="16" />
            <span>Total: {{ properties.length }} properties</span>
          </div>
          <button @click="openModal" class="btn-add-property">
            <Plus :size="18" />
            <span>Add New Property</span>
          </button>
        </div>
      </header>

      <div v-if="loading" class="loading">
        <Loader2 :size="32" class="loading-spinner" />
        <p>Loading properties...</p>
      </div>

      <div v-else-if="error" class="error">
        <AlertCircle :size="24" class="error-icon" />
        <p>{{ error }}</p>
      </div>

    <div v-else class="properties-table-container">
      <table class="properties-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Price</th>
            <th>Bedrooms</th>
            <th>Bathrooms</th>
            <th>Size (m²)</th>
            <th>Location</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(property, index) in paginatedProperties"
            :key="property.id"
            :class="{ 'row-even': index % 2 === 0, 'row-odd': index % 2 === 1 }"
          >
            <td class="property-title">
              <div class="title-container">
                <span class="title">{{ property.title }}</span>
                <span v-if="property.is_featured" class="featured-badge">Featured</span>
              </div>
            </td>
            <td>{{ property.type }}</td>
            <td class="price">R {{ formatPrice(property.price) }}</td>
            <td>{{ property.bedrooms }}</td>
            <td>{{ property.bathrooms }}</td>
            <td>{{ property.size_sqm }}</td>
            <td class="location">
              <div>{{ property.city }}, {{ property.province }}</div>
            </td>
            <td>
              <span :class="['status-badge', `status-${property.status}`]">
                {{ formatStatus(property.status) }}
              </span>
            </td>
            <td class="actions-cell">
              <button 
                @click="openEditModal(property)" 
                class="edit-btn"
                :title="`Edit ${property.title}`"
              >
                <Edit2 :size="16" />
              </button>
              <button 
                @click="confirmDelete(property)" 
                class="delete-btn"
                :title="`Delete ${property.title}`"
              >
                <Trash2 :size="16" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination Controls -->
      <div class="pagination-container" v-if="totalPages > 1">
        <div class="pagination-info">
          <span>Showing {{ startItem }} to {{ endItem }} of {{ properties.length }} properties</span>
        </div>
        
        <div class="pagination-controls">
          <button 
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="pagination-btn pagination-prev"
          >
            <ChevronLeft :size="16" />
            <span>Previous</span>
          </button>
          
          <div class="pagination-numbers">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="['pagination-number', { 'active': page === currentPage }]"
            >
              {{ page }}
            </button>
          </div>
          
          <button 
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="pagination-btn pagination-next"
          >
            <span>Next</span>
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>
    </div>

    <!-- Property Modal -->
    <PropertyModal
      :is-open="isModalOpen"
      :loading="isSubmitting"
      :edit-property="propertyToEdit"
      @close="closeModal"
      @submit="handlePropertySubmit"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :is-open="showDeleteDialog"
      :loading="isDeleting"
      title="Delete Property"
      :message="`Are you sure you want to delete this property?`"
      :details="propertyToDelete?.title"
      confirm-text="Delete"
      @confirm="handleDeleteProperty"
      @cancel="cancelDelete"
    />

    <!-- Toast Container -->
    <ToastContainer ref="toastContainer" />
    </div>
  </AppLayout>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import { createApiUrl } from '../config/api'
import PropertyModal from '../components/PropertyModal.vue'
import ToastContainer from '../components/ToastContainer.vue'
import ConfirmDialog from '../components/ConfirmDialog.vue'
import AppLayout from '../components/AppLayout.vue'
import { 
  Building2, 
  BarChart3, 
  Plus, 
  Edit2, 
  Trash2, 
  Loader2, 
  AlertCircle,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

interface Property {
  id: number
  title: string
  description: string
  type: string
  price: number
  bedrooms: number
  bathrooms: number
  parking_spaces: number
  size_sqm: number
  address: string
  city: string
  province: string
  postal_code: string
  latitude: number | null
  longitude: number | null
  status: string
  is_featured: boolean
  created_at: string
  updated_at: string
}

const properties = ref<Property[]>([])
const loading = ref(true)
const error = ref('')
const isModalOpen = ref(false)
const isSubmitting = ref(false)
const showDeleteDialog = ref(false)
const isDeleting = ref(false)
const propertyToDelete = ref<Property | null>(null)
const propertyToEdit = ref<Property | null>(null)
const toastContainer = ref<InstanceType<typeof ToastContainer> | null>(null)

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalPages = computed(() => Math.ceil(properties.value.length / itemsPerPage.value))
const startItem = computed(() => (currentPage.value - 1) * itemsPerPage.value + 1)
const endItem = computed(() => Math.min(currentPage.value * itemsPerPage.value, properties.value.length))

const paginatedProperties = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return properties.value.slice(start, end)
})

const visiblePages = computed(() => {
  const delta = 2
  const range = []
  const rangeWithDots = []
  
  for (let i = Math.max(2, currentPage.value - delta); 
       i <= Math.min(totalPages.value - 1, currentPage.value + delta); 
       i++) {
    range.push(i)
  }
  
  if (currentPage.value - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }
  
  rangeWithDots.push(...range)
  
  if (currentPage.value + delta < totalPages.value - 1) {
    rangeWithDots.push('...', totalPages.value)
  } else {
    rangeWithDots.push(totalPages.value)
  }
  
  return rangeWithDots.filter((item, index, array) => array.indexOf(item) === index)
})

const goToPage = (page: string | number) => {
  const pageNum = typeof page === 'string' ? parseInt(page) : page
  if (pageNum >= 1 && pageNum <= totalPages.value) {
    currentPage.value = pageNum
  }
}

const fetchProperties = async () => {
  try {
    loading.value = true
    const response = await axios.get(createApiUrl('/api/properties'), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })
    
    if (response.data.success) {
      properties.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to load properties'
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load properties'
    console.error('Error fetching properties:', err)
  } finally {
    loading.value = false
  }
}

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('en-ZA').format(price)
}

const formatStatus = (status: string): string => {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const openModal = () => {
  propertyToEdit.value = null
  isModalOpen.value = true
}

const openEditModal = (property: Property) => {
  propertyToEdit.value = property
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  propertyToEdit.value = null
}

const handlePropertySubmit = async (formData: any) => {
  if (propertyToEdit.value) {
    await handleUpdateProperty(formData)
  } else {
    await handleCreateProperty(formData)
  }
}

const handleCreateProperty = async (formData: any) => {
  try {
    isSubmitting.value = true
    
    const response = await axios.post(createApiUrl('/api/properties'), formData, {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    // Add new property to the beginning of the list
    properties.value.unshift(response.data.property)
    
    // Show success toast
    toastContainer.value?.addToast({
      type: 'success',
      title: 'Property Created!',
      message: 'Your property has been successfully added to the listings.',
      duration: 4000
    })
    
    // Close modal
    closeModal()
    
  } catch (err: any) {
    console.error('Error creating property:', err)
    
    // Show error toast
    let errorMessage = 'Failed to create property. Please try again.'
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      errorMessage = errors.join(' ')
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message
    }
    
    toastContainer.value?.addToast({
      type: 'error',
      title: 'Creation Failed',
      message: errorMessage,
      duration: 6000
    })
  } finally {
    isSubmitting.value = false
  }
}

const handleUpdateProperty = async (formData: any) => {
  if (!propertyToEdit.value) return
  
  try {
    isSubmitting.value = true
    
    const response = await axios.put(createApiUrl(`/api/properties/${propertyToEdit.value.id}`), formData, {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    // Update property in the list
    const index = properties.value.findIndex(p => p.id === propertyToEdit.value!.id)
    if (index > -1) {
      properties.value[index] = response.data.property
    }
    
    // Show success toast
    toastContainer.value?.addToast({
      type: 'success',
      title: 'Property Updated!',
      message: 'Your property has been successfully updated.',
      duration: 4000
    })
    
    // Close modal
    closeModal()
    
  } catch (err: any) {
    console.error('Error updating property:', err)
    
    // Show error toast
    let errorMessage = 'Failed to update property. Please try again.'
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      errorMessage = errors.join(' ')
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message
    }
    
    toastContainer.value?.addToast({
      type: 'error',
      title: 'Update Failed',
      message: errorMessage,
      duration: 6000
    })
  } finally {
    isSubmitting.value = false
  }
}

const confirmDelete = (property: Property) => {
  propertyToDelete.value = property
  showDeleteDialog.value = true
}

const cancelDelete = () => {
  showDeleteDialog.value = false
  propertyToDelete.value = null
}

const handleDeleteProperty = async () => {
  if (!propertyToDelete.value) return
  
  try {
    isDeleting.value = true
    
    await axios.delete(createApiUrl(`/api/properties/${propertyToDelete.value.id}`), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    // Remove property from the list
    const index = properties.value.findIndex(p => p.id === propertyToDelete.value!.id)
    if (index > -1) {
      properties.value.splice(index, 1)
    }
    
    // Show success toast
    toastContainer.value?.addToast({
      type: 'success',
      title: 'Property Deleted!',
      message: `"${propertyToDelete.value.title}" has been removed from your listings.`,
      duration: 4000
    })
    
    // Close dialog
    cancelDelete()
    
  } catch (err: any) {
    console.error('Error deleting property:', err)
    
    // Show error toast
    let errorMessage = 'Failed to delete property. Please try again.'
    if (err.response?.status === 404) {
      errorMessage = 'Property not found. It may have already been deleted.'
    } else if (err.response?.data?.message) {
      errorMessage = err.response.data.message
    }
    
    toastContainer.value?.addToast({
      type: 'error',
      title: 'Delete Failed',
      message: errorMessage,
      duration: 6000
    })
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  fetchProperties()
})
</script>

<style scoped>
.properties {
  padding: 2rem;
  min-height: 100vh;
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  0% { 
    opacity: 0;
    transform: translateY(20px);
  }
  100% { 
    opacity: 1;
    transform: translateY(0);
  }
}

.properties-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  0% { 
    transform: translateY(30px);
    opacity: 0;
  }
  100% { 
    transform: translateY(0);
    opacity: 1;
  }
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.header-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
}

.header-icon:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
}

.building-icon {
  color: white;
}

.header-text h1 {
  margin: 0 0 0.25rem 0;
  color: #1e293b;
  font-size: 2rem;
  font-weight: 700;
}

.properties-subtitle {
  margin: 0;
  color: #64748b;
  font-size: 1rem;
  font-weight: 500;
}

.properties-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.properties-stats {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.95rem;
  font-weight: 500;
}

.btn-add-property {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.btn-add-property:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
  background: linear-gradient(135deg, #2563eb, #0891b2);
}

.btn-add-property:active {
  transform: translateY(0);
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: rgba(255, 255, 255, 0.8);
  gap: 1rem;
}

.loading-spinner {
  color: white;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 2rem;
  color: #dc2626;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin-bottom: 2rem;
}

.error-icon {
  color: #dc2626;
}

.properties-table-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
  animation: slideUp 0.6s ease-out 0.2s both;
}

.properties-table {
  width: 100%;
  border-collapse: collapse;
}

.properties-table th {
  background: linear-gradient(0deg, #3b82f6, #06b6d4);
  color: white;
  padding: 1.25rem 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  position: relative;
}

.properties-table th::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: #c7d9f7;
}

.properties-table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid rgba(226, 232, 240, 0.5);
  vertical-align: middle;
  transition: all 0.2s ease;
  color: #1e293b;
}

.properties-table tbody tr:last-child td {
  border-bottom: none;
}

/* Alternating row colors */
.row-even {
  background-color: rgba(255, 255, 255, 0.6);
}

.row-odd {
  background-color: rgba(248, 250, 252, 0.8);
}

.properties-table tbody tr:hover {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(6, 182, 212, 0.08)) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.property-title {
  min-width: 200px;
}

.title-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.title {
  font-weight: 600;
  color: #2c3e50;
}

.featured-badge {
  background: #28a745;
  color: white;
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.price {
  font-weight: 600;
  color: #28a745;
  font-size: 1.1rem;
}

.location {
  color: #6c757d;
  font-size: 0.9rem;
}

.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-available {
  background: #d4edda;
  color: #155724;
}

.status-sold {
  background: #f8d7da;
  color: #721c24;
}

.status-rented {
  background: #d1ecf1;
  color: #0c5460;
}

.status-under-offer {
  background: #fff3cd;
  color: #856404;
}

.actions-cell {
  text-align: center;
  padding: 1rem 0.5rem !important;
  white-space: nowrap;
}

.edit-btn,
.delete-btn {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
  padding: 0.75rem;
  border-radius: 10px;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2.5rem;
  min-height: 2.5rem;
  margin: 0 0.25rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.edit-btn {
  color: #3b82f6;
}

.edit-btn:hover {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.edit-btn:active {
  transform: translateY(0) scale(1);
}

.delete-btn {
  color: #ef4444;
}

.delete-btn:hover {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3);
}

.delete-btn:active {
  transform: translateY(0) scale(1);
}

/* Pagination Styles */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.02), rgba(6, 182, 212, 0.02));
  border-top: 1px solid rgba(226, 232, 240, 0.5);
}

.pagination-info {
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(226, 232, 240, 0.5);
  border-radius: 10px;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.pagination-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.pagination-numbers {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin: 0 1rem;
}

.pagination-number {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(226, 232, 240, 0.3);
  border-radius: 8px;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination-number:hover {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  transform: translateY(-1px);
}

.pagination-number.active {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  border-color: transparent;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

@media (max-width: 1200px) {
  .properties-table {
    font-size: 0.9rem;
  }
  
  .properties-table th,
  .properties-table td {
    padding: 0.8rem;
  }
}

@media (max-width: 768px) {
  .properties {
    padding: 1rem;
  }
  
  .properties-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .properties-actions {
    width: 100%;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .btn-add-property {
    width: 100%;
    justify-content: center;
  }
  
  .properties-table-container {
    overflow-x: auto;
  }
  
  .properties-table {
    min-width: 900px;
  }
  
  .pagination-container {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }
  
  .pagination-controls {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .pagination-numbers {
    margin: 0 0.5rem;
  }
}
</style>