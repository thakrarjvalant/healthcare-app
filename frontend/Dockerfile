# Multi-stage build for optimized image size and build time

# Stage 1: Build stage
FROM node:18-alpine AS builder

# Set working directory
WORKDIR /app

# Copy package files first for better caching
COPY package*.json ./

# Install all dependencies (including dev dependencies needed for build)
RUN npm ci --silent

# Copy source code and public files
COPY . .

# Build the application with environment variables
ARG REACT_APP_API_BASE_URL
ARG REACT_APP_USER_SERVICE_BASE_URL
ARG REACT_APP_ADMIN_UI_BASE_URL
ARG REACT_APP_ENV

ENV REACT_APP_API_BASE_URL=$REACT_APP_API_BASE_URL
ENV REACT_APP_USER_SERVICE_BASE_URL=$REACT_APP_USER_SERVICE_BASE_URL
ENV REACT_APP_ADMIN_UI_BASE_URL=$REACT_APP_ADMIN_UI_BASE_URL
ENV REACT_APP_ENV=$REACT_APP_ENV

RUN npm run build

# Stage 2: Production stage
FROM node:18-alpine AS production

# Install serve globally and curl for health checks
RUN apk add --no-cache curl
RUN npm install -g serve@14 --silent

# Set working directory
WORKDIR /app

# Copy built application from builder stage
COPY --from=builder /app/build ./build

# Create non-root user for security
RUN addgroup -g 1001 -S nodejs
RUN adduser -S reactjs -u 1001

# Change ownership of the app directory
RUN chown -R reactjs:nodejs /app
USER reactjs

# Expose port
EXPOSE 3000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:3000 || exit 1

# Start the application
CMD ["serve", "-s", "build", "-l", "3000"]