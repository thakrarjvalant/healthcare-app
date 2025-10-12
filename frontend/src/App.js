import React, { useContext } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthContext, AuthProvider } from './context/AuthContext';
import {
  NotificationProvider,
  NotificationBell,
  ToastContainer,
  SessionWarningModal
} from './components/common';
import ErrorBoundary from './components/common/ErrorBoundary';
import NetworkStatus from './components/common/NetworkStatus';
import PatientDashboard from './pages/patient/Dashboard';
import DoctorDashboard from './pages/doctor/Dashboard';
import ReceptionistDashboard from './pages/receptionist/Dashboard';
import AdminDashboard from './pages/admin/Dashboard';
import MedicalCoordinatorDashboard from './pages/medical-coordinator/Dashboard';
import SuperAdminDashboard from './pages/superadmin/Dashboard';
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
    return <Navigate to="/login" replace />;
  }
  
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <Navigate to="/" replace />;
  }
  
  return children;
};

// Main App component
const App = () => {
	return (
		<AuthProvider>
			<NotificationProvider>
				<ErrorBoundary>
					<Router>
						<div className="App">
							<NetworkStatus />
							<AppContent />
							<SessionWarningModal />
							<ToastContainer />
						</div>
					</Router>
				</ErrorBoundary>
			</NotificationProvider>
		</AuthProvider>
	);
};

// App content component
const AppContent = () => {
  const { user, logout, sessionExpired, loading } = useContext(AuthContext);
  
  // Handle session expiration
  if (sessionExpired) {
    return (
      <div className="session-expired">
        <div className="session-expired-content">
          <h2>Session Expired</h2>
          <p>Your session has expired for security reasons. Please log in again.</p>
          <button onClick={() => window.location.href = '/login'} className="btn btn-primary">
            Go to Login
          </button>
        </div>
      </div>
    );
  }
  
	return (
		<>
			<header className="header">
				<div className="container header-content">
					<div className="header-logo">Healthcare Management System</div>
					{user && (
						<div className="header-user">
							<NotificationBell className="notification-bell" />
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
							user ? <Navigate to="/dashboard" replace /> : <Login />
						} />
						<Route path="/dashboard" element={
							<ProtectedRoute>
								{(() => {
									switch (user?.role) {
										case 'patient':
											return <PatientDashboard user={user} />;
										case 'doctor':
											return <DoctorDashboard user={user} />;
										case 'receptionist':
											return <ReceptionistDashboard user={user} />;
										case 'medical_coordinator':
											return <MedicalCoordinatorDashboard user={user} />;
										case 'admin':
											return <AdminDashboard user={user} />;
										case 'super_admin':
											return <SuperAdminDashboard user={user} />;
										default:
											return (
												<div className="dashboard">
													<h1>Unknown Role: {user?.role}</h1>
													<p>Your role "{user?.role}" is not recognized. Please contact support.</p>
													<pre>{JSON.stringify(user, null, 2)}</pre>
												</div>
											);
									}
								})()}
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
						<Route path="/medical-coordinator/dashboard" element={
							<ProtectedRoute allowedRoles={['medical_coordinator']}>
								<MedicalCoordinatorDashboard user={user} />
							</ProtectedRoute>
						} />
						<Route path="/admin/dashboard" element={
							<ProtectedRoute allowedRoles={['admin']}>
								<AdminDashboard user={user} />
							</ProtectedRoute>
						} />
						<Route path="/superadmin/dashboard" element={
							<ProtectedRoute allowedRoles={['super_admin']}>
								<SuperAdminDashboard user={user} />
							</ProtectedRoute>
						} />
					</Routes>
				</div>
			</main>
		</>
	);
};

export default App;