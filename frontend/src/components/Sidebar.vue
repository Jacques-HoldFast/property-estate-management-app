<template>
  <div class="sidebar" :class="{ 'sidebar-open': isMobileMenuOpen }">
    <div class="sidebar-header">
      <div class="logo">
        <div class="logo-icon">
          <Building2 :size="28" class="logo-building" />
        </div>
        <div class="logo-text">
          <h1>Property Pulse</h1>
          <p>Professional Management</p>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <ul class="nav-list">
        <li class="nav-item">
          <RouterLink 
            to="/dashboard" 
            class="nav-link"
            :class="{ 'nav-link-active': $route.path === '/dashboard' }"
            @click="closeMobileMenu"
          >
            <div class="nav-icon">
              <BarChart3 :size="20" />
            </div>
            <span class="nav-text">Dashboard</span>
          </RouterLink>
        </li>
        
        <li class="nav-item">
          <RouterLink 
            to="/properties" 
            class="nav-link"
            :class="{ 'nav-link-active': $route.path === '/properties' }"
            @click="closeMobileMenu"
          >
            <div class="nav-icon">
              <Building2 :size="20" />
            </div>
            <span class="nav-text">Properties</span>
          </RouterLink>
        </li>
        
        <li class="nav-item">
          <RouterLink 
            to="/residents" 
            class="nav-link"
            :class="{ 'nav-link-active': $route.path === '/residents' }"
            @click="closeMobileMenu"
          >
            <div class="nav-icon">
              <Users :size="20" />
            </div>
            <span class="nav-text">Residents</span>
          </RouterLink>
        </li>
        
        <li class="nav-item">
          <RouterLink 
            to="/maintenance" 
            class="nav-link"
            :class="{ 'nav-link-active': $route.path === '/maintenance' }"
            @click="closeMobileMenu"
          >
            <div class="nav-icon">
              <Wrench :size="20" />
            </div>
            <span class="nav-text">Maintenance</span>
          </RouterLink>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar">
          <User :size="16" />
        </div>
        <div class="user-details">
          <div class="user-name">{{ user?.name || 'User' }}</div>
          <div class="user-email">{{ user?.email || '' }}</div>
        </div>
      </div>
      
      <button @click="handleLogout" class="logout-btn">
        <LogOut :size="16" />
        <span class="logout-text">Logout</span>
      </button>
    </div>
  </div>

</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { createApiUrl } from '../config/api'
import axios from 'axios'
import Cookies from 'js-cookie'
import { 
  Building2, 
  BarChart3, 
  Users, 
  Wrench, 
  User, 
  LogOut 
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const isMobileMenuOpen = ref(false)

const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map(name => name.charAt(0))
    .join('')
    .substring(0, 2)
    .toUpperCase()
})

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const handleLogout = async () => {
  try {
    await axios.post(createApiUrl('/logout'), {}, {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })
    authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
    authStore.logout()
    router.push('/login')
  }
}
</script>

<style scoped>
.sidebar {
  height: 100vh;
  width: 280px;
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(20px);
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  display: flex;
  flex-direction: column;
  z-index: 1000;
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
  position: relative;
}

.sidebar-header {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: center;
  align-items: center;
}

.logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  text-align: center;
}

.logo-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 16px;
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
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.logo-text p {
  margin: 0.25rem 0 0 0;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
}

.sidebar-nav {
  flex: 1;
  padding: 2rem 0;
}

.nav-list {
  list-style: none;
  margin: 0;
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  margin: 0;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.nav-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 12px;
}

.nav-link:hover {
  color: white;
  transform: translateX(4px);
}

.nav-link:hover::before {
  opacity: 1;
}

.nav-link-active {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2);
}

.nav-link-active .nav-icon {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.nav-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  position: relative;
  z-index: 1;
}

.nav-text {
  font-weight: 500;
  font-size: 0.95rem;
  position: relative;
  z-index: 1;
}

.sidebar-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.user-info:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
}

.user-avatar {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.user-details {
  min-width: 0;
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: white;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.user-email {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  font-weight: 600;
  box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3);
}

.logout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(239, 68, 68, 0.4);
  background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.logout-text {
  font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    max-width: 280px;
  }
}
</style>