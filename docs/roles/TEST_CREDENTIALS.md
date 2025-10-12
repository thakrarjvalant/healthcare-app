# Healthcare App - Test Credentials

## ✅ Current Database Users (Working)

These credentials are stored in the actual MySQL database and work with the real authentication system:

### Admin User
- **Email**: `admin@example.com`  
- **Password**: `password123`  
- **Role**: Admin
- **Dashboard**: System management, user management, reports & analytics

### Doctor User
- **Email**: `jane.smith@example.com`  
- **Password**: `password123`  
- **Role**: Doctor
- **Dashboard**: Patient appointments, treatment plans, medical records
- **Display Name**: "Dr. Jane Smith"

### Receptionist User  
- **Email**: `bob.receptionist@example.com`  
- **Password**: `password123`  
- **Role**: Receptionist
- **Dashboard**: All appointments, patient registration, check-in functions
- **Display Name**: "Receptionist Bob"

### Patient User
- **Email**: `john.doe@example.com`  
- **Password**: `password123`  
- **Role**: Patient
- **Dashboard**: Appointment booking, medical history, personal reports
- **Display Name**: "John Doe"

## 🚫 Legacy Credentials (No Longer Valid)

These were mock credentials that have been replaced by the database-backed authentication:
- ~~admin@healthcare.com~~ 
- ~~doctor@healthcare.com~~
- ~~receptionist@healthcare.com~~
- ~~patient@healthcare.com~~

## 🚀 Usage Instructions

1. **Navigate to** `http://localhost:3000`
2. **Clear browser cache/localStorage** if you previously used the old mock credentials
3. **Use the database credentials** listed above to login
4. **You will be automatically redirected** to the appropriate dashboard based on your role
5. **Each role displays different UI** with role-specific features and welcome messages

## 🔄 Troubleshooting Login Issues

If you see "Test Patient" or wrong role after login:
1. **Clear browser localStorage**: F12 → Application → Local Storage → Delete all
2. **Clear browser cache**: Ctrl+Shift+Delete
3. **Refresh the page** and try logging in again
4. **Use only the database credentials** listed above

## 📊 Database Information

**Connection Details for MySQL Workbench:**
- **Host**: `localhost`
- **Port**: `3306`
- **Database**: `healthcare_db`
- **Username**: `healthcare_user`
- **Password**: `your_strong_password`

**Users Table Structure:**
```sql
SELECT id, name, email, role FROM users;
```

## 🔐 Authentication System

- **Database-backed**: All authentication now uses the MySQL database
- **Password hashing**: Passwords are hashed using PHP's `password_hash()`
- **No more fallback**: Mock authentication has been removed
- **Role-based UI**: Each role sees a completely different dashboard

## 📝 Registration

New user registration is available, but:
- **Patient role**: Available for self-registration
- **Doctor/Receptionist**: Must be created by admin
- **Admin role**: Not available for registration (security purposes)