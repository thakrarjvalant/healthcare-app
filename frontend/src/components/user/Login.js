import React, { useState, useContext } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';
import './User.css';

const Login = () => {
  const { login } = useContext(AuthContext);
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleClearCache = () => {
    localStorage.clear();
    window.location.reload();
  };

  // Clean user data to remove indexed properties
  const cleanUserData = (userData) => {
    if (!userData) return null;
    
    // Create a clean object with only the named properties
    const cleanUser = {
      id: userData.id,
      name: userData.name,
      email: userData.email,
      role: userData.role,
      verified: userData.verified,
      created_at: userData.created_at,
      updated_at: userData.updated_at
    };
    
    // Remove undefined properties
    Object.keys(cleanUser).forEach(key => {
      if (cleanUser[key] === undefined) {
        delete cleanUser[key];
      }
    });
    
    return cleanUser;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    
    // Basic validation
    if (!email || !password) {
      setError('Please fill in all fields');
      setLoading(false);
      return;
    }
    
    try {
      const response = await ApiService.login(email, password);
      
      // Check if response has the expected structure
      if (response && response.data) {
        // Successful login response
        if (response.data.user && response.data.token) {
          // Clean the user data to remove indexed properties
          const cleanUser = cleanUserData(response.data.user);
          
          // Use await to ensure login completes before navigation
          await login(cleanUser, response.data.token);
          
          // Navigate to dashboard after successful login
          navigate('/dashboard', { replace: true });
        } else if (response.data.message) {
          // Handle API response with error message
          setError(response.data.message);
          setLoading(false);
        }
      } else if (response && response.user) {
        // Handle older response format
        // Clean the user data to remove indexed properties
        const cleanUser = cleanUserData(response.user);
        
        // Use the actual token from the API response
        const token = response.token || 'mock-jwt-token-' + Date.now();
        
        // Use await to ensure login completes before navigation
        await login(cleanUser, token);
        
        // Navigate to dashboard after successful login
        navigate('/dashboard', { replace: true });
      } else {
        setError('Invalid response from server');
        setLoading(false);
      }
    } catch (err) {
      setLoading(false);
      // More specific error handling
      if (err.message.includes('Network error')) {
        setError('Could not connect to the server. Please check your internet connection and try again.');
      } else if (err.message.includes('Service endpoint not found')) {
        setError('Login service is currently unavailable. Please try again later.');
      } else if (err.message.includes('Server error')) {
        setError('Server error occurred. Please try again later.');
      } else if (err.message.includes('Failed to fetch')) {
        setError('Could not connect to the server. Please check your internet connection and try again.');
      } else {
        setError(err.message || 'Login failed. Please check your credentials and try again.');
      }
    }
  };

  return (
    <div className="form-container">
      <h2>Login</h2>
      {error && <div className="error-message">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="email">Email:</label>
          <input
            type="email"
            id="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div className="form-group">
          <label htmlFor="password">Password:</label>
          <input
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            autoComplete="current-password"
          />
        </div>
        <div className="form-group">
          <button type="submit" disabled={loading}>
            {loading ? 'Logging in...' : 'Login'}
          </button>
        </div>
      </form>
      <div className="form-footer">
        <button type="button" className="btn btn-secondary" onClick={handleClearCache} style={{marginBottom: '10px'}}>Clear Cache & Reload</button>
        <p>Don't have an account? <Link to="/register">Register</Link></p>
      </div>
    </div>
  );
};

export default Login;