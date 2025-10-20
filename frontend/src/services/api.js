// API Service for making HTTP requests to backend services

// Use dynamic base URL detection to prevent hardcoded URL issues
const getApiBaseUrl = (service = 'main') => {
  // Always try environment variables first, regardless of service type
  if (process.env.REACT_APP_USER_SERVICE_BASE_URL && service === 'user') {
    return process.env.REACT_APP_USER_SERVICE_BASE_URL;
  }
  
  if (process.env.REACT_APP_API_BASE_URL && service === 'main') {
    return process.env.REACT_APP_API_BASE_URL;
  }
  
  if (process.env.REACT_APP_ADMIN_UI_BASE_URL && service === 'admin') {
    return process.env.REACT_APP_ADMIN_UI_BASE_URL;
  }
  
  // Use current origin for relative URLs only as a last resort
  // But ensure we're pointing to the correct API port (8000)
  if (typeof window !== 'undefined') {
    const currentOrigin = window.location.origin;
    // If we're running on localhost:3000 (frontend dev server), 
    // redirect API calls to localhost:8000 (API gateway)
    if (currentOrigin.includes('localhost:3000')) {
      if (service === 'user') {
        return 'http://localhost:8000/api/users';
      }
      // For admin endpoints, route through the API gateway
      if (service === 'admin') {
        return 'http://localhost:8000/api';
      }
      return 'http://localhost:8000/api';
    }
    
    // For other cases, use relative paths
    if (service === 'user') {
      return `${currentOrigin}/api/users`;
    }
    if (service === 'admin') {
      return `${currentOrigin}/api`;
    }
    return `${currentOrigin}/api`;
  }
  
  // Fallback to localhost
  if (service === 'user') {
    return 'http://localhost:8000/api/users';
  }
  if (service === 'admin') {
    return 'http://localhost:8000/api';
  }
  return 'http://localhost:8000/api';
};

const API_BASE_URL = getApiBaseUrl();
const USER_SERVICE_BASE_URL = getApiBaseUrl('user');
const ADMIN_UI_BASE_URL = getApiBaseUrl('admin');

console.log('API_BASE_URL:', API_BASE_URL);
console.log('USER_SERVICE_BASE_URL:', USER_SERVICE_BASE_URL);
console.log('ADMIN_UI_BASE_URL:', ADMIN_UI_BASE_URL);

class ApiService {
  // Set up headers for API requests
  static getHeaders(isAdmin = false) {
    // Use actual token from localStorage for authentication
    const token = localStorage.getItem('token');
    const authHeader = token ? `Bearer ${token}` : '';
    
    return {
      'Content-Type': 'application/json',
      'Authorization': authHeader
    };
  }

