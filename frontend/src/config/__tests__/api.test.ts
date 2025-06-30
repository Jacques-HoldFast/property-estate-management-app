import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createApiUrl, API_ENDPOINTS } from '../api'

// Mock environment variables
vi.mock('import.meta', () => ({
  env: {
    VITE_API_BASE_URL: 'https://test-api.example.com'
  }
}))

describe('API Configuration', () => {
  describe('createApiUrl', () => {
    it('should create URL with provided endpoint', () => {
      const result = createApiUrl('/api/test')
      expect(result).toContain('/api/test')
    })

    it('should handle endpoint without leading slash', () => {
      const result = createApiUrl('api/test')
      expect(result).toContain('api/test')
    })
  })

  describe('API_ENDPOINTS', () => {
    it('should have all required endpoints defined', () => {
      expect(API_ENDPOINTS.LOGIN).toBe('/login')
      expect(API_ENDPOINTS.LOGOUT).toBe('/logout')
      expect(API_ENDPOINTS.REGISTER).toBe('/register')
      expect(API_ENDPOINTS.DASHBOARD_STATS).toBe('/api/dashboard/stats')
      expect(API_ENDPOINTS.PROPERTIES).toBe('/api/properties')
      expect(API_ENDPOINTS.RESIDENTS).toBe('/api/residents')
      expect(API_ENDPOINTS.MAINTENANCE_REQUESTS).toBe('/api/maintenance-requests')
    })

    it('should generate correct dynamic endpoints', () => {
      expect(API_ENDPOINTS.PROPERTY_BY_ID(123)).toBe('/api/properties/123')
      expect(API_ENDPOINTS.RESIDENT_BY_ID(456)).toBe('/api/residents/456')
      expect(API_ENDPOINTS.MAINTENANCE_REQUEST_BY_ID(789)).toBe('/api/maintenance-requests/789')
    })
  })
})