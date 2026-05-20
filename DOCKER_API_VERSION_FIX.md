# Docker API Version Issue Resolution

## Issue Description
The error message indicates a Docker API version mismatch:
```
request returned 500 Internal Server Error for API route and version http://%2F%2F.%2Fpipe%2FdockerDesktopLinuxEngine/v1.51/containers/json, check if the server supports the requested API version
```

## Root Cause
This is typically caused by:
1. Version mismatch between Docker client and Docker daemon
2. Docker Desktop needing a restart
3. Corrupted Docker installation
4. PowerShell terminal issues

## Resolution Steps

### Option 1: Restart Docker Desktop (Recommended)
1. Close Docker Desktop completely from the system tray
2. Wait 30 seconds
3. Restart Docker Desktop as Administrator
4. Wait for the whale icon to become stable (green/white)
5. Try `docker ps` again

### Option 2: Reset Docker to Factory Defaults
1. Open Docker Desktop
2. Go to Settings → Reset
3. Click "Reset to factory defaults"
4. Restart Docker Desktop
5. Rebuild and start containers:
   ```
   cd d:\customprojects\healthcare-app
   docker-compose down
   docker-compose up -d --build
   ```

### Option 3: Use Command Prompt Instead of PowerShell
1. Open Command Prompt (cmd) as Administrator
2. Navigate to project directory:
   ```
   cd d:\customprojects\healthcare-app
   ```
3. Check Docker version:
   ```
   docker --version
   docker version
   ```
4. Check container status:
   ```
   docker ps
   ```

### Option 4: Clear Docker Context and Reconfigure
1. List Docker contexts:
   ```
   docker context ls
   ```
2. Use default context:
   ```
   docker context use default
   ```
3. Restart Docker services:
   ```
   docker system prune -a
   ```

### Option 5: Reinstall Docker Desktop
If other options fail:
1. Uninstall Docker Desktop
2. Download latest version from docker.com
3. Install as Administrator
4. Enable required Windows features (WSL2, Hyper-V)
5. Restart computer
6. Start Docker Desktop

## Verification Steps
After applying any of the above solutions:

1. **Check Docker Status**:
   ```bash
   docker info
   ```

2. **Verify Container Listing**:
   ```bash
   docker ps
   ```

3. **Test Healthcare App Containers**:
   ```bash
   docker-compose ps
   ```

4. **If successful, test the API**:
   ```bash
   curl http://localhost:8000/health
   ```

## Expected Outcome
Once resolved:
- `docker ps` should show running containers
- Healthcare app services should be accessible
- API endpoints should respond correctly
- No more API version errors

## Additional Notes
- This is a common issue with Docker Desktop on Windows
- Usually resolved by restarting Docker Desktop
- The healthcare app configuration is correct and ready to run
- Only the Docker environment needs to be fixed