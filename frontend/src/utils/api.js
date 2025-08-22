// Utility functions for API requests

const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api';

// Generic fetch function
const fetchApi = async (endpoint, options = {}) => {
  const url = `${API_BASE_URL}${endpoint}`;
  
  const config = {
    headers: {
      'Content-Type': 'application/json',
      ...options.headers
    },
    ...options
  };
  
  try {
    const response = await fetch(url, config);
    
    // Handle different response status codes
    if (response.status === 401) {
      // Unauthorized - redirect to login
      window.location.href = '/login';
      throw new Error('Unauthorized');
    }
    
    if (response.status === 403) {
      throw new Error('Forbidden');
    }
    
    if (response.status === 404) {
      throw new Error('Not Found');
    }
    
    if (response.status >= 500) {
      throw new Error('Server Error');
    }
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'An error occurred');
    }
    
    return data;
  } catch (error) {
    throw new Error(error.message || 'An error occurred');
  }
};

// GET request
export const get = async (endpoint, token = null) => {
  const options = {};
  
  if (token) {
    options.headers = {
      'Authorization': `Bearer ${token}`
    };
  }
  
  return fetchApi(endpoint, options);
};

// POST request
export const post = async (endpoint, data, token = null) => {
  const options = {
    method: 'POST',
    body: JSON.stringify(data)
  };
  
  if (token) {
    options.headers = {
      'Authorization': `Bearer ${token}`
    };
  }
  
  return fetchApi(endpoint, options);
};

// PUT request
export const put = async (endpoint, data, token = null) => {
  const options = {
    method: 'PUT',
    body: JSON.stringify(data)
  };
  
  if (token) {
    options.headers = {
      'Authorization': `Bearer ${token}`
    };
  }
  
  return fetchApi(endpoint, options);
};

// DELETE request
export const del = async (endpoint, token = null) => {
  const options = {
    method: 'DELETE'
  };
  
  if (token) {
    options.headers = {
      'Authorization': `Bearer ${token}`
    };
  }
  
  return fetchApi(endpoint, options);
};

// Upload file
export const uploadFile = async (endpoint, file, token = null) => {
  const formData = new FormData();
  formData.append('file', file);
  
  const options = {
    method: 'POST',
    body: formData,
    headers: {}
  };
  
  if (token) {
    options.headers['Authorization'] = `Bearer ${token}`;
  }
  
  return fetchApi(endpoint, options);
};

// Handle API errors
export const handleApiError = (error) => {
  console.error('API Error:', error);
  
  // You can add more specific error handling here
  // For example, showing error messages to the user
  
  return error.message;
};

// Format API error message
export const formatApiError = (error) => {
  if (error.message) {
    return error.message;
  }
  
  if (typeof error === 'string') {
    return error;
  }
  
  return 'An unknown error occurred';
};