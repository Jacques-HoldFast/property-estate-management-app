<template>
  <AppLayout>
    <div class="residents">
      <header class="residents-header">
        <div class="header-content">
          <div class="header-icon">
            <Users :size="32" class="users-icon" />
          </div>
          <div class="header-text">
            <h1>Residents</h1>
            <p class="residents-subtitle">Manage tenant information and leases</p>
          </div>
        </div>
        <div class="residents-actions">
          <div class="residents-stats">
            <BarChart3 :size="16" />
            <span>Total: {{ residents.length }} residents</span>
          </div>
          <button @click="openAddModal" class="add-resident-btn">
            <UserPlus :size="18" />
            <span class="add-text">Add New Resident</span>
          </button>
        </div>
      </header>

      <div v-if="loading" class="loading">
        <Loader2 :size="32" class="loading-spinner" />
        <p>Loading residents...</p>
      </div>

      <div v-else-if="error" class="error">
        <AlertCircle :size="24" class="error-icon" />
        <p>{{ error }}</p>
      </div>

      <div v-else class="residents-table-container">
        <table class="residents-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Property</th>
              <th>Monthly Rent</th>
              <th>Lease Period</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(resident, index) in paginatedResidents"
              :key="resident.id"
              :class="{ 'row-even': index % 2 === 0, 'row-odd': index % 2 === 1 }"
            >
              <td class="resident-name">
                <div class="name-container">
                  <div class="full-name">{{ resident.full_name }}</div>
                  <div class="occupation">{{ resident.occupation }}</div>
                </div>
              </td>
              <td class="contact-info">
                <div>{{ resident.email }}</div>
              </td>
              <td class="contact-info">
                <div>{{ resident.phone || 'N/A' }}</div>
              </td>
              <td class="property-info">
                <div class="property-title">{{ resident.property?.title || 'Unknown Property' }}</div>
                <div class="property-location">{{ resident.property?.city }}, {{ resident.property?.province }}</div>
              </td>
              <td class="rent-info">
                <div class="rent-amount">R {{ formatPrice(resident.monthly_rent) }}</div>
              </td>
              <td class="lease-info">
                <div class="lease-dates">
                  <div>{{ formatDate(resident.lease_start_date) }}</div>
                  <div v-if="resident.lease_end_date" class="lease-end">
                    to {{ formatDate(resident.lease_end_date) }}
                  </div>
                  <div v-else class="lease-ongoing">Ongoing</div>
                </div>
              </td>
              <td>
                <span :class="['status-badge', `status-${resident.status}`]">
                  {{ formatStatus(resident.status) }}
                </span>
              </td>
              <td class="actions-cell">
                <button 
                  @click="confirmDelete(resident)" 
                  class="delete-btn"
                  :title="`Delete ${resident.full_name}`"
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
            <span>Showing {{ startItem }} to {{ endItem }} of {{ residents.length }} residents</span>
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

      <!-- Confirm Delete Dialog -->
      <ConfirmDialog
        :is-open="showDeleteDialog"
        :loading="isDeleting"
        title="Delete Resident"
        :message="`Are you sure you want to delete this resident?`"
        :details="residentToDelete?.full_name"
        confirm-text="Delete"
        @confirm="handleDeleteResident"
        @cancel="cancelDelete"
      />

      <!-- Add Resident Modal -->
      <ResidentModal
        :is-open="showAddModal"
        @close="closeAddModal"
        @resident-created="handleResidentCreated"
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
import AppLayout from '../components/AppLayout.vue'
import ConfirmDialog from '../components/ConfirmDialog.vue'
import ToastContainer from '../components/ToastContainer.vue'
import ResidentModal from '../components/ResidentModal.vue'
import { 
  Users, 
  BarChart3, 
  UserPlus, 
  Trash2, 
  Loader2, 
  AlertCircle,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

interface Property {
  id: number
  title: string
  city: string
  province: string
}

interface Resident {
  id: number
  property_id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone: string | null
  id_number: string
  date_of_birth: string
  gender: string
  occupation: string | null
  emergency_contact_name: string | null
  emergency_contact_phone: string | null
  lease_start_date: string
  lease_end_date: string | null
  monthly_rent: number
  deposit_amount: number | null
  status: string
  notes: string | null
  property?: Property
  created_at: string
  updated_at: string
}

const residents = ref<Resident[]>([])
const loading = ref(true)
const error = ref('')
const showDeleteDialog = ref(false)
const isDeleting = ref(false)
const residentToDelete = ref<Resident | null>(null)
const showAddModal = ref(false)
const toastContainer = ref<InstanceType<typeof ToastContainer> | null>(null)

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalPages = computed(() => Math.ceil(residents.value.length / itemsPerPage.value))
const startItem = computed(() => (currentPage.value - 1) * itemsPerPage.value + 1)
const endItem = computed(() => Math.min(currentPage.value * itemsPerPage.value, residents.value.length))

const paginatedResidents = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return residents.value.slice(start, end)
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

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const fetchResidents = async () => {
  try {
    loading.value = true
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
    } else {
      error.value = response.data.message || 'Failed to load residents'
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load residents'
    console.error('Error fetching residents:', err)
  } finally {
    loading.value = false
  }
}

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('en-ZA').format(price)
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-ZA', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatStatus = (status: string): string => {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const confirmDelete = (resident: Resident) => {
  residentToDelete.value = resident
  showDeleteDialog.value = true
}

