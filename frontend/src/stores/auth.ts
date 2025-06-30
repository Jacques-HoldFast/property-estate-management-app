import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import Cookies from 'js-cookie'
import { createApiUrl } from '../config/api'

interface User {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = ref(false)

  const checkAuth = async (): Promise<boolean> => {
    try {
      const response = await axios.get(createApiUrl('/api/user'), {
        withCredentials: true,
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
        },
      })
      user.value = response.data
      isAuthenticated.value = true
      return true
    } catch (error) {
      user.value = null
      isAuthenticated.value = false
      return false
    }
  }

  const login = (userData: User) => {
    user.value = userData
    isAuthenticated.value = true
  }

  const logout = () => {
    user.value = null
    isAuthenticated.value = false
  }

  return {
    user,
    isAuthenticated,
    checkAuth,
    login,
    logout
  }
})