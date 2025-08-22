import React, { useContext } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthContext, AuthProvider } from './context/AuthContext';
import PatientDashboard from './pages/patient/Dashboard';
import DoctorDashboard from './pages/doctor/Dashboard';
import ReceptionistDashboard from './pages/receptionist/Dashboard';
import AdminDashboard from './pages/admin/Dashboard';
import Login from './components/user/Login';
import Register from './components/user/Register';
import './assets/styles/main.css';

// Protected route component
const ProtectedRoute = ({ children, allowedRoles }) => {
  const { user, loading } = useContext(AuthContext);
  
  if (loading) {
    return <div>Loading...</div>;
  }
  
  if (!user) {
    return <Navigate to="/login" />;
  }
  
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/" />;
  }
  
  return children;
};

// Main App component
const App = () => {
  return (
    <AuthProvider>
      <Router>
        <div className="App">
          <AppContent />
        </div>
      </Router>
    </AuthProvider>
  );
};

// App content component
const AppContent = () => {
  const { user, logout } = useContext(AuthContext);
  
  return (
    <>
      <header className="header">
        <div className="container header-content">
          <div className="header-logo">Healthcare Management System</div>
          {user && (
            <div className="header-user">
              <div className="user-avatar">{user.name.charAt(0)}</div>
              <span>{user.name}</span>
              <button className="btn btn-secondary" onClick={logout}>Logout</button>
            </div>
          )}
        </div>
      </header>
      
      <main className="main-content">
        <div className="container">
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/" element={
              user ? <Navigate to="/dashboard" /> : <Login />
            } />
            <Route path="/dashboard" element={
              <ProtectedRoute>
                {user.role === 'patient' && <PatientDashboard user={user} />}
                {user.role === 'doctor' && <DoctorDashboard user={user} />}
                {user.role === 'receptionist' && <ReceptionistDashboard user={user} />}
                {user.role === 'admin' && <AdminDashboard user={user} />}
              </ProtectedRoute>
            } />
            <Route path="/patient/dashboard" element={
              <ProtectedRoute allowedRoles={['patient']}>
                <PatientDashboard user={user} />
              </ProtectedRoute>
            } />
            <Route path="/doctor/dashboard" element={
              <ProtectedRoute allowedRoles={['doctor']}>
                <DoctorDashboard user={user} />
              </ProtectedRoute>
            } />
            <Route path="/receptionist/dashboard" element={
              <ProtectedRoute allowedRoles={['receptionist']}>
                <ReceptionistDashboard user={user} />
              </ProtectedRoute>
            } />
            <Route path="/admin/dashboard" element={
              <ProtectedRoute allowedRoles={['admin']}>
                <AdminDashboard user={user} />
              </ProtectedRoute>
            } />
          </Routes>
        </div>
      </main>
    </>
  );
};

export default App;