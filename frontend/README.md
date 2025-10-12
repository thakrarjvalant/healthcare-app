# Frontend Application

This directory contains the frontend application for the Healthcare Management System. It's built with React and provides a responsive user interface for patients, doctors, receptionists, and administrators.

## Project Structure

```
frontend/
├── public/                 # Public assets
├── src/                    # Source code
│   ├── assets/             # Images, styles, and other assets
│   ├── components/         # Reusable UI components
│   ├── context/            # React context providers
│   ├── hooks/              # Custom React hooks
│   ├── pages/              # Page components
│   ├── routes/             # Routing configuration
│   ├── services/           # API service layer
│   ├── utils/              # Utility functions
│   ├── App.js              # Main application component
│   └── index.js            # Entry point
├── package.json            # Project dependencies and scripts
└── README.md               # This file
```

## Getting Started

### Prerequisites

- Node.js 14.x or higher
- npm 6.x or higher

### Installation

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm start
   ```

   The application will be available at `http://localhost:3000`.

### Environment Variables

Create a `.env` file in the frontend directory with the following variables:

```
REACT_APP_API_BASE_URL=http://localhost:8000/api
```

## Documentation

### 📚 Complete Documentation
All documentation has been organized in the [docs/](../docs/) directory. Start with [docs/README.md](../docs/README.md) for a complete overview.

### 🚀 Quick Links
- [Dashboard Features](../docs/features/DASHBOARD_FEATURES.md) - Detailed feature documentation
- [Transferred Features Summary](../docs/features/TRANSFERRED_FEATURES_SUMMARY.md) - Features moved between roles
- [Frontend Build Optimization](../docs/FRONTEND_BUILD_OPTIMIZATION.md) - Build optimization techniques
- [Test Credentials](../docs/roles/TEST_CREDENTIALS.md) - Working login credentials
- [Troubleshooting Guide](../docs/TROUBLESHOOTING.md) - Common issues and solutions

## Development

### Components

The application is organized into components by feature:

- `components/appointment/` - Appointment booking and management
- `components/billing/` - Billing and invoicing
- `components/clinical/` - Medical records and treatment plans
- `components/notification/` - Notifications
- `components/storage/` - Document storage and retrieval
- `components/user/` - User authentication and profile management

### Pages

Each user role has its own dashboard:

- `pages/patient/` - Patient dashboard and features
- `pages/doctor/` - Doctor dashboard and features
- `pages/receptionist/` - Receptionist dashboard and features
- `pages/admin/` - Admin dashboard and features
- `pages/medical-coordinator/` - Medical Coordinator dashboard and features

### Context

The application uses React Context for state management:

- `context/AuthContext.js` - Authentication state and functions

### Hooks

Custom hooks are used for common functionality:

- `hooks/useApi.js` - API call management
- `hooks/useAuth.js` - Authentication-related functions

### Services

The service layer handles API communication:

- `services/api.js` - Generic API functions
- `services/auth.js` - Authentication-related API calls

### Utilities

Utility functions for common tasks:

- `utils/formatters.js` - Date, currency, and text formatting
- `utils/auth.js` - Authentication helper functions
- `utils/api.js` - API helper functions

## Styling

The application uses CSS for styling with a consistent design system:

- `assets/styles/main.css` - Main stylesheet
- Component-specific CSS files in each component directory

## Routing

The application uses React Router for navigation:

- `routes/AppRoutes.js` - Route definitions

## Testing

### Unit Tests

Run unit tests with:

```bash
npm test
```

### Integration Tests

Run integration tests with:

```bash
npm run test:integration
```

### End-to-End Tests

Run end-to-end tests with:

```bash
npm run test:e2e
```

## Building for Production

To create a production build:

```bash
npm run build
```

The build artifacts will be stored in the `build/` directory.

## Deployment

### Docker

The frontend can be deployed using Docker. See the docker-compose.yml file for configuration.

### Static Hosting

The production build can be deployed to any static hosting service (Netlify, Vercel, etc.).

## Code Quality

### Linting

Run the linter with:

```bash
npm run lint
```

### Formatting

Format the code with:

```bash
npm run format
```

### Coding Standards

- Follow Airbnb JavaScript style guide
- Use functional components with hooks
- Write JSDoc comments for complex functions
- Keep components small and focused
- Use PropTypes for type checking

## Troubleshooting

### Common Issues

1. **Application Not Loading**
   - Check if the development server is running
   - Verify API endpoints are accessible
   - Check browser console for error messages

2. **Authentication Issues**
   - Verify API base URL in environment variables
   - Check browser storage for token
   - Verify user credentials

3. **Styling Issues**
   - Check CSS file imports
   - Verify class names match CSS selectors
   - Check browser developer tools for styling conflicts

### Getting Help

If you encounter issues not covered in this guide:

1. Check the browser console for error messages
2. Review the component documentation
3. Contact the development team