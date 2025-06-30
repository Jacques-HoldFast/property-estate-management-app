<template>
  <AppLayout>
    <div class="maintenance">
      <header class="maintenance-header">
        <div class="header-content">
          <div class="header-icon">
            <Wrench :size="32" class="wrench-icon" />
          </div>
          <div class="header-text">
            <h1>Maintenance Requests</h1>
            <p class="maintenance-subtitle">Manage property maintenance and repairs</p>
          </div>
        </div>
        <div class="maintenance-actions">
          <div class="maintenance-stats">
            <BarChart3 :size="16" />
            <span>Total: {{ maintenanceRequests.length }} requests</span>
          </div>
          <button @click="openAddModal" class="add-maintenance-btn">
            <Plus :size="18" />
            <span class="add-text">Add New Request</span>
          </button>
        </div>
      </header>

      <div class="filters-container">
        <div class="filter-group">
          <label for="status-filter">Status</label>
          <select id="status-filter" v-model="filters.status" @change="fetchMaintenanceRequests">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="assigned">Assigned</option>
            <option value="in_progress">In Progress</option>
            <option value="on_hold">On Hold</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="priority-filter">Priority</label>
          <select id="priority-filter" v-model="filters.priority" @change="fetchMaintenanceRequests">
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="category-filter">Category</label>
          <select id="category-filter" v-model="filters.category" @change="fetchMaintenanceRequests">
            <option value="">All Categories</option>
            <option value="plumbing">Plumbing</option>
            <option value="electrical">Electrical</option>
            <option value="hvac">HVAC</option>
            <option value="appliances">Appliances</option>
            <option value="structural">Structural</option>
            <option value="painting">Painting</option>
            <option value="landscaping">Landscaping</option>
            <option value="security">Security</option>
            <option value="cleaning">Cleaning</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="loading">
        <Loader2 :size="32" class="loading-spinner" />
        <p>Loading maintenance requests...</p>
      </div>

      <div v-else-if="maintenanceRequests.length > 0" class="maintenance-grid">
      <div 
        v-for="request in maintenanceRequests" 
        :key="request.id" 
        class="maintenance-card"
      >
        <div class="card-header">
          <div class="status-badges">
            <span :class="`status-badge status-${request.status}`">
              {{ formatStatus(request.status) }}
            </span>
            <span :class="`priority-badge priority-${request.priority}`">
              {{ formatPriority(request.priority) }}
            </span>
          </div>
          <div class="card-actions">
            <div class="card-date">
              {{ formatDate(request.reported_at) }}
            </div>
            <div class="action-buttons">
              <button @click="editRequest(request)" class="edit-btn" title="Edit Request">
                <Edit2 :size="16" />
              </button>
              <button @click="confirmDelete(request)" class="delete-btn" title="Delete Request">
                <Trash2 :size="16" />
              </button>
            </div>
          </div>
        </div>

        <div class="card-content">
          <h3 class="request-title">{{ request.title }}</h3>
          <p class="request-description">{{ request.description }}</p>
          
          <div class="request-details">
            <div class="detail-item">
              <span class="detail-label">Property:</span>
              <span class="detail-value">{{ request.property?.title || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Resident:</span>
              <span class="detail-value">{{ request.resident?.full_name || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Category:</span>
              <span class="detail-value">{{ formatCategory(request.category) }}</span>
            </div>
            <div class="detail-item" v-if="request.estimated_cost">
              <span class="detail-label">Est. Cost:</span>
              <span class="detail-value">R{{ formatCurrency(request.estimated_cost) }}</span>
            </div>
            <div class="detail-item" v-if="request.assigned_to">
              <span class="detail-label">Assigned to:</span>
              <span class="detail-value">{{ request.assigned_to }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

      <div v-else class="empty-state">
        <div class="empty-icon">
          <Wrench :size="64" />
        </div>
        <h3>No maintenance requests found</h3>
        <p>Get started by adding your first maintenance request.</p>
        <button @click="openAddModal" class="empty-add-btn">
          <Plus :size="18" />
          <span>Add First Request</span>
        </button>
      </div>

    <MaintenanceModal
      v-if="showModal"
      :maintenance-request="selectedRequest"
      :properties="properties"
      :residents="residents"
      @close="closeModal"
      @save="handleSave"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :is-open="showDeleteDialog"
      :loading="isDeleting"
      title="Delete Maintenance Request"
      :message="`Are you sure you want to delete this maintenance request?`"
      :details="requestToDelete?.title"
      confirm-text="Delete"
      @confirm="handleDeleteRequest"
      @cancel="cancelDelete"
    />
    </div>
  </AppLayout>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import { createApiUrl } from '../config/api'
import AppLayout from '../components/AppLayout.vue'
import MaintenanceModal from '../components/MaintenanceModal.vue'
import ConfirmDialog from '../components/ConfirmDialog.vue'
import { 
  Wrench, 
  BarChart3, 
  Plus, 
  Edit2, 
  Loader2,
  Trash2
} from 'lucide-vue-next'

interface Property {
  id: number
  title: string
  address: string
}

interface Resident {
  id: number
  property_id: number
  full_name: string
  email: string
}

interface MaintenanceRequest {
  id: number
  property_id: number
  resident_id: number
  title: string
  description: string
  category: string
  priority: string
  status: string
  estimated_cost?: number
  actual_cost?: number
  assigned_to?: string
  notes?: string
  reported_at: string
  assigned_at?: string
  started_at?: string
  completed_at?: string
  property?: Property
  resident?: Resident
}

const maintenanceRequests = ref<MaintenanceRequest[]>([])
const properties = ref<Property[]>([])
const residents = ref<Resident[]>([])
const loading = ref(false)
const showModal = ref(false)
const selectedRequest = ref<MaintenanceRequest | null>(null)
const showDeleteDialog = ref(false)
const isDeleting = ref(false)
const requestToDelete = ref<MaintenanceRequest | null>(null)

const filters = ref({
  status: '',
  priority: '',
  category: ''
})

const fetchMaintenanceRequests = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.status) params.append('status', filters.value.status)
    if (filters.value.priority) params.append('priority', filters.value.priority)
    if (filters.value.category) params.append('category', filters.value.category)

    const response = await axios.get(createApiUrl(`/api/maintenance-requests?${params}`), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    if (response.data.success) {
      maintenanceRequests.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching maintenance requests:', error)
  } finally {
    loading.value = false
  }
}

const fetchProperties = async () => {
  try {
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
    }
  } catch (error) {
    console.error('Error fetching properties:', error)
  }
}

const fetchResidents = async () => {
  try {
    const response = await axios.get(createApiUrl('/api/residents'), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    if (response.data.success) {
      residents.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching residents:', error)
  }
}

const openAddModal = () => {
  if (properties.value.length === 0) {
    fetchProperties()
  }
  
  if (residents.value.length === 0) {
    fetchResidents()
  }
  
  selectedRequest.value = null
  showModal.value = true
}

const editRequest = (request: MaintenanceRequest) => {
  selectedRequest.value = request
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedRequest.value = null
}

const handleSave = () => {
  closeModal()
  fetchMaintenanceRequests()
}

const confirmDelete = (request: MaintenanceRequest) => {
  requestToDelete.value = request
  showDeleteDialog.value = true
}

const cancelDelete = () => {
  showDeleteDialog.value = false
  requestToDelete.value = null
}

const handleDeleteRequest = async () => {
  if (!requestToDelete.value) return
  
  try {
    isDeleting.value = true
    
    await axios.delete(createApiUrl(`/api/maintenance-requests/${requestToDelete.value.id}`), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    // Remove request from the list
    const index = maintenanceRequests.value.findIndex(r => r.id === requestToDelete.value!.id)
    if (index > -1) {
      maintenanceRequests.value.splice(index, 1)
    }
    
    // Close dialog
    cancelDelete()
    
  } catch (err: any) {
    console.error('Error deleting maintenance request:', err)
    alert('Failed to delete maintenance request. Please try again.')
  } finally {
    isDeleting.value = false
  }
}

const formatStatus = (status: string) => {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatPriority = (priority: string) => {
  return priority.charAt(0).toUpperCase() + priority.slice(1)
}

const formatCategory = (category: string) => {
  return category.charAt(0).toUpperCase() + category.slice(1)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount: number) => {
  return amount.toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

onMounted(() => {
  fetchMaintenanceRequests()
  fetchProperties()
  fetchResidents()
})
</script>

<style scoped>
.maintenance {
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

.maintenance-header {
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
  background: linear-gradient(135deg, #f59e0b, #d97706);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
  transition: all 0.3s ease;
}

.header-icon:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(245, 158, 11, 0.4);
}

.wrench-icon {
  color: white;
}

.header-text h1 {
  margin: 0 0 0.25rem 0;
  color: #1e293b;
  font-size: 2rem;
  font-weight: 700;
}

.maintenance-subtitle {
  margin: 0;
  color: #64748b;
  font-size: 1rem;
  font-weight: 500;
}

.maintenance-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.maintenance-stats {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.95rem;
  font-weight: 500;
}

.add-maintenance-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
}

.add-maintenance-btn:hover {
  background: linear-gradient(135deg, #d97706, #b45309);
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(245, 158, 11, 0.4);
}

.add-maintenance-btn:active {
  transform: translateY(0);
}

.add-text {
  white-space: nowrap;
}

.filters-container {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  animation: slideUp 0.6s ease-out 0.1s both;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  min-width: 180px;
}

.filter-group label {
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
}

.filter-group select {
  padding: 1rem 1.25rem;
  border: 2px solid rgba(226, 232, 240, 0.8);
  border-radius: 12px;
  font-size: 0.95rem;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  color: #1e293b;
}

.filter-group select:focus {
  outline: none;
  border-color: #f59e0b;
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.95);
}

.filter-group select:hover:not(:focus) {
  border-color: rgba(245, 158, 11, 0.3);
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

.maintenance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
  animation: slideUp 0.6s ease-out 0.2s both;
}

.maintenance-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
  animation: slideUp 0.6s ease-out;
}

.maintenance-card:hover {
  transform: translateY(-4px);
  box-shadow: 
    0 32px 64px -12px rgba(0, 0, 0, 0.35),
    0 0 0 1px rgba(255, 255, 255, 0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.card-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-badges {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.status-badge, .priority-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-assigned { background: #cce5ff; color: #004085; }
.status-in_progress { background: #e6ccff; color: #5a1a7a; }
.status-on_hold { background: #ffe6cc; color: #cc6600; }
.status-completed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.priority-low { background: #d4edda; color: #155724; }
.priority-medium { background: #fff3cd; color: #856404; }
.priority-high { background: #ffe6cc; color: #cc6600; }
.priority-urgent { background: #f8d7da; color: #721c24; }

.card-date {
  color: #666;
  font-size: 0.85rem;
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
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.edit-btn {
  color: #f59e0b;
}

.edit-btn:hover {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
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

.edit-btn:active,
.delete-btn:active {
  transform: translateY(0) scale(1);
}

.card-content {
  flex: 1;
}

.request-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 0.5rem 0;
}

.request-description {
  color: #666;
  margin: 0 0 1rem 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.request-details {
  display: grid;
  gap: 0.5rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.detail-label {
  color: #666;
  font-weight: 500;
}

.detail-value {
  color: #2c3e50;
  font-weight: 500;
  text-align: right;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #64748b;
  animation: slideUp 0.6s ease-out 0.2s both;
}

.empty-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 2rem;
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
  color: white;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin: 0 0 0.5rem 0;
  color: #1e293b;
  font-weight: 700;
}

.empty-state p {
  margin: 0 0 2rem 0;
  font-size: 1rem;
  color: #64748b;
}

.empty-add-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
}

.empty-add-btn:hover {
  background: linear-gradient(135deg, #d97706, #b45309);
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(245, 158, 11, 0.4);
}

@media (max-width: 768px) {
  .maintenance {
    padding: 1rem;
  }
  
  .maintenance-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .maintenance-actions {
    width: 100%;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .add-text {
    display: none;
  }
  
  .add-maintenance-btn {
    padding: 0.75rem;
    width: auto;
    min-width: 3rem;
    justify-content: center;
  }
  
  .maintenance-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-container {
    flex-direction: column;
    gap: 1rem;
  }
  
  .filter-group {
    min-width: auto;
  }
}
</style>