const cancelDelete = () => {
  showDeleteDialog.value = false
  residentToDelete.value = null
}

const handleDeleteResident = async () => {
  if (!residentToDelete.value) return
  
  try {
    isDeleting.value = true
    
    await axios.delete(createApiUrl(`/api/residents/${residentToDelete.value.id}`), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    // Remove resident from the list
    const index = residents.value.findIndex(r => r.id === residentToDelete.value!.id)
    if (index > -1) {
      residents.value.splice(index, 1)
    }
    
    // Show success toast
    toastContainer.value?.addToast({
      type: 'success',
      title: 'Resident Deleted!',
      message: `"${residentToDelete.value.full_name}" has been removed from the system.`,
      duration: 4000
    })
    
    // Close dialog
    cancelDelete()
    
  } catch (err: any) {
    console.error('Error deleting resident:', err)
    
    // Show error toast
    let errorMessage = 'Failed to delete resident. Please try again.'
    if (err.response?.status === 404) {
      errorMessage = 'Resident not found. They may have already been deleted.'
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

const openAddModal = () => {
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
}

const handleResidentCreated = (newResident: Resident) => {
  residents.value.unshift(newResident)
  
  toastContainer.value?.addToast({
    type: 'success',
    title: 'Resident Added!',
    message: `"${newResident.full_name}" has been successfully added to the system.`,
    duration: 4000
  })
}

onMounted(() => {
  fetchResidents()
})
</script>

<style scoped>
.residents {
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

.residents-header {
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
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
  transition: all 0.3s ease;
}

.header-icon:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(16, 185, 129, 0.4);
}

.users-icon {
  color: white;
}

.header-text h1 {
  margin: 0 0 0.25rem 0;
  color: #1e293b;
  font-size: 2rem;
  font-weight: 700;
}

.residents-subtitle {
  margin: 0;
  color: #64748b;
  font-size: 1rem;
  font-weight: 500;
}

.residents-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.residents-stats {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.95rem;
  font-weight: 500;
}

.add-resident-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
}

.add-resident-btn:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(16, 185, 129, 0.4);
}

.add-resident-btn:active {
  transform: translateY(0);
}

.add-text {
  white-space: nowrap;
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

.residents-table-container {
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

.residents-table {
  width: 100%;
  border-collapse: collapse;
}

.residents-table th {
  background: #10b981;
  color: white;
  padding: 1.25rem 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  position: relative;
}

.residents-table th::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: #088a5f;
}

.residents-table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid rgba(226, 232, 240, 0.5);
  vertical-align: middle;
  transition: all 0.2s ease;
  color: #1e293b;
}

.residents-table tbody tr:last-child td {
  border-bottom: none;
}

/* Alternating row colors */
.row-even {
  background-color: rgba(255, 255, 255, 0.6);
}

.row-odd {
  background-color: rgba(248, 250, 252, 0.8);
}

.residents-table tbody tr:hover {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(6, 182, 212, 0.05)) !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
}

.resident-name {
  min-width: 180px;
}

.name-container {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.full-name {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.95rem;
}

.occupation {
  color: #6c757d;
  font-size: 0.8rem;
  font-style: italic;
}

.contact-info {
  color: #495057;
  font-size: 0.9rem;
}

.property-info {
  min-width: 200px;
}

.property-title {
  font-weight: 500;
  color: #2c3e50;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.property-location {
  color: #6c757d;
  font-size: 0.8rem;
}

.rent-info {
  text-align: right;
}

.rent-amount {
  font-weight: 600;
  color: #28a745;
  font-size: 1rem;
}

.lease-info {
  min-width: 140px;
}

.lease-dates {
  font-size: 0.85rem;
  line-height: 1.4;
}

.lease-end {
  color: #6c757d;
}

.lease-ongoing {
  color: #28a745;
  font-weight: 500;
}

.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-active {
  background: #d4edda;
  color: #155724;
}

.status-inactive {
  background: #f8d7da;
  color: #721c24;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-terminated {
  background: #e2e3e5;
  color: #383d41;
}

.actions-cell {
  text-align: center;
  padding: 1rem 0.5rem !important;
}

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
  color: #ef4444;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
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

@media (max-width: 1200px) {
  .residents-table {
    font-size: 0.9rem;
  }
  
  .residents-table th,
  .residents-table td {
    padding: 0.8rem;
  }
}

/* Pagination Styles */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.02), rgba(5, 150, 105, 0.02));
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
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
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
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  transform: translateY(-1px);
}

.pagination-number.active {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border-color: transparent;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}

@media (max-width: 768px) {
  .residents {
    padding: 1rem;
  }
  
  .residents-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .residents-actions {
    width: 100%;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .add-text {
    display: none;
  }
  
  .add-resident-btn {
    padding: 0.75rem;
    width: auto;
    min-width: 3rem;
    justify-content: center;
  }
  
  .residents-table-container {
    overflow-x: auto;
  }
  
  .residents-table {
    min-width: 1000px;
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