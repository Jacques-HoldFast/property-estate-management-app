// API Configuration
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'

// Helper function to create full API URLs
export const createApiUrl = (endpoint: string): string => {
  // Remove leading slash if present to avoid double slashes
  const cleanEndpoint = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint
  return `${API_BASE_URL}/${cleanEndpoint}`
}

// Common API endpoints
export const API_ENDPOINTS = {
  // Authentication
  LOGIN: '/login',
  LOGOUT: '/logout',
  REGISTER: '/register',
  
  // Dashboard
  DASHBOARD_STATS: '/api/dashboard/stats',
  
  // Properties
  PROPERTIES: '/api/properties',
  PROPERTY_BY_ID: (id: number) => `/api/properties/${id}`,
  
  // Residents
  RESIDENTS: '/api/residents',
  RESIDENT_BY_ID: (id: number) => `/api/residents/${id}`,
  
  // Maintenance
  MAINTENANCE_REQUESTS: '/api/maintenance-requests',
  MAINTENANCE_REQUEST_BY_ID: (id: number) => `/api/maintenance-requests/${id}`,
} as const