<template>
  <div class="app-layout">
    <!-- Desktop Sidebar -->
    <div class="desktop-sidebar">
      <Sidebar />
    </div>
    
    <div class="main-content">
      <div class="mobile-header">
        <div class="mobile-logo">
          <div class="mobile-logo-icon">
            <Building2 :size="24" class="logo-building" />
          </div>
          <span class="logo-text">Property Pulse</span>
        </div>
        <button @click="toggleMobileMenu" class="mobile-menu-btn">
          <Menu :size="24" />
        </button>
      </div>
      
      <div class="content-wrapper">
        <slot />
      </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div 
      v-if="isMobileSidebarOpen" 
      class="mobile-sidebar-overlay"
      @click="closeMobileSidebar"
    >
      <div class="mobile-sidebar" @click.stop>
        <div class="mobile-sidebar-header">
          <div class="mobile-logo">
            <div class="mobile-logo-icon">
              <Building2 :size="24" class="logo-building" />
            </div>
            <span class="logo-text">Property Pulse</span>
          </div>
          <button @click="closeMobileSidebar" class="close-btn">
            <X :size="24" />
          </button>
        </div>

        <nav class="mobile-nav">
          <ul class="mobile-nav-list">
            <li class="mobile-nav-item">
              <RouterLink 
                to="/dashboard" 
                class="mobile-nav-link"
                :class="{ 'mobile-nav-link-active': $route.path === '/dashboard' }"
                @click="closeMobileSidebar"
              >
                <div class="nav-icon">
                  <BarChart3 :size="20" />
                </div>
                <span class="nav-text">Dashboard</span>
              </RouterLink>
            </li>
            
            <li class="mobile-nav-item">
              <RouterLink 
                to="/properties" 
                class="mobile-nav-link"
                :class="{ 'mobile-nav-link-active': $route.path === '/properties' }"
                @click="closeMobileSidebar"
              >
                <div class="nav-icon">
                  <Building2 :size="20" />
                </div>
                <span class="nav-text">Properties</span>
              </RouterLink>
            </li>
            
            <li class="mobile-nav-item">
              <RouterLink 
                to="/residents" 
                class="mobile-nav-link"
                :class="{ 'mobile-nav-link-active': $route.path === '/residents' }"
                @click="closeMobileSidebar"
              >
                <div class="nav-icon">
                  <Users :size="20" />
                </div>
                <span class="nav-text">Residents</span>
              </RouterLink>
            </li>
            
            <li class="mobile-nav-item">
              <RouterLink 
                to="/maintenance" 
                class="mobile-nav-link"
                :class="{ 'mobile-nav-link-active': $route.path === '/maintenance' }"
                @click="closeMobileSidebar"
              >
                <div class="nav-icon">
                  <Wrench :size="20" />
                </div>
                <span class="nav-text">Maintenance</span>
              </RouterLink>
            </li>
          </ul>
        </nav>

        <div class="mobile-sidebar-footer">
          <div class="mobile-user-info">
            <div class="user-avatar">
              <User :size="16" />
            </div>
            <div class="user-details">
              <div class="user-name">{{ user?.name || 'User' }}</div>
              <div class="user-email">{{ user?.email || '' }}</div>
            </div>
          </div>
          
          <button @click="handleLogout" class="mobile-logout-btn">
            <LogOut :size="16" />
            <span class="logout-text">Logout</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Sidebar from './Sidebar.vue'
import { createApiUrl } from '../config/api'
import axios from 'axios'
import Cookies from 'js-cookie'
import { 
  Building2, 
  Menu, 
  X, 
  BarChart3, 
  Users, 
  Wrench, 
  User, 
  LogOut 
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const isMobileSidebarOpen = ref(false)

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

const toggleMobileMenu = () => {
  isMobileSidebarOpen.value = !isMobileSidebarOpen.value
}

const closeMobileSidebar = () => {
  isMobileSidebarOpen.value = false
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
.app-layout {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #0f766e 100%);
  position: relative;
}

.desktop-sidebar {
  display: none;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.mobile-header {
  display: none;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  color: #1e293b;
  border-bottom: 1px solid rgba(226, 232, 240, 0.3);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.mobile-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.mobile-logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.logo-building {
  color: white;
}

.logo-text {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.mobile-menu-btn {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(226, 232, 240, 0.3);
  color: #64748b;
  cursor: pointer;
  padding: 0.75rem;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.mobile-menu-btn:hover {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  transform: scale(1.05);
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.content-wrapper {
  flex: 1;
  overflow: auto;
}

/* Mobile Sidebar Styles */
.mobile-sidebar-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 2000;
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

.mobile-sidebar {
  position: absolute;
  top: 0;
  left: 0;
  height: 100vh;
  width: 300px;
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(20px);
  color: white;
  display: flex;
  flex-direction: column;
  transform: translateX(0);
  animation: slideIn 0.3s ease-out;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border-right: 1px solid rgba(255, 255, 255, 0.1);
}

@keyframes slideIn {
  0% {
    transform: translateX(-100%);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

.mobile-sidebar-header {
  padding: 2rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  padding: 0.75rem;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  width: 2.5rem;
  height: 2.5rem;
}

.close-btn:hover {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  transform: scale(1.05);
  box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
}

.mobile-nav {
  flex: 1;
  padding: 2rem 0;
}

.mobile-nav-list {
  list-style: none;
  margin: 0;
  padding: 0 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mobile-nav-item {
  margin: 0;
}

.mobile-nav-link {
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

.mobile-nav-link::before {
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

.mobile-nav-link:hover {
  color: white;
  transform: translateX(4px);
}

.mobile-nav-link:hover::before {
  opacity: 1;
}

.mobile-nav-link-active {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2);
}

.mobile-nav-link-active .nav-icon {
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

.mobile-sidebar-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1.5rem;
}

.mobile-user-info {
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

.mobile-user-info:hover {
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
  font-weight: 500;
  color: #ecf0f1;
  font-size: 0.9rem;
  margin-bottom: 0.125rem;
}

.user-email {
  color: #bdc3c7;
  font-size: 0.8rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.mobile-logout-btn {
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

.mobile-logout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(239, 68, 68, 0.4);
  background: linear-gradient(135deg, #dc2626, #b91c1c);
}

/* Desktop Styles */
@media (min-width: 769px) {
  .desktop-sidebar {
    display: block;
  }
  
  .mobile-header {
    display: none !important;
  }
}

/* Mobile Styles */
@media (max-width: 768px) {
  .mobile-header {
    display: flex;
  }
  
  .mobile-sidebar-overlay {
    display: block;
  }
}
</style>