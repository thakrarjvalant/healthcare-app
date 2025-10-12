// Test script to verify the API fix

const https = require('https');
const http = require('http');

// Function to make HTTP requests
function makeRequest(url, options = {}) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    const lib = urlObj.protocol === 'https:' ? https : http;
    
    const requestOptions = {
      hostname: urlObj.hostname,
      port: urlObj.port,
      path: urlObj.pathname + urlObj.search,
      method: options.method || 'GET',
      headers: {
        'Authorization': 'Bearer admin-token',
        'Content-Type': 'application/json',
        ...options.headers
      }
    };
    
    const req = lib.request(requestOptions, (res) => {
      let data = '';
      
      res.on('data', (chunk) => {
        data += chunk;
      });
      
      res.on('end', () => {
        try {
          const jsonData = JSON.parse(data);
          resolve(jsonData);
        } catch (error) {
          resolve(data);
        }
      });
    });
    
    req.on('error', (error) => {
      reject(error);
    });
    
    if (options.body) {
      req.write(JSON.stringify(options.body));
    }
    
    req.end();
  });
}

async function testAPI() {
  console.log('Testing API endpoints with fixed URLs...\n');
  
  try {
    // Test getUsers endpoint
    console.log('1. Testing getUsers endpoint:');
    const usersResponse = await makeRequest('http://localhost:8000/api/admin/users');
    console.log('Response:', JSON.stringify(usersResponse, null, 2));
    
    if (usersResponse.status === 200 && usersResponse.data && usersResponse.data.users) {
      console.log('✅ getUsers working correctly\n');
    } else {
      console.log('❌ getUsers response format issue\n');
    }
    
    // Test getDynamicRoles endpoint
    console.log('2. Testing getDynamicRoles endpoint:');
    const rolesResponse = await makeRequest('http://localhost:8000/api/admin/roles');
    console.log('Response:', JSON.stringify(rolesResponse, null, 2));
    
    if (rolesResponse.status === 200 && rolesResponse.data && rolesResponse.data.roles) {
      console.log('✅ getDynamicRoles working correctly\n');
    } else {
      console.log('❌ getDynamicRoles response format issue\n');
    }
    
  } catch (error) {
    console.error('Error testing API:', error.message);
  }
}

testAPI();