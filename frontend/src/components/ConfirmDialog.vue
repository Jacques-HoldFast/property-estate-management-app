<template>
  <div v-if="isOpen" class="confirm-overlay" @click="handleCancel">
    <div class="confirm-dialog" @click.stop>
      <div class="confirm-header">
        <div class="confirm-icon">
          <span class="warning-icon">⚠️</span>
        </div>
        <h3 class="confirm-title">{{ title }}</h3>
      </div>

      <div class="confirm-content">
        <p class="confirm-message">{{ message }}</p>
        <div v-if="details" class="confirm-details">
          {{ details }}
        </div>
      </div>

      <div class="confirm-actions">
        <button 
          @click="handleCancel" 
          class="btn-cancel"
          :disabled="loading"
        >
          Cancel
        </button>
        <button 
          @click="handleConfirm" 
          class="btn-confirm"
          :disabled="loading"
          :class="{ 'btn-loading': loading }"
        >
          {{ loading ? 'Deleting...' : confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
interface Props {
  isOpen: boolean
  title?: string
  message?: string
  details?: string
  confirmText?: string
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Confirm Action',
  message: 'Are you sure you want to proceed?',
  confirmText: 'Confirm',
  loading: false
})

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()

const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  if (!props.loading) {
    emit('cancel')
  }
}
</script>

<style scoped>
.confirm-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}

.confirm-dialog {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  max-width: 400px;
  width: 100%;
  overflow: hidden;
  animation: dialogSlideIn 0.2s ease-out;
}

@keyframes dialogSlideIn {
  from {
    opacity: 0;
    transform: translateY(-1rem) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.confirm-header {
  text-align: center;
  padding: 2rem 1.5rem 1rem;
}

.confirm-icon {
  margin-bottom: 1rem;
}

.warning-icon {
  font-size: 3rem;
  display: block;
}

.confirm-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.confirm-content {
  padding: 0 1.5rem 1.5rem;
  text-align: center;
}

.confirm-message {
  margin: 0 0 0.5rem 0;
  color: #6b7280;
  font-size: 0.95rem;
  line-height: 1.5;
}

.confirm-details {
  color: #374151;
  font-weight: 500;
  font-size: 0.9rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.confirm-actions {
  display: flex;
  gap: 0.75rem;
  padding: 1.5rem;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.btn-cancel,
.btn-confirm {
  flex: 1;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-cancel:hover:not(:disabled) {
  background: #e5e7eb;
}

.btn-cancel:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-confirm {
  background: #dc2626;
  color: white;
}

.btn-confirm:hover:not(:disabled) {
  background: #b91c1c;
}

.btn-confirm:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-loading {
  position: relative;
}

.btn-loading::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 1rem;
  height: 1rem;
  border: 2px solid transparent;
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-left: 0.5rem;
}

@keyframes spin {
  to {
    transform: translate(-50%, -50%) rotate(360deg);
  }
}

@media (max-width: 480px) {
  .confirm-dialog {
    margin: 0.5rem;
    max-width: none;
  }
  
  .confirm-actions {
    flex-direction: column;
  }
  
  .btn-cancel,
  .btn-confirm {
    width: 100%;
  }
}
</style>