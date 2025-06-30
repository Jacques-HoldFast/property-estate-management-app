<template>
  <Teleport to="body">
    <div class="toast-container">
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        :type="toast.type"
        :title="toast.title"
        :message="toast.message"
        :duration="toast.duration"
        :auto-close="toast.autoClose"
        @close="removeToast(toast.id)"
      />
    </div>
  </Teleport>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import Toast from './Toast.vue'

interface ToastItem {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title: string
  message?: string
  duration?: number
  autoClose?: boolean
}

const toasts = ref<ToastItem[]>([])

const addToast = (toast: Omit<ToastItem, 'id'>) => {
  const id = Date.now().toString() + Math.random().toString(36).substr(2, 9)
  toasts.value.push({ ...toast, id })
}

const removeToast = (id: string) => {
  const index = toasts.value.findIndex(toast => toast.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

// Expose methods for parent components
defineExpose({
  addToast,
  removeToast
})
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 9999;
  pointer-events: none;
}

.toast-container > * {
  pointer-events: auto;
}

@media (max-width: 768px) {
  .toast-container {
    top: 1rem;
    left: 1rem;
    right: 1rem;
  }
}
</style>