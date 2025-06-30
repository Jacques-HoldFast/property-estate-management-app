<template>
  <div class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <Wrench :size="24" class="wrench-icon" />
          </div>
          <div class="header-text">
            <h2>{{ isEditing ? 'Edit Maintenance Request' : 'Add Maintenance Request' }}</h2>
            <p class="header-subtitle">{{ isEditing ? 'Update maintenance request details' : 'Create a new maintenance request' }}</p>
          </div>
        </div>
        <button @click="closeModal" class="close-btn">
          <X :size="20" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="maintenance-form">
        <div class="form-row">
          <div class="form-group">
            <label for="property">Property *</label>
            <select 
              id="property" 
              v-model="form.property_id" 
              @change="onPropertyChange"
              required
              :disabled="isEditing"
            >
              <option value="">Select a property</option>
              <option v-for="property in properties" :key="property.id" :value="property.id">
                {{ property.title }} - {{ property.address }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="resident">Resident *</label>
            <select id="resident" v-model="form.resident_id" required :disabled="isEditing">
              <option value="">Select a resident</option>
              <option 
                v-for="resident in filteredResidents" 
                :key="resident.id" 
                :value="resident.id"
              >
                {{ resident.full_name }} ({{ resident.email }})
              </option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="title">Title *</label>
          <input 
            type="text" 
            id="title" 
            v-model="form.title" 
            required
            placeholder="Brief description of the issue"
          />
        </div>

        <div class="form-group">
          <label for="description">Description *</label>
          <textarea 
            id="description" 
            v-model="form.description" 
            required
            rows="4"
            placeholder="Detailed description of the maintenance request"
          ></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" v-model="form.category" required>
              <option value="">Select category</option>
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

          <div class="form-group">
            <label for="priority">Priority *</label>
            <select id="priority" v-model="form.priority" required>
              <option value="">Select priority</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>

        <div class="form-row" v-if="isEditing">
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" v-model="form.status">
              <option value="pending">Pending</option>
              <option value="assigned">Assigned</option>
              <option value="in_progress">In Progress</option>
              <option value="on_hold">On Hold</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div class="form-group">
            <label for="assigned_to">Assigned To</label>
            <input 
              type="text" 
              id="assigned_to" 
              v-model="form.assigned_to"
              placeholder="Contractor or technician name"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="estimated_cost">Estimated Cost (R)</label>
            <input 
              type="number" 
              id="estimated_cost" 
              v-model="form.estimated_cost"
              min="0"
              step="0.01"
              placeholder="0.00"
            />
          </div>

          <div class="form-group" v-if="isEditing">
            <label for="actual_cost">Actual Cost (R)</label>
            <input 
              type="number" 
              id="actual_cost" 
              v-model="form.actual_cost"
              min="0"
              step="0.01"
              placeholder="0.00"
            />
          </div>
        </div>

        <div class="form-group">
          <label for="notes">Notes</label>
          <textarea 
            id="notes" 
            v-model="form.notes"
            rows="3"
            placeholder="Additional notes or comments"
          ></textarea>
        </div>

        <div class="form-actions">
          <button type="button" @click="closeModal" class="btn-cancel">
            <X :size="16" />
            <span>Cancel</span>
          </button>
          <button type="submit" :disabled="loading" class="btn-submit">
            <Loader2 v-if="loading" :size="16" class="loading-icon" />
            <Save v-else :size="16" />
            <span>{{ loading ? 'Saving...' : (isEditing ? 'Update Request' : 'Create Request') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import { Wrench, X, Save, Loader2 } from 'lucide-vue-next'
import { createApiUrl } from '../config/api'

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
  id?: number
  property_id: number
  resident_id: number
  title: string
  description: string
  category: string
  priority: string
  status?: string
  estimated_cost?: number
  actual_cost?: number
  assigned_to?: string
  notes?: string
}

const props = defineProps<{
  maintenanceRequest?: MaintenanceRequest | null
  properties: Property[]
  residents: Resident[]
}>()

const emit = defineEmits<{
  close: []
  save: []
}>()

const loading = ref(false)

const form = ref<MaintenanceRequest>({
  property_id: '' as any,
  resident_id: '' as any,
  title: '',
  description: '',
  category: '',
  priority: '',
  status: 'pending',
  estimated_cost: undefined,
  actual_cost: undefined,
  assigned_to: '',
  notes: '',
})

const isEditing = computed(() => !!props.maintenanceRequest?.id)

const filteredResidents = computed(() => {
  if (!form.value.property_id) return []
  return props.residents.filter(resident => resident.property_id === form.value.property_id)
})

const onPropertyChange = () => {
  form.value.resident_id = '' as any
}

const handleSubmit = async () => {
  // Validate required fields
  if (!form.value.property_id) {
    alert('Please select a property')
    return
  }
  
  if (!form.value.resident_id) {
    alert('Please select a resident')
    return
  }

  loading.value = true
  
  try {
    const url = isEditing.value 
      ? createApiUrl(`/api/maintenance-requests/${props.maintenanceRequest!.id}`)
      : createApiUrl('/api/maintenance-requests')
    
    const method = isEditing.value ? 'put' : 'post'
    
    const data = { ...form.value }
    if (!data.estimated_cost) delete data.estimated_cost
    if (!data.actual_cost) delete data.actual_cost
    if (!data.assigned_to) delete data.assigned_to
    if (!data.notes) delete data.notes

    const response = await axios[method](url, data, {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })

    if (response.data.success) {
      emit('save')
    } else {
      console.error('Error saving maintenance request:', response.data.message)
      alert('Error saving maintenance request: ' + response.data.message)
    }
  } catch (error: any) {
    console.error('Error saving maintenance request:', error)
    const message = error.response?.data?.message || 'An error occurred while saving the maintenance request'
    alert('Error: ' + message)
  } finally {
    loading.value = false
  }
}


const closeModal = () => {
  emit('close')
}

onMounted(() => {
  if (props.maintenanceRequest) {
    form.value = { ...props.maintenanceRequest }
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  0% { 
    opacity: 0;
  }
  100% { 
    opacity: 1;
  }
}

.modal-content {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  0% { 
    transform: translateY(30px);
    opacity: 0;
    scale: 0.95;
  }
  100% { 
    transform: translateY(0);
    opacity: 1;
    scale: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem;
  border-bottom: 1px solid rgba(226, 232, 240, 0.3);
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.02), rgba(217, 119, 6, 0.02));
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3);
}

.wrench-icon {
  color: white;
}

.header-text h2 {
  margin: 0 0 0.25rem 0;
  color: #1e293b;
  font-size: 1.5rem;
  font-weight: 700;
}

.header-subtitle {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
}

.close-btn {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(226, 232, 240, 0.3);
  border-radius: 10px;
  cursor: pointer;
  color: #64748b;
  padding: 0.75rem;
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  transform: scale(1.05);
  box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
}

.maintenance-form {
  padding: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.75rem;
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 1rem 1.25rem;
  border: 2px solid rgba(226, 232, 240, 0.8);
  border-radius: 12px;
  font-size: 0.95rem;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  color: #1e293b;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #9ca3af;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #f59e0b;
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.95);
}

.form-group input:hover:not(:focus),
.form-group select:hover:not(:focus),
.form-group textarea:hover:not(:focus) {
  border-color: rgba(245, 158, 11, 0.3);
}

.form-group input:disabled,
.form-group select:disabled {
  background: rgba(241, 245, 249, 0.8);
  color: #6b7280;
  border-color: rgba(226, 232, 240, 0.5);
}

.form-group textarea {
  resize: vertical;
  min-height: 120px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(226, 232, 240, 0.3);
  flex-wrap: wrap;
}

.btn-cancel,
.btn-submit {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  min-width: 140px;
  justify-content: center;
}

.btn-cancel {
  background: rgba(107, 114, 128, 0.9);
  color: white;
  border: 1px solid rgba(107, 114, 128, 0.2);
}

.btn-cancel:hover {
  background: linear-gradient(135deg, #6b7280, #4b5563);
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(107, 114, 128, 0.3);
}

.btn-submit {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.btn-submit:hover:not(:disabled) {
  background: linear-gradient(135deg, #d97706, #b45309);
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
}

.btn-submit:disabled {
  background: rgba(107, 114, 128, 0.6);
  cursor: not-allowed;
  transform: none;
  opacity: 0.7;
}


.loading-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .modal-content {
    margin: 0.5rem;
    max-height: 95vh;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column-reverse;
  }
  
  .btn-cancel,
  .btn-submit {
    width: 100%;
  }
}
</style>