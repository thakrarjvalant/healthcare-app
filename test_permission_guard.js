// Test PermissionGuard logic with sample user data

// Simulate the hasPermission function from AuthContext
const createHasPermissionFunction = (user) => {
  return (permission) => {
    if (!user || !user.permissions) return false;
    return user.permissions.includes(permission);
  };
};

// Simulate the PermissionGuard logic
const permissionGuardCheck = (user, requiredPermissions = [], checkAny = false) => {
  const hasPermission = createHasPermissionFunction(user);
  
  const hasRequiredPermissions = () => {
    if (requiredPermissions.length === 0) return true;
    
    if (checkAny) {
      return requiredPermissions.some(permission => hasPermission(permission));
    } else {
      return requiredPermissions.every(permission => hasPermission(permission));
    }
  };

  // Check permissions if specified
  if (requiredPermissions.length > 0 && !hasRequiredPermissions()) {
    return false; // Would return fallback
  }

  return true; // Would return children
};

// Test with sample user data that matches what we expect
const sampleUser = {
  "id": 5,
  "name": "Medical Coordinator",
  "email": "medical.coordinator@example.com",
  "role": "medical_coordinator",
  "roles": [
    {
      "id": 6,
      "name": "medical_coordinator",
      "display_name": "Medical Coordinator",
      "permissions": [
        "patients.basic_create",
        "patients.basic_read",
        "patients.assign_clinician",
        "patients.limited_history"
      ]
    }
  ],
  "permissions": [
    "patients.assign_clinician",
    "patients.basic_create",
    "patients.basic_read",
    "patients.limited_history"
  ],
  "verified": 1,
  "created_at": "2025-10-12 20:02:42",
  "updated_at": "2025-10-12 20:02:42"
};

console.log("Testing PermissionGuard logic with sample user data:\n");

// Test 1: Check for patients.assign_clinician permission
const test1 = permissionGuardCheck(sampleUser, ['patients.assign_clinician']);
console.log("Test 1 - patients.assign_clinician:", test1 ? "✅ PASS" : "❌ FAIL");

// Test 2: Check for patients.limited_history permission
const test2 = permissionGuardCheck(sampleUser, ['patients.limited_history']);
console.log("Test 2 - patients.limited_history:", test2 ? "✅ PASS" : "❌ FAIL");

// Test 3: Check for both permissions (AND condition)
const test3 = permissionGuardCheck(sampleUser, ['patients.assign_clinician', 'patients.limited_history']);
console.log("Test 3 - Both permissions (AND):", test3 ? "✅ PASS" : "❌ FAIL");

// Test 4: Check for either permission (OR condition)
const test4 = permissionGuardCheck(sampleUser, ['patients.assign_clinician', 'patients.limited_history'], true);
console.log("Test 4 - Either permission (OR):", test4 ? "✅ PASS" : "❌ FAIL");

// Test 5: Check for a missing permission
const test5 = permissionGuardCheck(sampleUser, ['patients.nonexistent_permission']);
console.log("Test 5 - Nonexistent permission:", test5 ? "❌ FAIL (should be denied)" : "✅ PASS (correctly denied)");

console.log("\nUser permissions:", sampleUser.permissions);