<template>
  <div v-if="isOpen" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <div class="header-content">
          <div class="header-icon">
            <Building2 :size="24" class="building-icon" />
          </div>
          <div class="header-text">
            <h2>{{ isEditMode ? 'Edit Property' : 'Add New Property' }}</h2>
            <p class="header-subtitle">{{ isEditMode ? 'Update property information' : 'Create a new property listing' }}</p>
          </div>
        </div>
        <button @click="closeModal" class="close-btn">
          <X :size="20" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="property-form">
        <div class="form-grid">
          <!-- Basic Information -->
          <div class="form-section">
            <h3>Basic Information</h3>
            
            <div class="form-group">
              <label for="title">Property Title *</label>
              <input
                v-model="form.title"
                id="title"
                type="text"
                required
                placeholder="e.g., Beautiful 3 Bedroom House"
              />
            </div>

            <div class="form-group">
              <label for="description">Description *</label>
              <textarea
                v-model="form.description"
                id="description"
                required
                rows="4"
                placeholder="Describe the property features, location, and amenities..."
              ></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="type">Property Type *</label>
                <select v-model="form.type" id="type" required>
                  <option value="">Select Type</option>
                  <option value="House">House</option>
                  <option value="Apartment">Apartment</option>
                  <option value="Townhouse">Townhouse</option>
                  <option value="Villa">Villa</option>
                  <option value="Duplex">Duplex</option>
                  <option value="Penthouse">Penthouse</option>
                  <option value="Studio">Studio</option>
                </select>
              </div>

              <div class="form-group">
                <label for="status">Status</label>
                <select v-model="form.status" id="status">
                  <option value="available">Available</option>
                  <option value="under_offer">Under Offer</option>
                  <option value="sold">Sold</option>
                  <option value="rented">Rented</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Property Details -->
          <div class="form-section">
            <h3>Property Details</h3>
            
            <div class="form-row">
              <div class="form-group">
                <label for="price">Price (R) *</label>
                <input
                  v-model.number="form.price"
                  id="price"
                  type="number"
                  required
                  min="0"
                  step="1000"
                  placeholder="e.g., 2500000"
                />
              </div>

              <div class="form-group">
                <label for="size_sqm">Size (m²) *</label>
                <input
                  v-model.number="form.size_sqm"
                  id="size_sqm"
                  type="number"
                  required
                  min="1"
                  placeholder="e.g., 150"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="bedrooms">Bedrooms *</label>
                <input
                  v-model.number="form.bedrooms"
                  id="bedrooms"
                  type="number"
                  required
                  min="0"
                  placeholder="e.g., 3"
                />
              </div>

              <div class="form-group">
                <label for="bathrooms">Bathrooms *</label>
                <input
                  v-model.number="form.bathrooms"
                  id="bathrooms"
                  type="number"
                  required
                  min="1"
                  placeholder="e.g., 2"
                />
              </div>

              <div class="form-group">
                <label for="parking_spaces">Parking Spaces</label>
                <input
                  v-model.number="form.parking_spaces"
                  id="parking_spaces"
                  type="number"
                  min="0"
                  placeholder="e.g., 2"
                />
              </div>
            </div>
          </div>

          <!-- Location -->
          <div class="form-section">
            <h3>Location</h3>
            
            <div class="form-group">
              <label for="address">Street Address *</label>
              <input
                v-model="form.address"
                id="address"
                type="text"
                required
                placeholder="e.g., 123 Main Street"
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="city">City *</label>
                <select v-model="form.city" id="city" required @change="updateProvince">
                  <option value="">Select City</option>
                  <option v-for="city in southAfricanCities" :key="city.name" :value="city.name">
                    {{ city.name }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label for="province">Province *</label>
                <input
                  v-model="form.province"
                  id="province"
                  type="text"
                  required
                  readonly
                  placeholder="Province will be auto-filled"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="postal_code">Postal Code *</label>
                <input
                  v-model="form.postal_code"
                  id="postal_code"
                  type="text"
                  required
                  placeholder="e.g., 8001"
                />
              </div>

              <div class="form-group">
                <label>
                  <input v-model="form.is_featured" type="checkbox" />
                  Featured Property
                </label>
              </div>
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
            <Save v-else :size="16" />
            <span>{{ loading ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Property' : 'Create Property') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch, computed } from 'vue'
import { Building2, X, Save, Loader2 } from 'lucide-vue-next'

interface PropertyForm {
  title: string
  description: string
  type: string
  price: number | null
  bedrooms: number | null
  bathrooms: number | null
  parking_spaces: number | null
  size_sqm: number | null
  address: string
  city: string
  province: string
  postal_code: string
  latitude: number | null
  longitude: number | null
  status: string
  is_featured: boolean
}

const props = defineProps<{
  isOpen: boolean
  loading: boolean
  editProperty?: PropertyForm | null
}>()

const emit = defineEmits<{
  close: []
  submit: [form: PropertyForm]
}>()

const southAfricanCities = [
  { name: 'Cape Town', province: 'Western Cape' },
  { name: 'Johannesburg', province: 'Gauteng' },
  { name: 'Durban', province: 'KwaZulu-Natal' },
  { name: 'Pretoria', province: 'Gauteng' },
  { name: 'Port Elizabeth', province: 'Eastern Cape' },
  { name: 'Bloemfontein', province: 'Free State' },
  { name: 'East London', province: 'Eastern Cape' },
  { name: 'Pietermaritzburg', province: 'KwaZulu-Natal' },
  { name: 'Kimberley', province: 'Northern Cape' },
  { name: 'Polokwane', province: 'Limpopo' },
  { name: 'Nelspruit', province: 'Mpumalanga' },
  { name: 'Rustenburg', province: 'North West' },
]

const form = ref<PropertyForm>({
  title: '',
  description: '',
  type: '',
  price: null,
  bedrooms: null,
  bathrooms: null,
  parking_spaces: null,
  size_sqm: null,
  address: '',
  city: '',
  province: '',
  postal_code: '',
  latitude: null,
  longitude: null,
  status: 'available',
  is_featured: false,
})

const isEditMode = computed(() => !!props.editProperty)

const resetForm = () => {
  form.value = {
    title: '',
    description: '',
    type: '',
    price: null,
    bedrooms: null,
    bathrooms: null,
    parking_spaces: null,
    size_sqm: null,
    address: '',
    city: '',
    province: '',
    postal_code: '',
    latitude: null,
    longitude: null,
    status: 'available',
    is_featured: false,
  }
}

const populateForm = (property: PropertyForm) => {
  form.value = {
    title: property.title || '',
    description: property.description || '',
    type: property.type || '',
    price: property.price,
    bedrooms: property.bedrooms,
    bathrooms: property.bathrooms,
    parking_spaces: property.parking_spaces,
    size_sqm: property.size_sqm,
    address: property.address || '',
    city: property.city || '',
    province: property.province || '',
    postal_code: property.postal_code || '',
    latitude: property.latitude,
    longitude: property.longitude,
    status: property.status || 'available',
    is_featured: property.is_featured || false,
  }
}

const updateProvince = () => {
  const selectedCity = southAfricanCities.find(city => city.name === form.value.city)
  if (selectedCity) {
    form.value.province = selectedCity.province
  }
}

const closeModal = () => {
  resetForm()
  emit('close')
}

const handleSubmit = () => {
  emit('submit', form.value)
}

// Handle modal open/close and edit mode
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    if (props.editProperty) {
      populateForm(props.editProperty)
    } else {
      resetForm()
    }
  } else {
    resetForm()
  }
})

// Watch for changes in edit property
watch(() => props.editProperty, (newProperty) => {
  if (newProperty && props.isOpen) {
    populateForm(newProperty)
  }
}, { deep: true })
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
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.02), rgba(6, 182, 212, 0.02));
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.building-icon {
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

.property-form {
  padding: 2rem;
}

.form-grid {
  display: grid;
  gap: 2rem;
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
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.1);
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
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 2px;
}

.form-group {
  margin-bottom: 1rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-row.triple {
  grid-template-columns: 1fr 1fr 1fr;
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
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.95);
}

.form-group input:hover:not(:focus),
.form-group select:hover:not(:focus),
.form-group textarea:hover:not(:focus) {
  border-color: rgba(59, 130, 246, 0.3);
}

.form-group input[type="checkbox"] {
  width: auto;
  margin-right: 0.75rem;
  transform: scale(1.2);
  accent-color: #3b82f6;
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
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.btn-submit:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #0891b2);
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.4);
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