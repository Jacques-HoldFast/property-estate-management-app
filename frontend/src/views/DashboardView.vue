<template>
  <AppLayout>
    <div class="dashboard">
      <header class="dashboard-header">
        <div class="header-content">
          <div class="header-icon">
            <BarChart3 :size="40" class="chart-icon" />
          </div>
          <div class="header-text">
            <h1>Property Management Dashboard</h1>
            <p class="dashboard-subtitle">Overview of your properties and residents</p>
          </div>
        </div>
      </header>

      <div v-if="loading" class="loading">
        <Loader2 :size="48" class="loading-spinner" />
        <p>Loading dashboard statistics...</p>
      </div>

      <div v-else-if="error" class="error">
        <AlertCircle :size="24" class="error-icon" />
        <p>{{ error }}</p>
        <button @click="fetchStats" class="retry-btn">
          <RefreshCw :size="16" />
          <span>Retry</span>
        </button>
      </div>

      <main v-else class="dashboard-content">
        <!-- Summary Cards -->
        <div class="summary-cards">
          <div class="summary-card properties-summary">
            <div class="card-icon">
              <Building2 :size="32" />
            </div>
            <div class="card-content">
              <h3>Total Properties</h3>
              <div class="card-number">{{ stats.properties?.total || 0 }}</div>
              <RouterLink to="/properties" class="card-link">
                <span>View All Properties</span>
                <ArrowRight :size="16" />
              </RouterLink>
            </div>
          </div>

          <div class="summary-card residents-summary">
            <div class="card-icon">
              <Users :size="32" />
            </div>
            <div class="card-content">
              <h3>Total Residents</h3>
              <div class="card-number">{{ stats.residents?.total || 0 }}</div>
              <RouterLink to="/residents" class="card-link">
                <span>View All Residents</span>
                <ArrowRight :size="16" />
              </RouterLink>
            </div>
          </div>

          <div class="summary-card maintenance-summary">
            <div class="card-icon">
              <Wrench :size="32" />
            </div>
            <div class="card-content">
              <h3>Total Maintenance</h3>
              <div class="card-number">{{ stats.maintenance?.total || 0 }}</div>
              <RouterLink to="/maintenance" class="card-link">
                <span>View All Requests</span>
                <ArrowRight :size="16" />
              </RouterLink>
            </div>
          </div>

          <div class="summary-card urgent-summary" v-if="stats.maintenance?.urgent_open > 0">
            <div class="card-icon">
              <AlertTriangle :size="32" />
            </div>
            <div class="card-content">
              <h3>Urgent Requests</h3>
              <div class="card-number">{{ stats.maintenance?.urgent_open || 0 }}</div>
              <RouterLink to="/maintenance" class="card-link">
                <span>View Urgent</span>
                <ArrowRight :size="16" />
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="stats-grid">
          <!-- Property Statistics -->
          <div class="stats-card">
            <div class="stats-header">
              <h3>Property Status Breakdown</h3>
              <div class="stats-icon">
                <PieChart :size="20" />
              </div>
            </div>
            <div class="stats-content">
              <div v-if="Object.keys(stats.properties?.by_status || {}).length === 0" class="no-data">
                No properties found
              </div>
              <div v-else class="status-list">
                <div 
                  v-for="(count, status) in stats.properties?.by_status" 
                  :key="status"
                  class="status-item"
                >
                  <div class="status-info">
                    <span class="status-name">{{ formatStatus(status) }}</span>
                    <span class="status-count">{{ count }}</span>
                  </div>
                  <div class="status-bar">
                    <div 
                      class="status-fill"
                      :class="`fill-${status}`"
                      :style="{ width: `${(count / stats.properties.total) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Resident Statistics -->
          <div class="stats-card">
            <div class="stats-header">
              <h3>Resident Status Breakdown</h3>
              <div class="stats-icon">
                <TrendingUp :size="20" />
              </div>
            </div>
            <div class="stats-content">
              <div v-if="Object.keys(stats.residents?.by_status || {}).length === 0" class="no-data">
                No residents found
              </div>
              <div v-else class="status-list">
                <div 
                  v-for="(count, status) in stats.residents?.by_status" 
                  :key="status"
                  class="status-item"
                >
                  <div class="status-info">
                    <span class="status-name">{{ formatStatus(status) }}</span>
                    <span class="status-count">{{ count }}</span>
                  </div>
                  <div class="status-bar">
                    <div 
                      class="status-fill"
                      :class="`fill-${status}`"
                      :style="{ width: `${(count / stats.residents.total) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Maintenance Statistics -->
          <div class="stats-card">
            <div class="stats-header">
              <h3>Maintenance by Status</h3>
              <div class="stats-icon">
                <Settings :size="20" />
              </div>
            </div>
            <div class="stats-content">
              <div v-if="Object.keys(stats.maintenance?.by_status || {}).length === 0" class="no-data">
                No maintenance requests found
              </div>
              <div v-else class="status-list">
                <div 
                  v-for="(count, status) in stats.maintenance?.by_status" 
                  :key="status"
                  class="status-item"
                >
                  <div class="status-info">
                    <span class="status-name">{{ formatStatus(status) }}</span>
                    <span class="status-count">{{ count }}</span>
                  </div>
                  <div class="status-bar">
                    <div 
                      class="status-fill"
                      :class="`fill-maintenance-${status}`"
                      :style="{ width: `${(count / stats.maintenance.total) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="stats-card">
            <div class="stats-header">
              <h3>Maintenance by Priority</h3>
              <div class="stats-icon">
                <Zap :size="20" />
              </div>
            </div>
            <div class="stats-content">
              <div v-if="Object.keys(stats.maintenance?.by_priority || {}).length === 0" class="no-data">
                No maintenance requests found
              </div>
              <div v-else class="status-list">
                <div 
                  v-for="(count, priority) in stats.maintenance?.by_priority" 
                  :key="priority"
                  class="status-item"
                >
                  <div class="status-info">
                    <span class="status-name">{{ formatStatus(priority) }}</span>
                    <span class="status-count">{{ count }}</span>
                  </div>
                  <div class="status-bar">
                    <div 
                      class="status-fill"
                      :class="`fill-priority-${priority}`"
                      :style="{ width: `${(count / stats.maintenance.total) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="stats-card full-width">
            <div class="stats-header">
              <h3>Recent Activity</h3>
              <div class="stats-icon">
                <Clock :size="20" />
              </div>
            </div>
            <div class="stats-content">
              <div class="activity-grid">
                <div class="activity-section">
                  <h4>Recent Residents</h4>
                  <div v-if="stats.residents?.recent?.length === 0" class="no-data">
                    No recent residents
                  </div>
                  <div v-else class="activity-list">
                    <div 
                      v-for="resident in stats.residents?.recent" 
                      :key="resident.id"
                      class="activity-item"
                    >
                      <div class="activity-info">
                        <span class="activity-name">{{ resident.full_name }}</span>
                        <span class="activity-detail">{{ resident.property?.title || 'Unknown Property' }}</span>
                      </div>
                      <span class="activity-date">{{ formatDate(resident.created_at) }}</span>
                    </div>
                  </div>
                </div>

                <div class="activity-section">
                  <h4>Recent Properties</h4>
                  <div v-if="stats.properties?.recent?.length === 0" class="no-data">
                    No recent properties
                  </div>
                  <div v-else class="activity-list">
                    <div 
                      v-for="property in stats.properties?.recent" 
                      :key="property.id"
                      class="activity-item"
                    >
                      <div class="activity-info">
                        <span class="activity-name">{{ property.title }}</span>
                        <span class="activity-detail">{{ property.city }}, {{ property.province }}</span>
                      </div>
                      <span class="activity-date">{{ formatDate(property.created_at) }}</span>
                    </div>
                  </div>
                </div>

                <div class="activity-section">
                  <h4>Recent Maintenance</h4>
                  <div v-if="stats.maintenance?.recent?.length === 0" class="no-data">
                    No recent maintenance requests
                  </div>
                  <div v-else class="activity-list">
                    <div 
                      v-for="request in stats.maintenance?.recent" 
                      :key="request.id"
                      class="activity-item"
                    >
                      <div class="activity-info">
                        <span class="activity-name">{{ request.title }}</span>
                        <span class="activity-detail">{{ request.property?.title }} - {{ request.resident?.full_name }}</span>
                      </div>
                      <div class="maintenance-badges">
                        <span :class="`badge-${request.status}`" class="activity-badge">{{ formatStatus(request.status) }}</span>
                        <span :class="`badge-${request.priority}`" class="activity-badge">{{ formatStatus(request.priority) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </AppLayout>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import axios from 'axios'
import Cookies from 'js-cookie'
import AppLayout from '../components/AppLayout.vue'
import { createApiUrl } from '../config/api'
import { 
  BarChart3,
  Building2, 
  Users, 
  Wrench, 
  AlertTriangle, 
  ArrowRight,
  PieChart,
  TrendingUp,
  Settings,
  Zap,
  Clock,
  Loader2,
  AlertCircle,
  RefreshCw
} from 'lucide-vue-next'

interface DashboardStats {
  properties: {
    total: number
    by_status: Record<string, number>
    recent: Array<{
      id: number
      title: string
      city: string
      province: string
      created_at: string
    }>
  }
  residents: {
    total: number
    by_status: Record<string, number>
    recent: Array<{
      id: number
      full_name: string
      created_at: string
      property?: {
        title: string
      }
    }>
  }
  maintenance: {
    total: number
    by_status: Record<string, number>
    by_priority: Record<string, number>
    urgent_open: number
    recent: Array<{
      id: number
      title: string
      status: string
      priority: string
      reported_at: string
      property?: {
        title: string
      }
      resident?: {
        full_name: string
      }
    }>
  }
}

const loading = ref(true)
const error = ref('')
const stats = ref<DashboardStats>({
  properties: { total: 0, by_status: {}, recent: [] },
  residents: { total: 0, by_status: {}, recent: [] },
  maintenance: { total: 0, by_status: {}, by_priority: {}, urgent_open: 0, recent: [] }
})

const fetchStats = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const response = await axios.get(createApiUrl('/api/dashboard/stats'), {
      withCredentials: true,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(Cookies.get('XSRF-TOKEN') || ''),
      },
    })
    
    stats.value = response.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load dashboard statistics'
    console.error('Error fetching dashboard stats:', err)
  } finally {
    loading.value = false
  }
}

