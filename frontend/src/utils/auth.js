// Utility functions for authentication

// Get token from localStorage
export const getToken = () => {
  return localStorage.getItem('token');
};

// Set token in localStorage
export const setToken = (token) => {
  localStorage.setItem('token', token);
};

// Remove token from localStorage
export const removeToken = () => {
  localStorage.removeItem('token');
};

// Check if user is authenticated
export const isAuthenticated = () => {
  const token = getToken();
  return !!token;
};

// Get user role from token (simplified)
export const getUserRole = () => {
  // In a real app, you would decode the JWT token to get the user role
  // For now, we'll return a mock role
  const token = getToken();
  if (token) {
    return 'patient'; // Default to patient role
  }
  return null;
};

// Check if user has a specific role
export const hasRole = (role) => {
  const userRole = getUserRole();
  return userRole === role;
};

// Check if user has any of the specified roles
export const hasAnyRole = (roles) => {
  const userRole = getUserRole();
  return roles.includes(userRole);
};

// Redirect to login page
export const redirectToLogin = () => {
  window.location.href = '/login';
};

// Redirect to dashboard based on user role
export const redirectToDashboard = (role) => {
  switch (role) {
    case 'patient':
      window.location.href = '/dashboard';
      break;
    case 'doctor':
      window.location.href = '/dashboard';
      break;
    case 'receptionist':
      window.location.href = '/dashboard';
      break;
    case 'admin':
      window.location.href = '/dashboard';
      break;
    default:
      window.location.href = '/';
  }
};

// Logout user
export const logout = () => {
  removeToken();
  redirectToLogin();
};

// Validate password strength
export const validatePassword = (password) => {
  // Check if password is at least 8 characters long
  if (password.length < 8) {
    return false;
  }
  
  // Check if password contains at least one uppercase letter
  if (!/[A-Z]/.test(password)) {
    return false;
  }
  
  // Check if password contains at least one lowercase letter
  if (!/[a-z]/.test(password)) {
    return false;
  }
  
  // Check if password contains at least one digit
  if (!/\d/.test(password)) {
    return false;
  }
  
  // Check if password contains at least one special character
  if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    return false;
  }
  
  return true;
};

// Generate a random password
export const generatePassword = (length = 12) => {
  const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
  let password = "";
  
  for (let i = 0; i < length; i++) {
    const randomIndex = Math.floor(Math.random() * charset.length);
    password += charset[randomIndex];
  }
  
  return password;
};