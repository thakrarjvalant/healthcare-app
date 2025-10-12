import React from 'react';
import { Routes, Route } from 'react-router-dom';
import PatientDashboard from '../pages/patient/Dashboard';
import DoctorDashboard from '../pages/doctor/Dashboard';
import ReceptionistDashboard from '../pages/receptionist/Dashboard';
import AdminDashboard from '../pages/admin/Dashboard';
import MedicalCoordinatorDashboard from '../pages/medical-coordinator/Dashboard';
import Login from '../components/user/Login';
import Register from '../components/user/Register';

const AppRoutes = ({ user, onLogin }) => {
  return (
    <Routes>
      {!user ? (
        <>
          <Route path="/login" element={<Login onLogin={onLogin} />} />
          <Route path="/register" element={<Register />} />
          <Route path="/" element={<Login onLogin={onLogin} />} />
        </>
      ) : (
        <>
          <Route path="/" element={
            user.role === 'patient' ? <PatientDashboard user={user} /> :
            user.role === 'doctor' ? <DoctorDashboard user={user} /> :
            user.role === 'receptionist' ? <ReceptionistDashboard user={user} /> :
            user.role === 'admin' ? <AdminDashboard user={user} /> :
            user.role === 'medical_coordinator' ? <MedicalCoordinatorDashboard user={user} /> :
            <div>Unknown role</div>
          } />
          <Route path="/dashboard" element={
            user.role === 'patient' ? <PatientDashboard user={user} /> :
            user.role === 'doctor' ? <DoctorDashboard user={user} /> :
            user.role === 'receptionist' ? <ReceptionistDashboard user={user} /> :
            user.role === 'admin' ? <AdminDashboard user={user} /> :
            user.role === 'medical_coordinator' ? <MedicalCoordinatorDashboard user={user} /> :
            <div>Unknown role</div>
          } />
          {/* Add more routes here as needed */}
        </>
      )}
    </Routes>
  );
};

export default AppRoutes;