// Permission-based UI components
export { default as PermissionGuard } from './PermissionGuard';
export {
  withPermissionGuard,
  AdminOnly,
  DoctorOnly,
  ReceptionistOnly,
  PatientOnly,
  PermissionMatrix
} from './PermissionGuard';

// Audit logging components
export { default as AuditLogger } from './AuditLogger';
export {
  AuditLogViewer,
  withAuditLogging
} from './AuditLogger';

// Notification system components
export { default as NotificationContext } from './NotificationCenter';
export {
  NotificationProvider,
  useNotifications,
  NotificationBell,
  ToastNotification,
  ToastContainer
} from './NotificationCenter';

// Data visualization components
export { default as DataVisualization } from './DataVisualization';
export {
  LineChart,
  AreaChart,
  BarChart,
  PieChart,
  KPICard,
  DashboardGrid,
  HealthMetricsChart,
  AppointmentAnalytics,
  RevenueAnalytics,
  SystemMetrics
} from './DataVisualization';

// Security components
export {
  SessionWarningModal,
  SessionInfo,
  DataEncryptionStatus,
  UserActivityMonitor,
  SecuritySettings
} from './SecurityComponents';

// Existing components
export { default as Modal } from './Modal';