const formatStatus = (status: string): string => {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-ZA', {
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchStats()
})
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  padding: 2rem;
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  0% { 
    opacity: 0;
    transform: translateY(20px);
  }
  100% { 
    opacity: 1;
    transform: translateY(0);
  }
}

.dashboard-header {
  margin-bottom: 3rem;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  text-align: center;
}

.header-icon {
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

.header-icon:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
}

.chart-icon {
  color: white;
}

.header-text h1 {
  margin: 0 0 0.5rem 0;
  color: white;
  font-size: 2.5rem;
  font-weight: 700;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.dashboard-subtitle {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.1rem;
  margin: 0;
  font-weight: 500;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: rgba(255, 255, 255, 0.8);
}

.loading-spinner {
  color: white;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error {
  text-align: center;
  padding: 2rem;
  color: #dc2626;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
  margin: 0 auto;
  max-width: 500px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.error-icon {
  color: #dc2626;
}

.retry-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.retry-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
  background: linear-gradient(135deg, #2563eb, #0891b2);
}

.dashboard-content {
  max-width: 1200px;
  margin: 0 auto;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.summary-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  animation: slideUp 0.6s ease-out;
}

.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 
    0 32px 64px -12px rgba(0, 0, 0, 0.35),
    0 0 0 1px rgba(255, 255, 255, 0.15);
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

.properties-summary {
  border-left: 4px solid #3498db;
}

.residents-summary {
  border-left: 4px solid #2ecc71;
}

.maintenance-summary {
  border-left: 4px solid #f39c12;
}

.urgent-summary {
  border-left: 4px solid #e74c3c;
}

.card-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.properties-summary .card-icon {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
}

.residents-summary .card-icon {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
}

.maintenance-summary .card-icon {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
}

.urgent-summary .card-icon {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3);
}

.summary-card:hover .card-icon {
  transform: scale(1.1);
}

.card-content {
  flex: 1;
}

.card-content h3 {
  margin: 0 0 0.5rem 0;
  color: #495057;
  font-size: 1rem;
  font-weight: 500;
}

.card-number {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.card-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 500;
  transition: all 0.2s ease;
  padding: 0.5rem 0.5rem;
  border-radius: 8px;
}

.card-link:hover {
  color: #2563eb;
  transform: translateX(4px);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.stats-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
  transition: all 0.3s ease;
  animation: slideUp 0.6s ease-out;
}

.stats-card:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 32px 64px -12px rgba(0, 0, 0, 0.35),
    0 0 0 1px rgba(255, 255, 255, 0.15);
}

.stats-card.full-width {
  grid-column: 1 / -1;
}

.stats-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(6, 182, 212, 0.05));
  border-bottom: 1px solid rgba(59, 130, 246, 0.1);
}

