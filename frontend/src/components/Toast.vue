<template>
  <Transition name="toast" appear>
    <div v-if="visible" :class="['toast', `toast-${type}`]">
      <div class="toast-icon">
        <span v-if="type === 'success'">✓</span>
        <span v-else-if="type === 'error'">✗</span>
        <span v-else-if="type === 'warning'">⚠</span>
        <span v-else>ℹ</span>
      </div>
      <div class="toast-content">
        <div class="toast-title">{{ title }}</div>
        <div v-if="message" class="toast-message">{{ message }}</div>
      </div>
      <button @click="close" class="toast-close">×</button>
    </div>
  </Transition>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'

interface Props {
  type?: 'success' | 'error' | 'warning' | 'info'
  title: string
  message?: string
  duration?: number
  autoClose?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  duration: 5000,
  autoClose: true
})

const emit = defineEmits<{
  close: []
}>()

const visible = ref(true)

const close = () => {
  visible.value = false
  setTimeout(() => {
    emit('close')
  }, 300) // Wait for transition to complete
}

onMounted(() => {
  if (props.autoClose) {
    setTimeout(() => {
      close()
    }, props.duration)
  }
})
</script>

<style scoped>
.toast {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  min-width: 300px;
  max-width: 500px;
  padding: 1rem;
  margin-bottom: 0.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-left: 4px solid transparent;
  position: relative;
}

.toast-success {
  border-left-color: #28a745;
}

.toast-error {
  border-left-color: #dc3545;
}

.toast-warning {
  border-left-color: #ffc107;
}

.toast-info {
  border-left-color: #17a2b8;
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  font-weight: bold;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.toast-success .toast-icon {
  background: #d4edda;
  color: #155724;
}

.toast-error .toast-icon {
  background: #f8d7da;
  color: #721c24;
}

.toast-warning .toast-icon {
  background: #fff3cd;
  color: #856404;
}

.toast-info .toast-icon {
  background: #d1ecf1;
  color: #0c5460;
}

.toast-content {
  flex: 1;
}

.toast-title {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.toast-message {
  color: #6c757d;
  font-size: 0.85rem;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #6c757d;
  font-size: 1.25rem;
  padding: 0;
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.toast-close:hover {
  background: #f8f9fa;
  color: #2c3e50;
}

/* Transition animations */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>