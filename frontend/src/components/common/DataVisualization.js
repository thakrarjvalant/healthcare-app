import React from 'react';
import {
  LineChart as RechartsLineChart,
  Line,
  AreaChart as RechartsAreaChart,
  Area,
  BarChart as RechartsBarChart,
  Bar,
  PieChart as RechartsPieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer
} from 'recharts';
import './DataVisualization.css';

// Color palette for consistent theming
const COLORS = {
  primary: '#007bff',
  secondary: '#6c757d',
  success: '#28a745',
  danger: '#dc3545',
  warning: '#ffc107',
  info: '#17a2b8',
  light: '#f8f9fa',
  dark: '#343a40'
};

const CHART_COLORS = [
  '#007bff', '#28a745', '#dc3545', '#ffc107',
  '#17a2b8', '#6f42c1', '#fd7e14', '#20c997'
];

// Line Chart Component
export const LineChart = ({ data, xKey, yKeys, title, height = 300 }) => {
  return (
    <div className="chart-container">
      {title && <h3 className="chart-title">{title}</h3>}
      <ResponsiveContainer width="100%" height={height}>
        <RechartsLineChart data={data} margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey={xKey} />
          <YAxis />
          <Tooltip />
          <Legend />
          {yKeys.map((key, index) => (
            <Line
              key={key}
              type="monotone"
              dataKey={key}
              stroke={CHART_COLORS[index % CHART_COLORS.length]}
              strokeWidth={2}
              dot={{ r: 4 }}
            />
          ))}
        </RechartsLineChart>
      </ResponsiveContainer>
    </div>
  );
};

// Area Chart Component
export const AreaChart = ({ data, xKey, yKeys, title, height = 300 }) => {
  return (
    <div className="chart-container">
      {title && <h3 className="chart-title">{title}</h3>}
      <ResponsiveContainer width="100%" height={height}>
        <RechartsAreaChart data={data} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey={xKey} />
          <YAxis />
          <Tooltip />
          <Legend />
          {yKeys.map((key, index) => (
            <Area
              key={key}
              type="monotone"
              dataKey={key}
              stackId="1"
              stroke={CHART_COLORS[index % CHART_COLORS.length]}
              fill={CHART_COLORS[index % CHART_COLORS.length]}
              fillOpacity={0.6}
            />
          ))}
        </RechartsAreaChart>
      </ResponsiveContainer>
    </div>
  );
};

// Bar Chart Component
export const BarChart = ({ data, xKey, yKeys, title, height = 300, horizontal = false }) => {
  return (
    <div className="chart-container">
      {title && <h3 className="chart-title">{title}</h3>}
      <ResponsiveContainer width="100%" height={height}>
        <RechartsBarChart 
          data={data} 
          margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
          layout={horizontal ? 'horizontal' : 'vertical'}
        >
          <CartesianGrid strokeDasharray="3 3" />
          {horizontal ? (
            <>
              <XAxis type="number" />
              <YAxis dataKey={xKey} type="category" />
            </>
          ) : (
            <>
              <XAxis dataKey={xKey} />
              <YAxis />
            </>
          )}
          <Tooltip />
          <Legend />
          {yKeys.map((key, index) => (
            <Bar
              key={key}
              dataKey={key}
              fill={CHART_COLORS[index % CHART_COLORS.length]}
            />
          ))}
        </RechartsBarChart>
      </ResponsiveContainer>
    </div>
  );
};

// Pie Chart Component
export const PieChart = ({ data, dataKey, nameKey, title, height = 300, showLabels = true }) => {
  const renderLabel = (entry) => {
    if (!showLabels) return null;
    return `${entry[nameKey]}: ${entry[dataKey]}`;
  };

  return (
    <div className="chart-container">
      {title && <h3 className="chart-title">{title}</h3>}
      <ResponsiveContainer width="100%" height={height}>
        <RechartsPieChart>
          <Pie
            data={data}
            cx="50%"
            cy="50%"
            labelLine={false}
            label={showLabels ? renderLabel : false}
            outerRadius={80}
            fill="#8884d8"
            dataKey={dataKey}
          >
            {data.map((entry, index) => (
              <Cell key={`cell-${index}`} fill={CHART_COLORS[index % CHART_COLORS.length]} />
            ))}
          </Pie>
          <Tooltip />
          <Legend />
        </RechartsPieChart>
      </ResponsiveContainer>
    </div>
  );
};

