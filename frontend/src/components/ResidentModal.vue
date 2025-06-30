<template>
  <div v-if="isOpen" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <Users :size="24" class="users-icon" />
          </div>
          <div class="header-text">
            <h2>Add New Resident</h2>
            <p class="header-subtitle">Create a new resident profile</p>
          </div>
        </div>
        <button @click="closeModal" class="close-btn">
          <X :size="20" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="resident-form">
        <div class="form-grid">
          <!-- Personal Information -->
          <div class="form-section">
            <h3>Personal Information</h3>
            
            <div class="form-row">
              <div class="form-group">
                <label for="first_name">First Name *</label>
                <input
                  type="text"
                  id="first_name"
                  v-model="formData.first_name"
                  :class="{ 'error': errors.first_name }"
                  required
                />
                <span v-if="errors.first_name" class="error-message">{{ errors.first_name[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input
                  type="text"
                  id="last_name"
                  v-model="formData.last_name"
                  :class="{ 'error': errors.last_name }"
                  required
                />
                <span v-if="errors.last_name" class="error-message">{{ errors.last_name[0] }}</span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="email">Email *</label>
                <input
                  type="email"
                  id="email"
                  v-model="formData.email"
                  :class="{ 'error': errors.email }"
                  required
                />
                <span v-if="errors.email" class="error-message">{{ errors.email[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="phone">Phone</label>
                <input
                  type="tel"
                  id="phone"
                  v-model="formData.phone"
                  :class="{ 'error': errors.phone }"
                  placeholder="0XX XXX XXXX"
                />
                <span v-if="errors.phone" class="error-message">{{ errors.phone[0] }}</span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="id_number">ID Number *</label>
                <input
                  type="text"
                  id="id_number"
                  v-model="formData.id_number"
                  :class="{ 'error': errors.id_number }"
                  maxlength="13"
                  required
                />
                <span v-if="errors.id_number" class="error-message">{{ errors.id_number[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="date_of_birth">Date of Birth *</label>
                <input
                  type="date"
                  id="date_of_birth"
                  v-model="formData.date_of_birth"
                  :class="{ 'error': errors.date_of_birth }"
                  required
                />
                <span v-if="errors.date_of_birth" class="error-message">{{ errors.date_of_birth[0] }}</span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="gender">Gender *</label>
                <select
                  id="gender"
                  v-model="formData.gender"
                  :class="{ 'error': errors.gender }"
                  required
                >
                  <option value="">Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
                <span v-if="errors.gender" class="error-message">{{ errors.gender[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="occupation">Occupation</label>
                <input
                  type="text"
                  id="occupation"
                  v-model="formData.occupation"
                  :class="{ 'error': errors.occupation }"
                />
                <span v-if="errors.occupation" class="error-message">{{ errors.occupation[0] }}</span>
              </div>
            </div>
          </div>

          <!-- Emergency Contact -->
          <div class="form-section">
            <h3>Emergency Contact</h3>
            
            <div class="form-row">
              <div class="form-group">
                <label for="emergency_contact_name">Emergency Contact Name</label>
                <input
                  type="text"
                  id="emergency_contact_name"
                  v-model="formData.emergency_contact_name"
                  :class="{ 'error': errors.emergency_contact_name }"
                />
                <span v-if="errors.emergency_contact_name" class="error-message">{{ errors.emergency_contact_name[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
                <input
                  type="tel"
                  id="emergency_contact_phone"
                  v-model="formData.emergency_contact_phone"
                  :class="{ 'error': errors.emergency_contact_phone }"
                  placeholder="0XX XXX XXXX"
                />
                <span v-if="errors.emergency_contact_phone" class="error-message">{{ errors.emergency_contact_phone[0] }}</span>
              </div>
            </div>
          </div>

          <!-- Property & Lease Information -->
          <div class="form-section">
            <h3>Property & Lease Information</h3>
            
            <div class="form-row">
              <div class="form-group">
                <label for="property_id">Property *</label>
                <select
                  id="property_id"
                  v-model="formData.property_id"
                  :class="{ 'error': errors.property_id }"
                  required
                >
                  <option value="">Select Property</option>
                  <option v-for="property in properties" :key="property.id" :value="property.id">
                    {{ property.title }} - {{ property.city }}
                  </option>
                </select>
                <span v-if="errors.property_id" class="error-message">{{ errors.property_id[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="status">Status *</label>
                <select
                  id="status"
                  v-model="formData.status"
                  :class="{ 'error': errors.status }"
                  required
                >
                  <option value="">Select Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="pending">Pending</option>
                  <option value="terminated">Terminated</option>
                </select>
                <span v-if="errors.status" class="error-message">{{ errors.status[0] }}</span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="lease_start_date">Lease Start Date *</label>
                <input
                  type="date"
                  id="lease_start_date"
                  v-model="formData.lease_start_date"
                  :class="{ 'error': errors.lease_start_date }"
                  required
                />
                <span v-if="errors.lease_start_date" class="error-message">{{ errors.lease_start_date[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="lease_end_date">Lease End Date</label>
                <input
                  type="date"
                  id="lease_end_date"
                  v-model="formData.lease_end_date"
                  :class="{ 'error': errors.lease_end_date }"
                  :min="formData.lease_start_date"
                />
                <span v-if="errors.lease_end_date" class="error-message">{{ errors.lease_end_date[0] }}</span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="monthly_rent">Monthly Rent (R) *</label>
                <input
                  type="number"
                  id="monthly_rent"
                  v-model="formData.monthly_rent"
                  :class="{ 'error': errors.monthly_rent }"
                  min="0"
                  step="0.01"
                  required
                />
                <span v-if="errors.monthly_rent" class="error-message">{{ errors.monthly_rent[0] }}</span>
              </div>
              
              <div class="form-group">
                <label for="deposit_amount">Deposit Amount (R)</label>
                <input
                  type="number"
                  id="deposit_amount"
                  v-model="formData.deposit_amount"
                  :class="{ 'error': errors.deposit_amount }"
                  min="0"
                  step="0.01"
                />
                <span v-if="errors.deposit_amount" class="error-message">{{ errors.deposit_amount[0] }}</span>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="form-section full-width">
            <h3>Additional Notes</h3>
            <div class="form-group">
              <label for="notes">Notes</label>
              <textarea
                id="notes"
                v-model="formData.notes"
                :class="{ 'error': errors.notes }"
                rows="3"
                placeholder="Any additional information about the resident..."
              ></textarea>
              <span v-if="errors.notes" class="error-message">{{ errors.notes[0] }}</span>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" @click="closeModal" class="btn-cancel">
            <X :size="16" />
            <span>Cancel</span>
          </button>
          <button type="submit" :disabled="loading" class="btn-submit">
            <Loader2 v-if="loading" :size="16" class="loading-icon" />
            <UserPlus v-else :size="16" />
            <span>{{ loading ? 'Creating...' : 'Create Resident' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, reactive, watch, onMounted } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import { Users, X, UserPlus, Loader2 } from 'lucide-vue-next'
import { createApiUrl } from '../config/api'

interface Property {
  id: number
  title: string
  city: string
  province: string
}

interface ResidentFormData {
  property_id: string
  first_name: string
  last_name: string
  email: string
  phone: string
  id_number: string
  date_of_birth: string
  gender: string
  occupation: string
  emergency_contact_name: string
  emergency_contact_phone: string
  lease_start_date: string
  lease_end_date: string
  monthly_rent: string
  deposit_amount: string
  status: string
  notes: string
}

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  close: []
  residentCreated: [resident: any]
}>()

const loading = ref(false)
const properties = ref<Property[]>([])
const errors = ref<Record<string, string[]>>({})

const formData = reactive<ResidentFormData>({
  property_id: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  id_number: '',
  date_of_birth: '',
  gender: '',
  occupation: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  lease_start_date: '',
  lease_end_date: '',
  monthly_rent: '',
  deposit_amount: '',
  status: 'active',
  notes: ''
})

const resetForm = () => {
  Object.assign(formData, {
    property_id: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    id_number: '',
    date_of_birth: '',
    gender: '',
    occupation: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    lease_start_date: '',
    lease_end_date: '',
    monthly_rent: '',
    deposit_amount: '',
    status: 'active',
    notes: ''
  })
  errors.value = {}
}

const closeModal = () => {
  resetForm()
  emit('close')
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
  } catch (err) {
    console.error('Error fetching properties:', err)
  }
}

const handleSubmit = async () => {
  if (loading.value) return
  
  loading.value = true
  errors.value = {}
  
  try {
    const submitData = {
      ...formData,
      property_id: parseInt(formData.property_id),
      monthly_rent: parseFloat(formData.monthly_rent),
      deposit_amount: formData.deposit_amount ? parseFloat(formData.deposit_amount) : null,
    }
    
    const response = await axios.post(createApiUrl('/api/residents'), submitData, {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })
    
    emit('residentCreated', response.data.resident)
    closeModal()
    
  } catch (err: any) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      console.error('Error creating resident:', err)
    }
  } finally {
    loading.value = false
  }
}

watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    fetchProperties()
  }
})

onMounted(() => {
  if (props.isOpen) {
    fetchProperties()
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
  max-width: 1000px;
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
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.02), rgba(5, 150, 105, 0.02));
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}

.users-icon {
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

.resident-form {
  padding: 2rem;
}

.form-grid {
  display: grid;
  gap: 2rem;
  padding: 2rem;
}

.form-section {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(226, 232, 240, 0.3);
  border-radius: 16px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.form-section:hover {
  background: rgba(255, 255, 255, 0.8);
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.1);
}

.form-section.full-width {
  grid-column: 1 / -1;
}

.form-section h3 {
  margin: 0 0 1.5rem 0;
  color: #1e293b;
  font-size: 1.125rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-section h3::before {
  content: '';
  width: 4px;
  height: 1.5rem;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 2px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-row:last-child {
  margin-bottom: 0;
}

.form-group {
  display: flex;
  flex-direction: column;
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
  border-color: #10b981;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.95);
}

.form-group input:hover:not(:focus),
.form-group select:hover:not(:focus),
.form-group textarea:hover:not(:focus) {
  border-color: rgba(16, 185, 129, 0.3);
}

.form-group input.error,
.form-group select.error,
.form-group textarea.error {
  border-color: #ef4444;
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.error-message {
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 0.5rem;
  font-weight: 500;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(226, 232, 240, 0.3);
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
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.btn-submit:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.4);
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
    flex-direction: column;
  }
  
  .btn-cancel,
  .btn-submit {
    width: 100%;
  }
}
</style>