.stats-header h3 {
  margin: 0;
  color: #1e293b;
  font-size: 1.1rem;
  font-weight: 600;
}

.stats-icon {
  color: #3b82f6;
  opacity: 0.8;
}

.stats-content {
  padding: 2rem;
}

.no-data {
  text-align: center;
  color: #6c757d;
  font-style: italic;
  padding: 2rem;
}

.status-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.status-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.status-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.status-name {
  color: #495057;
  font-weight: 500;
}

.status-count {
  color: #2c3e50;
  font-weight: 600;
  font-size: 1.1rem;
}

.status-bar {
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.status-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.fill-available, .fill-active {
  background: linear-gradient(45deg, #2ecc71, #27ae60);
}

.fill-rented, .fill-inactive {
  background: linear-gradient(45deg, #f39c12, #e67e22);
}

.fill-maintenance, .fill-pending {
  background: linear-gradient(45deg, #e74c3c, #c0392b);
}

.fill-sold, .fill-terminated {
  background: linear-gradient(45deg, #95a5a6, #7f8c8d);
}

/* Maintenance status colors */
.fill-maintenance-pending {
  background: linear-gradient(45deg, #f39c12, #e67e22);
}

.fill-maintenance-assigned {
  background: linear-gradient(45deg, #3498db, #2980b9);
}

.fill-maintenance-in_progress {
  background: linear-gradient(45deg, #9b59b6, #8e44ad);
}

.fill-maintenance-on_hold {
  background: linear-gradient(45deg, #e67e22, #d35400);
}

.fill-maintenance-completed {
  background: linear-gradient(45deg, #2ecc71, #27ae60);
}

.fill-maintenance-cancelled {
  background: linear-gradient(45deg, #95a5a6, #7f8c8d);
}

/* Priority colors */
.fill-priority-low {
  background: linear-gradient(45deg, #2ecc71, #27ae60);
}

.fill-priority-medium {
  background: linear-gradient(45deg, #f39c12, #e67e22);
}

.fill-priority-high {
  background: linear-gradient(45deg, #e67e22, #d35400);
}

.fill-priority-urgent {
  background: linear-gradient(45deg, #e74c3c, #c0392b);
}

.activity-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.activity-section h4 {
  margin: 0 0 1rem 0;
  color: #495057;
  font-size: 1rem;
  font-weight: 600;
  border-bottom: 2px solid #3498db;
  padding-bottom: 0.5rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.activity-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
  border-left: 3px solid #3498db;
}

.activity-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
  flex: 1;
}

.activity-name {
  color: #2c3e50;
  font-weight: 500;
  font-size: 0.9rem;
}

.activity-detail {
  color: #6c757d;
  font-size: 0.8rem;
  margin-top: 0.125rem;
}

.activity-date {
  color: #6c757d;
  font-size: 0.8rem;
  white-space: nowrap;
  margin-left: 1rem;
}

.maintenance-badges {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  align-items: flex-end;
}

.activity-badge {
  padding: 0.2rem 0.5rem;
  border-radius: 10px;
  font-size: 0.7rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

.badge-pending { background: #fff3cd; color: #856404; }
.badge-assigned { background: #cce5ff; color: #004085; }
.badge-in_progress { background: #e6ccff; color: #5a1a7a; }
.badge-on_hold { background: #ffe6cc; color: #cc6600; }
.badge-completed { background: #d4edda; color: #155724; }
.badge-cancelled { background: #f8d7da; color: #721c24; }

.badge-low { background: #d4edda; color: #155724; }
.badge-medium { background: #fff3cd; color: #856404; }
.badge-high { background: #ffe6cc; color: #cc6600; }
.badge-urgent { background: #f8d7da; color: #721c24; }

@media (max-width: 768px) {
  .dashboard {
    padding: 1rem;
  }
  
  .dashboard-header h1 {
    font-size: 2rem;
  }
  
  .summary-cards {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .summary-card {
    padding: 1.5rem;
  }
  
  .stats-content {
    padding: 1.5rem;
  }
  
  .activity-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .activity-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .activity-date {
    margin-left: 0;
  }
}
</style>