  // Generic fetch method - add baseUrl parameter
  static async fetch(url, options = {}, baseUrl = null) {
    // Determine if this is an admin endpoint
    const isAdminEndpoint = url.startsWith('/admin/');
    const config = {
      headers: this.getHeaders(isAdminEndpoint),
      ...options
    };

    // Use the appropriate base URL based on the endpoint type
    let baseURL = baseUrl || API_BASE_URL;
    if (isAdminEndpoint && !baseUrl) {
      baseURL = ADMIN_UI_BASE_URL;
    }

    // For admin endpoints, construct the full URL by combining the base URL with the endpoint path
    let fullUrl;
    if (isAdminEndpoint) {
      fullUrl = baseURL + url;
    } else {
      fullUrl = baseURL + url;
    }

    // Debug logging to see what URLs are being called
    console.log('ApiService.fetch - URL construction:');
    console.log('  url:', url);
    console.log('  isAdminEndpoint:', isAdminEndpoint);
    console.log('  baseURL:', baseURL);
    console.log('  fullUrl:', fullUrl);

    try {
      const response = await fetch(fullUrl, config);
      
      // Handle different HTTP status codes appropriately
      if (!response.ok) {
        let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
        
        try {
          // Try to parse error response
          const errorData = await response.json();
          if (errorData && errorData.message) {
            errorMessage = errorData.message;
          }
        } catch (parseError) {
          // If we can't parse JSON, check if it's HTML content
          const text = await response.text();
          if (text.startsWith('<')) {
            // It's HTML content, likely an error page
            errorMessage = 'Server error occurred. Please try again later.';
          } else {
            // Try to parse as JSON anyway
            try {
              const jsonData = JSON.parse(text);
              if (jsonData && jsonData.message) {
                errorMessage = jsonData.message;
              }
            } catch (jsonError) {
              errorMessage = text.substring(0, 100) + '...';
            }
          }
        }
        
        // Special handling for authentication errors
        if (response.status === 401) {
          errorMessage = 'Invalid credentials. Please check your email and password.';
        } else if (response.status === 404) {
          errorMessage = 'Service endpoint not found. Please check the server configuration.';
        } else if (response.status >= 500) {
          errorMessage = 'Server error occurred. Please try again later.';
        }
        
        throw new Error(errorMessage);
      }
      
      // Check if response is actually JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        // If not JSON, try to get text response
        const text = await response.text();
        // Check if it's HTML content
        if (text.startsWith('<')) {
          throw new Error('Server returned an HTML page instead of JSON. This usually indicates a server error.');
        }
        try {
          // Try to parse as JSON anyway (in case content-type is wrong)
          return JSON.parse(text);
        } catch (jsonError) {
          // If it's not valid JSON, return the text
          throw new Error(`Expected JSON response but got: ${text.substring(0, 100)}...`);
        }
      }
      
      // For JSON responses, try to parse
      try {
        const data = await response.json();
        return data;
      } catch (jsonError) {
        // Handle the case where response claims to be JSON but isn't valid
        const text = await response.text();
        // Check if it's HTML content
        if (text.startsWith('<')) {
          throw new Error('Server returned an HTML page instead of JSON. This usually indicates a server error.');
        }
        throw new Error(`Invalid JSON response: ${text.substring(0, 100)}...`);
      }
    } catch (error) {
      // Network error handling
      if (error instanceof TypeError && error.message.includes('Failed to fetch')) {
        console.error('Network error details:', {
          fullUrl,
          error: error.message,
          stack: error.stack
        });
        throw new Error('Network error - Could not connect to the server. Please check your connection and server status.');
      }
      
      // Re-throw other errors
      throw error;
    }
  }

  // User Service methods
  static async register(userData) {
    try {
      // Use user service base URL for registration
      return await this.fetch('/register', {
        method: 'POST',
        body: JSON.stringify(userData)
      }, USER_SERVICE_BASE_URL);
    } catch (error) {
      console.error('Registration error:', error);
      throw error;
    }
  }

  static async login(email, password) {
    try {
      // Use user service base URL for login
      const response = await this.fetch('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password })
      }, USER_SERVICE_BASE_URL);
      return response;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  }

  static async getProfile() {
    try {
      return await this.fetch('/me', {}, USER_SERVICE_BASE_URL);
    } catch (error) {
      console.error('Get profile error:', error);
      throw error;
    }
  }

  // User Role and Permission methods
  static async getUserRoles(userId) {
    try {
      return await this.fetch(`/admin/users/${userId}/roles`);
    } catch (error) {
      console.error('Get user roles error:', error);
      throw error;
    }
  }

  static async getRolePermissions(roleId) {
    try {
      return await this.fetch(`/admin/roles/${roleId}/permissions`);
    } catch (error) {
      console.error('Get role permissions error:', error);
      throw error;
    }
  }

  // Appointment Service methods
  static async getAvailableSlots(doctorId, date) {
    try {
      return await this.fetch(`/appointments/availability?doctorId=${doctorId}&date=${date}`);
    } catch (error) {
      console.error('Get available slots error:', error);
      throw error;
    }
  }

  static async bookAppointment(appointmentData) {
    try {
      return await this.fetch('/appointments', {
        method: 'POST',
        body: JSON.stringify(appointmentData)
      });
    } catch (error) {
      console.error('Book appointment error:', error);
      throw error;
    }
  }

  static async getUserAppointments(userId) {
    try {
      return await this.fetch(`/appointments/user/${userId}`);
    } catch (error) {
      console.error('Get user appointments error:', error);
      throw error;
    }
  }

  static async updateAppointmentStatus(appointmentId, status) {
    try {
      return await this.fetch(`/appointments/${appointmentId}/status`, {
        method: 'PUT',
        body: JSON.stringify({ status })
      });
    } catch (error) {
      console.error('Update appointment status error:', error);
      throw error;
    }
  }

  // Clinical Service methods
  static async getMedicalHistory(patientId) {
    try {
      return await this.fetch(`/clinical/history/${patientId}`);
    } catch (error) {
      console.error('Get medical history error:', error);
      throw error;
    }
  }

  static async getTreatmentPlans(patientId) {
    try {
      return await this.fetch(`/clinical/treatment-plans/${patientId}`);
    } catch (error) {
      console.error('Get treatment plans error:', error);
      throw error;
    }
  }

  // Notification Service methods
  static async getNotifications(userId) {
    try {
      return await this.fetch(`/notifications/user/${userId}`);
    } catch (error) {
      console.error('Get notifications error:', error);
      throw error;
    }
  }

  // Billing Service methods
  static async getInvoices(patientId) {
    try {
      return await this.fetch(`/billing/invoices/${patientId}`);
    } catch (error) {
      console.error('Get invoices error:', error);
      throw error;
    }
  }

  // Admin Service methods
  static async getUsers() {
    try {
      return await this.fetch('/admin/users');
    } catch (error) {
      console.error('Get users error:', error);
      throw error;
    }
  }

  static async createUser(userData) {
    try {
      return await this.fetch('/admin/users', {
        method: 'POST',
        body: JSON.stringify(userData)
      });
    } catch (error) {
      console.error('Create user error:', error);
      throw error;
    }
  }

  static async updateUser(userId, userData) {
    try {
      return await this.fetch(`/admin/users/${userId}`, {
        method: 'PUT',
        body: JSON.stringify(userData)
      });
    } catch (error) {
      console.error('Update user error:', error);
      throw error;
    }
  }

  static async deleteUser(userId) {
    try {
      return await this.fetch(`/admin/users/${userId}`, {
        method: 'DELETE'
      });
    } catch (error) {
      console.error('Delete user error:', error);
      throw error;
    }
  }

  // Dynamic RBAC Service methods
  static async getDynamicRoles() {
    try {
      return await this.fetch('/admin/roles');
    } catch (error) {
      console.error('Get dynamic roles error:', error);
      throw error;
    }
  }

  static async getAllPermissions() {
    try {
      return await this.fetch('/admin/permissions');
    } catch (error) {
      console.error('Get all permissions error:', error);
      throw error;
    }
  }

  static async createDynamicRole(roleData) {
    try {
      return await this.fetch('/admin/roles', {
        method: 'POST',
        body: JSON.stringify(roleData)
      });
    } catch (error) {
      console.error('Create dynamic role error:', error);
      throw error;
    }
  }

  static async updateDynamicRole(roleId, roleData) {
    try {
      return await this.fetch(`/admin/roles/${roleId}`, {
        method: 'PUT',
        body: JSON.stringify(roleData)
      });
    } catch (error) {
      console.error('Update dynamic role error:', error);
      throw error;
    }
  }

  static async deleteDynamicRole(roleId) {
    try {
      return await this.fetch(`/admin/roles/${roleId}`, {
        method: 'DELETE'
      });
    } catch (error) {
      console.error('Delete dynamic role error:', error);
      throw error;
    }
  }

  static async assignPermissionToRole(roleId, permissionId) {
    try {
      return await this.fetch(`/admin/roles/${roleId}/permissions`, {
        method: 'POST',
        body: JSON.stringify({ permission_id: permissionId })
      });
    } catch (error) {
      console.error('Assign permission to role error:', error);
      throw error;
    }
  }

  static async removePermissionFromRole(roleId, permissionId) {
    try {
      return await this.fetch(`/admin/roles/${roleId}/permissions/${permissionId}`, {
        method: 'DELETE'
      });
    } catch (error) {
      console.error('Remove permission from role error:', error);
      throw error;
    }
  }

  static async assignRoleToUser(userId, roleId) {
    try {
      return await this.fetch(`/admin/users/${userId}/roles`, {
        method: 'POST',
        body: JSON.stringify({ role_id: roleId })
      });
    } catch (error) {
      console.error('Assign role to user error:', error);
      throw error;
    }
  }

  static async removeRoleFromUser(userId, roleId) {
    try {
      return await this.fetch(`/admin/users/${userId}/roles/${roleId}`, {
        method: 'DELETE'
      });
    } catch (error) {
      console.error('Remove role from user error:', error);
      throw error;
    }
  }

  static async getFeatureModules() {
    try {
      return await this.fetch('/admin/modules');
    } catch (error) {
      console.error('Get feature modules error:', error);
      throw error;
    }
  }

  static async getRoleFeatureAccess(roleId) {
    try {
      return await this.fetch(`/admin/roles/${roleId}/features`);
    } catch (error) {
      console.error('Get role feature access error:', error);
      throw error;
    }
  }

  static async updateRoleFeatureAccess(roleId, moduleId, accessLevel) {
    try {
      return await this.fetch(`/admin/roles/${roleId}/features`, {
        method: 'POST',
        body: JSON.stringify({ module_id: moduleId, access_level: accessLevel })
      });
    } catch (error) {
      console.error('Update role feature access error:', error);
      throw error;
    }
  }

  // Role user count method
  static async getRoleUserCount(roleId) {
    try {
      return await this.fetch(`/admin/roles/${roleId}/user-count`);
    } catch (error) {
      console.error('Get role user count error:', error);
      throw error;
    }
  }

  // Escalation Management methods
  static async getEscalations(filters = {}) {
    try {
      const queryParams = new URLSearchParams(filters).toString();
      const url = `/admin/escalations${queryParams ? `?${queryParams}` : ''}`;
      return await this.fetch(url);
    } catch (error) {
      console.error('Get escalations error:', error);
      throw error;
    }
  }

  static async getEscalation(escalationId) {
    try {
      return await this.fetch(`/admin/escalations/${escalationId}`);
    } catch (error) {
      console.error('Get escalation error:', error);
      throw error;
    }
  }

  static async createEscalation(escalationData) {
    try {
      return await this.fetch('/admin/escalations', {
        method: 'POST',
        body: JSON.stringify(escalationData)
      });
    } catch (error) {
      console.error('Create escalation error:', error);
      throw error;
    }
  }

  static async updateEscalation(escalationId, escalationData) {
    try {
      return await this.fetch(`/admin/escalations/${escalationId}`, {
        method: 'PUT',
        body: JSON.stringify(escalationData)
      });
    } catch (error) {
      console.error('Update escalation error:', error);
      throw error;
    }
  }

  static async deleteEscalation(escalationId) {
    try {
      return await this.fetch(`/admin/escalations/${escalationId}`, {
        method: 'DELETE'
      });
    } catch (error) {
      console.error('Delete escalation error:', error);
      throw error;
    }
  }

  static async addEscalationComment(escalationId, commentData) {
    try {
      return await this.fetch(`/admin/escalations/${escalationId}/comments`, {
        method: 'POST',
        body: JSON.stringify(commentData)
      });
    } catch (error) {
      console.error('Add escalation comment error:', error);
      throw error;
    }
  }

  static async getEscalationCategories() {
    try {
      return await this.fetch('/admin/escalation-categories');
    } catch (error) {
      console.error('Get escalation categories error:', error);
      throw error;
    }
  }

  static async getEscalationStatuses() {
    try {
      return await this.fetch('/admin/escalation-statuses');
    } catch (error) {
      console.error('Get escalation statuses error:', error);
      throw error;
    }
  }

  // Medical Coordinator Service methods
  static async getPatientsForAssignment() {
    try {
      return await this.fetch('/medical-coordinator/patients');
    } catch (error) {
      console.error('Get patients for assignment error:', error);
      throw error;
    }
  }

  static async getDoctorsForAssignment() {
    try {
      return await this.fetch('/medical-coordinator/doctors');
    } catch (error) {
      console.error('Get doctors for assignment error:', error);
      throw error;
    }
  }

  static async assignPatientToDoctor(assignmentData) {
    try {
      return await this.fetch('/medical-coordinator/assignments', {
        method: 'POST',
        body: JSON.stringify(assignmentData)
      });
    } catch (error) {
      console.error('Assign patient to doctor error:', error);
      throw error;
    }
  }

  static async getPatientAssignmentHistory(patientId) {
    try {
      return await this.fetch(`/medical-coordinator/patients/${patientId}/assignments`);
    } catch (error) {
      console.error('Get patient assignment history error:', error);
      throw error;
    }
  }

  static async getPatientLimitedHistory(patientId) {
    try {
      return await this.fetch(`/medical-coordinator/patients/${patientId}/history`);
    } catch (error) {
      console.error('Get patient limited history error:', error);
      throw error;
    }
  }
}

export default ApiService;