<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .header {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sidebar {
            width: 200px;
            background-color: #444;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 1rem;
        }
        
        .sidebar a {
            display: block;
            color: white;
            padding: 1rem;
            text-decoration: none;
        }
        
        .sidebar a:hover {
            background-color: #555;
        }
        
        .main-content {
            margin-left: 200px;
            padding: 2rem;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem;
            text-align: center;
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: #666;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 0.5rem;
            text-align: left;
        }
        
        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div>
            <span>Welcome, Admin</span>
            <a href="/logout" style="color: white; margin-left: 1rem;">Logout</a>
        </div>
    </div>
    
    <div class="sidebar">
        <a href="/admin/dashboard">Dashboard</a>
        <a href="/admin/users">User Management</a>
        <a href="/admin/settings">System Settings</a>
        <a href="/admin/reports">Reports</a>
        <a href="/admin/logs">Audit Logs</a>
    </div>
    
    <div class="main-content">
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value">1,250</div>
            </div>
            <div class="stat-card">
                <h3>Total Appointments</h3>
                <div class="value">342</div>
            </div>
            <div class="stat-card">
                <h3>Total Doctors</h3>
                <div class="value">15</div>
            </div>
            <div class="stat-card">
                <h3>Total Patients</h3>
                <div class="value">1,120</div>
            </div>
        </div>
        
        <div class="card">
            <h2>Recent Activities</h2>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>John Doe</td>
                        <td>Booked appointment</td>
                        <td>2023-08-20 14:30:00</td>
                    </tr>
                    <tr>
                        <td>Dr. Smith</td>
                        <td>Updated treatment plan</td>
                        <td>2023-08-20 12:15:00</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <h2>System Status</h2>
            <p>All systems operational</p>
        </div>
    </div>
</body>
</html>