// KPI Card Component
export const KPICard = ({ title, value, change, trend, icon, color = 'primary' }) => {
  const getTrendIcon = () => {
    if (trend === 'up') return '📈';
    if (trend === 'down') return '📉';
    return '➡️';
  };

  const getTrendClass = () => {
    if (trend === 'up') return 'trend-up';
    if (trend === 'down') return 'trend-down';
    return 'trend-neutral';
  };

  return (
    <div className={`kpi-card kpi-${color}`}>
      <div className="kpi-header">
        <span className="kpi-icon">{icon}</span>
        <span className="kpi-title">{title}</span>
      </div>
      <div className="kpi-value">{value}</div>
      {change && (
        <div className={`kpi-change ${getTrendClass()}`}>
          <span className="trend-icon">{getTrendIcon()}</span>
          <span className="change-value">{change}</span>
        </div>
      )}
    </div>
  );
};

// Dashboard Grid Component for organizing multiple charts
export const DashboardGrid = ({ children, columns = 2 }) => {
  return (
    <div 
      className="dashboard-grid" 
      style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}
    >
      {children}
    </div>
  );
};

// Health Metrics Visualization (specific to healthcare)
export const HealthMetricsChart = ({ patientData, metric, timeRange = '7d' }) => {
  const getMetricConfig = (metric) => {
    switch (metric) {
      case 'vitals':
        return {
          title: 'Vital Signs Trends',
          yKeys: ['heartRate', 'bloodPressureSystolic', 'bloodPressureDiastolic', 'temperature'],
          colors: ['#dc3545', '#007bff', '#17a2b8', '#ffc107']
        };
      case 'symptoms':
        return {
          title: 'Symptom Severity',
          yKeys: ['pain', 'fatigue', 'nausea'],
          colors: ['#dc3545', '#fd7e14', '#6f42c1']
        };
      case 'medication':
        return {
          title: 'Medication Adherence',
          yKeys: ['adherenceRate'],
          colors: ['#28a745']
        };
      default:
        return {
          title: 'Health Metrics',
          yKeys: ['value'],
          colors: ['#007bff']
        };
    }
  };

  const config = getMetricConfig(metric);

  return (
    <LineChart
      data={patientData}
      xKey="date"
      yKeys={config.yKeys}
      title={config.title}
      height={350}
    />
  );
};

// Appointment Analytics Chart
export const AppointmentAnalytics = ({ appointmentData, viewType = 'daily' }) => {
  const getChartData = () => {
    switch (viewType) {
      case 'status':
        return {
          component: PieChart,
          props: {
            data: appointmentData.statusBreakdown,
            dataKey: 'count',
            nameKey: 'status',
            title: 'Appointments by Status',
            height: 300
          }
        };
      case 'department':
        return {
          component: BarChart,
          props: {
            data: appointmentData.departmentBreakdown,
            xKey: 'department',
            yKeys: ['count'],
            title: 'Appointments by Department',
            height: 300
          }
        };
      default: // daily
        return {
          component: LineChart,
          props: {
            data: appointmentData.dailyTrends,
            xKey: 'date',
            yKeys: ['scheduled', 'completed', 'cancelled'],
            title: 'Daily Appointment Trends',
            height: 300
          }
        };
    }
  };

  const { component: ChartComponent, props } = getChartData();
  return <ChartComponent {...props} />;
};

// Revenue Analytics Chart
export const RevenueAnalytics = ({ revenueData, period = 'monthly' }) => {
  return (
    <div className="revenue-analytics">
      <AreaChart
        data={revenueData}
        xKey="period"
        yKeys={['revenue', 'expenses', 'profit']}
        title={`${period.charAt(0).toUpperCase() + period.slice(1)} Revenue Analytics`}
        height={400}
      />
    </div>
  );
};

// System Performance Metrics
export const SystemMetrics = ({ metricsData }) => {
  return (
    <div className="system-metrics">
      <DashboardGrid columns={3}>
        <KPICard
          title="System Uptime"
          value="99.9%"
          change="+0.1%"
          trend="up"
          icon="⚡"
          color="success"
        />
        <KPICard
          title="Response Time"
          value="120ms"
          change="-5ms"
          trend="up"
          icon="⏱️"
          color="primary"
        />
        <KPICard
          title="Active Users"
          value="1,247"
          change="+23"
          trend="up"
          icon="👥"
          color="info"
        />
      </DashboardGrid>
      
      <LineChart
        data={metricsData}
        xKey="timestamp"
        yKeys={['cpu', 'memory', 'diskUsage']}
        title="System Resource Usage"
        height={300}
      />
    </div>
  );
};

export default {
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
};