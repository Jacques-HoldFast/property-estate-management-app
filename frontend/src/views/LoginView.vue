<template>
  <div class="login-container">
    <div class="login-background">
      <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
      </div>
    </div>
    
    <div class="login-content">
      <div class="login-card">
        <!-- Header -->
        <div class="login-header">
          <div class="logo-section">
            <div class="logo-icon">
              <Building2 :size="32" class="logo-building" />
            </div>
            <div class="logo-text">
              <h1>Estatly</h1>
              <p>Professional Property Management</p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-header">
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue</p>
          </div>

          <!-- Email Field -->
          <div class="form-group">
            <label for="email" class="form-label">
              <Mail :size="18" class="label-icon" />
              Email Address
            </label>
            <div class="input-wrapper">
              <input 
                v-model="form.email" 
                id="email" 
                type="email" 
                required 
                class="form-input"
                :class="{ 'input-error': error && error.toLowerCase().includes('email') }"
                placeholder="Enter your email address"
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label for="password" class="form-label">
              <Lock :size="18" class="label-icon" />
              Password
            </label>
            <div class="input-wrapper">
              <input 
                v-model="form.password" 
                id="password" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                minlength="8" 
                class="form-input"
                :class="{ 'input-error': error && error.toLowerCase().includes('password') }"
                placeholder="Enter your password"
              />
              <button 
                type="button" 
                @click="togglePassword" 
                class="password-toggle"
                :title="showPassword ? 'Hide password' : 'Show password'"
              >
                <EyeOff v-if="showPassword" :size="18" />
                <Eye v-else :size="18" />
              </button>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="error-message">
            <AlertCircle :size="18" class="error-icon" />
            <span>{{ error }}</span>
          </div>

          <!-- Submit Button -->
          <button type="submit" :disabled="loading" class="submit-button">
            <div class="button-content">
              <Loader2 v-if="loading" :size="18" class="loading-icon" />
              <LogIn v-else :size="18" class="login-icon" />
              <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
            </div>
          </button>

          <!-- Footer Links -->
          <div class="form-footer">
            <router-link to="/register" class="footer-link">
              <UserPlus :size="16" />
              <span>Don't have an account? Sign up</span>
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Cookies from 'js-cookie'
import { useAuthStore } from '../stores/auth'
import { createApiUrl } from '../config/api'
import { 
  Building2, 
  Mail, 
  Lock, 
  Eye, 
  EyeOff, 
  LogIn, 
  UserPlus, 
  AlertCircle, 
  Loader2 
} from 'lucide-vue-next'

interface LoginForm {
  email: string
  password: string
}

const router = useRouter()
const authStore = useAuthStore()
const form = ref<LoginForm>({ email: '', password: '' })
const error = ref<string>('')
const loading = ref(false)
const showPassword = ref(false)

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const handleLogin = async () => {
  error.value = ''
  loading.value = true
  try {
    await axios.get(createApiUrl('/sanctum/csrf-cookie'), { withCredentials: true })
    await axios.post(createApiUrl('/login'), form.value, {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
         'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })
    const response = await axios.get(createApiUrl('/api/user'), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })
    authStore.login(response.data)
    router.push('/dashboard')
  } catch (e: any) {
    if (e.response?.data?.errors) {
      error.value = Object.values(e.response.data.errors).flat().join(' ')
    } else {
      error.value = e.response?.data?.message || 'Login failed'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style>
/* Global styles to prevent scrollbars */
html, body {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}
</style>

<style scoped>
/* Reset any default margins/padding that might cause overflow */
* {
  box-sizing: border-box;
}

.login-container {
  height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #0f766e 100%);
  margin: 0;
  padding: 0;
}

.login-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
  width: 100%;
  height: 100%;
}

.background-shapes {
  position: relative;
  width: 100%;
  height: 100%;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
  filter: blur(1px);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 300px;
  height: 300px;
  top: 5%;
  left: 5%;
  animation-delay: 0s;
}

.shape-2 {
  width: 200px;
  height: 200px;
  top: 55%;
  right: 5%;
  animation-delay: 2s;
}

.shape-3 {
  width: 250px;
  height: 250px;
  bottom: 15%;
  left: 55%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(10deg); }
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

.login-content {
  position: relative;
  z-index: 10;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  padding: 3rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
  width: 100%;
  max-width: 480px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  animation: slideUp 0.6s ease-out;
}

.login-card:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 32px 64px -12px rgba(0, 0, 0, 0.35),
    0 0 0 1px rgba(255, 255, 255, 0.15);
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.logo-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
}

.logo-icon:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
}

.logo-building {
  color: white;
}

.logo-text h1 {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #1e293b, #0f766e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-text p {
  margin: 0.25rem 0 0 0;
  font-size: 0.95rem;
  color: #64748b;
  font-weight: 500;
}

.login-form {
  width: 100%;
}

.form-header {
  text-align: center;
  margin-bottom: 2rem;
}

.form-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.875rem;
  font-weight: 700;
  color: #1e293b;
}

.form-header p {
  margin: 0;
  font-size: 1rem;
  color: #64748b;
  font-weight: 500;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #374151;
}

.label-icon {
  color: #6b7280;
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 1rem 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  background: #ffffff;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

.form-input:hover:not(:focus) {
  border-color: #d1d5db;
}

.form-input.input-error {
  border-color: #ef4444;
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.password-toggle {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.password-toggle:hover {
  color: #374151;
  background: rgba(0, 0, 0, 0.05);
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 12px;
  color: #dc2626;
  font-size: 0.9rem;
  font-weight: 500;
}

.error-icon {
  flex-shrink: 0;
}

.submit-button {
  width: 100%;
  padding: 1.25rem;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 2rem;
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
  background: linear-gradient(135deg, #2563eb, #0891b2);
}

.submit-button:active {
  transform: translateY(0);
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.button-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.loading-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.login-icon {
  transition: transform 0.2s ease;
}

.submit-button:hover:not(:disabled) .login-icon {
  transform: translateX(2px);
}

.form-footer {
  text-align: center;
}

.footer-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  padding: 0.75rem;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.footer-link:hover {
  color: #3b82f6;
  background: rgba(59, 130, 246, 0.05);
  transform: translateY(-1px);
}

/* Responsive Design */
@media (max-width: 640px) {
  .login-content {
    padding: 1rem;
  }
  
  .login-card {
    max-width: none;
    padding: 2rem;
    border-radius: 20px;
  }
  
  .logo-text h1 {
    font-size: 1.75rem;
  }
  
  .form-header h2 {
    font-size: 1.625rem;
  }
  
  .logo-icon {
    width: 64px;
    height: 64px;
  }
}

@media (max-width: 480px) {
  .login-card {
    padding: 1.5rem;
  }
  
  .logo-text h1 {
    font-size: 1.5rem;
  }
  
  .form-header h2 {
    font-size: 1.5rem;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .shape,
  .loading-icon,
  .login-card,
  .submit-button,
  .form-input,
  .footer-link {
    animation: none;
    transition: none;
  }
}

/* Focus visibility */
.submit-button:focus-visible,
.footer-link:focus-visible,
.password-toggle:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>