// API Service for making HTTP requests to backend services

const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api';

class ApiService {
  // Set up headers for API requests
  static getHeaders() {
    const token = localStorage.getItem('token');
    return {
      'Content-Type': 'application/json',
      'Authorization': token ? `Bearer ${token}` : ''
    };
  }

  // Generic fetch method
  static async fetch(url, options = {}) {
    const config = {
      headers: this.getHeaders(),
      ...options
    };

    try {
      const response = await fetch(`${API_BASE_URL}${url}`, config);
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'An error occurred');
      }
      
      return data;
    } catch (error) {
      throw new Error(error.message || 'An error occurred');
    }
  }

  // User Service methods
  static async register(userData) {
    return this.fetch('/users/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  }

  static async login(email, password) {
    return this.fetch('/users/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    });
  }

  static async getProfile() {
    return this.fetch('/users/profile');
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
}

export default ApiService;