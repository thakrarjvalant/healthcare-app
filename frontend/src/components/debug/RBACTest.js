import React, { useContext, useEffect, useState } from 'react';
import { AuthContext } from '../../context/AuthContext';
import ApiService from '../../services/api';

const RBACTest = () => {
  const { user, hasPermission, refreshPermissions } = useContext(AuthContext);
  const [testResults, setTestResults] = useState({});
  const [loading, setLoading] = useState(false);

  const runRBACTests = async () => {
    setLoading(true);
    const results = {};
    
    try {
      // Test 1: Check user roles and permissions
      results.userCheck = {
        userId: user?.id,
        userRole: user?.role,
        permissionsCount: user?.permissions?.length || 0,
        hasSuperAdminPerm: hasPermission('system.configure_roles'),
        hasAdminPerm: hasPermission('users.create'),
        hasDoctorPerm: hasPermission('patients.clinical_read'),
        hasReceptionistPerm: hasPermission('appointments.create'),
        hasPatientPerm: hasPermission('appointments.self_read'),
        hasMedicalCoordinatorPerm: hasPermission('patients.assign_clinician')
      };
      
      // Test 2: Fetch roles
      try {
        const rolesResponse = await ApiService.getDynamicRoles();
        results.rolesFetch = {
          success: rolesResponse.status === 200,
          rolesCount: rolesResponse.data?.roles?.length || 0,
          roles: rolesResponse.data?.roles?.map(r => r.name) || []
        };
      } catch (error) {
        results.rolesFetch = {
          success: false,
          error: error.message
        };
      }
      
      // Test 3: Fetch permissions
      try {
        const permsResponse = await ApiService.getAllPermissions();
        results.permissionsFetch = {
          success: permsResponse.status === 200,
          permissionsCount: permsResponse.data?.permissions?.length || 0
        };
      } catch (error) {
        results.permissionsFetch = {
          success: false,
          error: error.message
        };
      }
      
      // Test 4: Fetch feature modules
      try {
        const modulesResponse = await ApiService.getFeatureModules();
        results.modulesFetch = {
          success: modulesResponse.status === 200,
          modulesCount: modulesResponse.data?.modules?.length || 0
        };
      } catch (error) {
        results.modulesFetch = {
          success: false,
          error: error.message
        };
      }
      
      // Test 5: If user is super admin, test role management
      if (user?.role === 'super_admin') {
        try {
          // Get a role to test with (use the first one)
          const rolesResponse = await ApiService.getDynamicRoles();
          if (rolesResponse.data?.roles?.length > 0) {
            const testRole = rolesResponse.data.roles[0];
            
            // Fetch role permissions
            const rolePermsResponse = await ApiService.getRolePermissions(testRole.id);
            results.rolePermissionsFetch = {
              success: rolePermsResponse.status === 200,
              permissionsCount: rolePermsResponse.data?.permissions?.length || 0
            };
            
            // Fetch role feature access
            const roleFeaturesResponse = await ApiService.getRoleFeatureAccess(testRole.id);
            results.roleFeaturesFetch = {
              success: roleFeaturesResponse.status === 200,
              featuresCount: roleFeaturesResponse.data?.feature_access?.length || 0
            };
          }
        } catch (error) {
          results.roleManagement = {
            success: false,
            error: error.message
          };
        }
      }
      
    } catch (error) {
      results.generalError = error.message;
    }
    
    setTestResults(results);
    setLoading(false);
  };

  useEffect(() => {
    if (user) {
      runRBACTests();
    }
  }, [user]);

  return (
    <div style={{ 
      position: 'fixed', 
      top: 0, 
      right: 0, 
      background: 'rgba(0,0,0,0.9)', 
      color: 'white', 
      padding: '10px', 
      fontSize: '12px',
      zIndex: 9999,
      maxWidth: '400px',
      maxHeight: '400px',
      overflowY: 'auto'
    }}>
      <h3>RBAC System Test</h3>
      <button 
        onClick={runRBACTests} 
        disabled={loading}
        style={{marginBottom: '10px', padding: '5px 10px'}}
      >
        {loading ? 'Testing...' : 'Run Tests'}
      </button>
      <button 
        onClick={refreshPermissions} 
        style={{marginBottom: '10px', padding: '5px 10px', marginLeft: '10px'}}
      >
        Refresh Permissions
      </button>
      <pre>{JSON.stringify(testResults, null, 2)}</pre>
    </div>
  );
};

export default RBACTest;