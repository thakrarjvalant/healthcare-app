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
    // Use user service base URL for registration
    return this.fetch('/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    }, USER_SERVICE_BASE_URL);
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
      throw error;
    }
  }

  static async getProfile() {
    return this.fetch('/profile', {}, USER_SERVICE_BASE_URL);
  }

  // User Role and Permission methods
  static async getUserRoles(userId) {
    return this.fetch(`/admin/users/${userId}/roles`);
  }

  static async getRolePermissions(roleId) {
    return this.fetch(`/admin/roles/${roleId}/permissions`);
  }

  // Appointment Service methods
  static async getAvailableSlots(doctorId, date) {
    return this.fetch(`/appointments/availability?doctorId=${doctorId}&date=${date}`);
  }

  static async bookAppointment(appointmentData) {
    return this.fetch('/appointments', {
      method: 'POST',
      body: JSON.stringify(appointmentData)
    });
  }

  static async getUserAppointments(userId) {
    return this.fetch(`/appointments/user/${userId}`);
  }

  static async updateAppointmentStatus(appointmentId, status) {
    return this.fetch(`/appointments/${appointmentId}/status`, {
      method: 'PUT',
      body: JSON.stringify({ status })
    });
  }

  // Clinical Service methods
  static async getMedicalHistory(patientId) {
    return this.fetch(`/clinical/history/${patientId}`);
  }

  static async getTreatmentPlans(patientId) {
    return this.fetch(`/clinical/treatment-plans/${patientId}`);
  }

  // Notification Service methods
  static async getNotifications(userId) {
    return this.fetch(`/notifications/user/${userId}`);
  }

  // Billing Service methods
  static async getInvoices(patientId) {
    return this.fetch(`/billing/invoices/${patientId}`);
  }

  // Admin Service methods
  static async getUsers() {
    return this.fetch('/admin/users');
  }

  static async createUser(userData) {
    return this.fetch('/admin/users', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  }

  static async updateUser(userId, userData) {
    return this.fetch(`/admin/users/${userId}`, {
      method: 'PUT',
      body: JSON.stringify(userData)
    });
  }

  static async deleteUser(userId) {
    return this.fetch(`/admin/users/${userId}`, {
      method: 'DELETE'
    });
  }

  // Dynamic RBAC Service methods
  static async getDynamicRoles() {
    return this.fetch('/admin/roles');
  }

  static async getAllPermissions() {
    return this.fetch('/admin/permissions');
  }

  static async createDynamicRole(roleData) {
    return this.fetch('/admin/roles', {
      method: 'POST',
      body: JSON.stringify(roleData)
    });
  }

  static async updateDynamicRole(roleId, roleData) {
    return this.fetch(`/admin/roles/${roleId}`, {
      method: 'PUT',
      body: JSON.stringify(roleData)
    });
  }

  static async deleteDynamicRole(roleId) {
    return this.fetch(`/admin/roles/${roleId}`, {
      method: 'DELETE'
    });
  }

  static async assignPermissionToRole(roleId, permissionId) {
    return this.fetch(`/admin/roles/${roleId}/permissions`, {
      method: 'POST',
      body: JSON.stringify({ permission_id: permissionId })
    });
  }

  static async removePermissionFromRole(roleId, permissionId) {
    return this.fetch(`/admin/roles/${roleId}/permissions/${permissionId}`, {
      method: 'DELETE'
    });
  }

  static async assignRoleToUser(userId, roleId) {
    return this.fetch(`/admin/users/${userId}/roles`, {
      method: 'POST',
      body: JSON.stringify({ role_id: roleId })
    });
  }

  static async removeRoleFromUser(userId, roleId) {
    return this.fetch(`/admin/users/${userId}/roles/${roleId}`, {
      method: 'DELETE'
    });
  }

  static async getFeatureModules() {
    return this.fetch('/admin/modules');
  }

  static async getRoleFeatureAccess(roleId) {
    return this.fetch(`/admin/roles/${roleId}/features`);
  }

  static async updateRoleFeatureAccess(roleId, moduleId, accessLevel) {
    return this.fetch(`/admin/roles/${roleId}/features`, {
      method: 'POST',
      body: JSON.stringify({ module_id: moduleId, access_level: accessLevel })
    });
  }

  // Role user count method
  static async getRoleUserCount(roleId) {
    return this.fetch(`/admin/roles/${roleId}/user-count`);
  }

  // Escalation Management methods
  static async getEscalations(filters = {}) {
    const queryParams = new URLSearchParams(filters).toString();
    const url = `/admin/escalations${queryParams ? `?${queryParams}` : ''}`;
    return this.fetch(url);
  }

  static async getEscalation(escalationId) {
    return this.fetch(`/admin/escalations/${escalationId}`);
  }

  static async createEscalation(escalationData) {
    return this.fetch('/admin/escalations', {
      method: 'POST',
      body: JSON.stringify(escalationData)
    });
  }

  static async updateEscalation(escalationId, escalationData) {
    return this.fetch(`/admin/escalations/${escalationId}`, {
      method: 'PUT',
      body: JSON.stringify(escalationData)
    });
  }

  static async deleteEscalation(escalationId) {
    return this.fetch(`/admin/escalations/${escalationId}`, {
      method: 'DELETE'
    });
  }

  static async addEscalationComment(escalationId, commentData) {
    return this.fetch(`/admin/escalations/${escalationId}/comments`, {
      method: 'POST',
      body: JSON.stringify(commentData)
    });
  }

  static async getEscalationCategories() {
    return this.fetch('/admin/escalation-categories');
  }

  static async getEscalationStatuses() {
    return this.fetch('/admin/escalation-statuses');
  }

  // Medical Coordinator Service methods
  static async getPatientsForAssignment() {
    return this.fetch('/medical-coordinator/patients');
  }

  static async getDoctorsForAssignment() {
    return this.fetch('/medical-coordinator/doctors');
  }

  static async assignPatientToDoctor(assignmentData) {
    return this.fetch('/medical-coordinator/assignments', {
      method: 'POST',
      body: JSON.stringify(assignmentData)
    });
  }

  static async getPatientAssignmentHistory(patientId) {
    return this.fetch(`/medical-coordinator/patients/${patientId}/assignments`);
  }

  static async getPatientLimitedHistory(patientId) {
    return this.fetch(`/medical-coordinator/patients/${patientId}/history`);
  }
}

export default ApiService;