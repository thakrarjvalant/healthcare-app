// Test script to simulate the frontend API calls

// Simulate the ApiService.getUsers() call
async function testGetUsers() {
  try {
    console.log('Testing getUsers...');
    
    const response = await fetch('http://localhost:8000/api/admin/users', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer admin-token',
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    console.log('getUsers response:', data);
    
    if (data.status === 200 && data.data && data.data.users) {
      console.log('✅ getUsers working correctly');
      console.log('Users found:', data.data.users.length);
    } else {
      console.log('❌ getUsers response format issue');
    }
  } catch (error) {
    console.log('❌ getUsers error:', error);
  }
}

// Simulate the ApiService.getDynamicRoles() call
async function testGetDynamicRoles() {
  try {
    console.log('\nTesting getDynamicRoles...');
    
    const response = await fetch('http://localhost:8000/api/admin/roles', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer admin-token',
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    console.log('getDynamicRoles response:', data);
    
    if (data.status === 200 && data.data && data.data.roles) {
      console.log('✅ getDynamicRoles working correctly');
      console.log('Roles found:', data.data.roles.length);
    } else {
      console.log('❌ getDynamicRoles response format issue');
    }
  } catch (error) {
    console.log('❌ getDynamicRoles error:', error);
  }
}

// Run the tests
testGetUsers();
testGetDynamicRoles();