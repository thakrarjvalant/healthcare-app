# Docker Build Instructions for Healthcare Application

## 📋 Prerequisites

Before building the Docker containers, ensure you have:

1. **Docker Desktop** installed and running
2. **Windows PowerShell** or **PowerShell 7+**
3. **Administrator privileges** for running Docker commands

## 🏗️ Building the Frontend Container

### Method 1: Using the PowerShell Script (Recommended)

1. Navigate to the project root directory:
   ```
   cd d:\customprojects\healthcare-app
   ```

2. Run the build script as Administrator:
   ```
   .\build-frontend.ps1
   ```

### Method 2: Manual Docker Build

1. Open PowerShell as Administrator
2. Navigate to the frontend directory:
   ```
   cd d:\customprojects\healthcare-app\frontend
   ```

3. Build the container:
   ```
   docker build -t healthcare-app-frontend .
   ```

## ⚙️ Build Process Details

The frontend Docker build uses a multi-stage approach:

### Stage 1: Builder
- Uses `node:18-alpine` as the base image
- Installs dependencies with `npm ci` for faster, consistent builds
- Builds the React application with `npm run build`

### Stage 2: Production
- Uses `node:18-alpine` as the base image
- Installs `serve` to serve the static files
- Copies the built application from the builder stage
- Runs as a non-root user for security
- Includes health checks

## 🐳 Running the Container

After building, run the container with:

```
docker run -p 3000:3000 healthcare-app-frontend
```

Then access the application at http://localhost:3000

## 🔧 Troubleshooting

### Common Issues

1. **Docker Connection Errors**:
   - Ensure Docker Desktop is running
   - Run PowerShell as Administrator
   - Check Docker context with `docker context ls`

2. **Build Failures**:
   - Clean Docker cache: `docker system prune -af`
   - Check available disk space
   - Verify package.json dependencies

3. **Permission Issues**:
   - Ensure you have write permissions to the project directory
   - Run PowerShell as Administrator

### Docker Context Issues

If you encounter context-related errors:

```bash
# List available contexts
docker context ls

# Switch to default context
docker context use default

# Or switch to Docker Desktop context
docker context use desktop-linux
```

## 📊 Build Optimization

The Docker build is optimized with:

1. **Layer Caching**: Dependencies are copied before source code
2. **Multi-stage Build**: Reduces final image size
3. **Alpine Base**: Minimal base images for smaller footprint
4. **BuildKit**: Enabled for parallel builds and better caching

## 🛡️ Security Considerations

1. **Non-root User**: Application runs as non-root user
2. **Minimal Base**: Uses Alpine Linux for reduced attack surface
3. **Health Checks**: Built-in health monitoring

## 📈 Performance Tips

1. **Use .dockerignore**: Prevents unnecessary files from being included in build context
2. **Cache Dependencies**: Package files are copied first for better caching
3. **Multi-stage Builds**: Reduces final image size by ~60%

## 🔄 CI/CD Integration

For automated builds, use:

```bash
# Enable BuildKit
export DOCKER_BUILDKIT=1

# Build with specific tags
docker build -t healthcare-app/frontend:latest -t healthcare-app/frontend:v1.0.0 .
```