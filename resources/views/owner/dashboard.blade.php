@extends('layouts/app')

@section('content')
<style>
/* Modern Command Center Dashboard */
:root {
  --primary-color: #083E40;
  --success-color: #889717;
  --warning-color: #ffc107;
  --danger-color: #dc3545;
  --info-color: #0a4f52;
  --text-primary: #1a202c;
  --text-secondary: #4a5568;
  --text-muted: #718096;
  --border-color: #e2e8f0;
  --bg-light: #f8fafc;
}

body {
  background: var(--bg-light) !important;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.dark body {
  background: #0f172a !important; /* slate-900 */
  color: #f1f5f9; /* slate-100 */
}

/* Dashboard Header - Modern Gradient & Pattern - Flush Left Alignment */
.dashboard-header {
  position: relative;
  background: linear-gradient(135deg, #0f4c5c 0%, #1e7e8d 50%, #2a9daf 100%);
  color: white;
  padding: 3rem 0;
  margin-bottom: 2rem;
  margin-left: 0;
  margin-right: 0;
  padding-left: 0;
  padding-right: 0;
  border-radius: 1.5rem;
  box-shadow: 0 10px 25px rgba(15, 76, 92, 0.3), 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  width: 100%;
}

.dashboard-header .container {
  position: relative;
  z-index: 1;
}

/* Pattern Overlay - Subtle Abstract Pattern */
.dashboard-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.08) 1px, transparent 1px),
    radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.08) 1px, transparent 1px),
    radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.05) 2px, transparent 2px);
  background-size: 60px 60px, 80px 80px, 100px 100px;
  background-position: 0 0, 20px 20px, 40px 40px;
  opacity: 0.6;
  pointer-events: none;
}

/* Additional subtle pattern overlay */
.dashboard-header::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.03) 50%, transparent 70%),
    linear-gradient(-45deg, transparent 30%, rgba(255, 255, 255, 0.03) 50%, transparent 70%);
  background-size: 200px 200px;
  opacity: 0.4;
  pointer-events: none;
}


.header-title {
  font-size: 32px;
  font-weight: 700;
  margin: 0;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 0.875rem;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.header-icon-wrapper:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

.header-icon-wrapper i {
  font-size: 24px;
  color: white;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.header-subtitle {
  font-size: 16px;
  opacity: 0.95;
  margin-top: 12px;
  font-weight: 400;
  letter-spacing: 0.02em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments for header */
@media (max-width: 768px) {
  .dashboard-header {
    padding: 2rem 0;
    border-radius: 1rem;
  }

  .header-title {
    font-size: 24px;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .header-icon-wrapper {
    width: 48px;
    height: 48px;
  }

  .header-icon-wrapper i {
    font-size: 20px;
  }

  .header-subtitle {
    font-size: 14px;
  }
}

/* Smart Analytics Header */
.smart-analytics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.dark .smart-analytics-grid {
  gap: 1.5rem;
}

@media (max-width: 1200px) {
  .smart-analytics-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .smart-analytics-grid {
    grid-template-columns: 1fr;
  }
}

.smart-stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color);
  transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease;
  position: relative;
  overflow: hidden;
}

.dark .smart-stat-card {
  background: #1e293b; /* slate-800 */
  border-color: #334155; /* slate-700 */
}

.smart-stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.smart-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color), var(--info-color));
  opacity: 0;
  transition: opacity 0.2s ease;
}

.smart-stat-card:hover::before {
  opacity: 1;
}

.smart-stat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.smart-stat-content {
  flex: 1;
}

.smart-stat-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.dark .smart-stat-label {
  color: #94a3b8; /* slate-400 */
}

.smart-stat-value {
  font-size: 28px;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.dark .smart-stat-value {
  color: #f1f5f9; /* slate-100 */
}

.smart-stat-trend {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
  margin-top: 0.25rem;
}

.smart-stat-trend.positive {
  color: #10b981;
  background: #d1fae5;
}

.smart-stat-trend.negative {
  color: #ef4444;
  background: #fee2e2;
}

.smart-stat-trend.neutral {
  color: var(--text-muted);
  background: #f3f4f6;
}

.smart-stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}

.smart-stat-icon.total {
  background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
}

.smart-stat-icon.proses {
  background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.smart-stat-icon.selesai {
  background: linear-gradient(135deg, #889717 0%, #6b8e23 100%);
}

.smart-stat-icon.nilai {
  background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
}

/* Sparkline Chart Background */
.sparkline-container {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 40px;
  opacity: 0.1;
  overflow: hidden;
}

.sparkline-svg {
  width: 100%;
  height: 100%;
}

/* Control Bar */
.control-bar {
  background: white;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.dark .control-bar {
  background: #1e293b; /* slate-800 */
  border-color: #334155; /* slate-700 */
}

.control-bar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  min-width: 0;
}

.control-bar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.search-input-modern {
  flex: 1;
  min-width: 300px;
  padding: 10px 16px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
  background: white;
  color: var(--text-primary);
}

.dark .search-input-modern {
  background: #0f172a; /* slate-900 */
  border-color: #334155; /* slate-700 */
  color: #f1f5f9; /* slate-100 */
}

.search-input-modern:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

.filter-select {
  padding: 10px 16px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

/* View Switcher */
.view-switcher {
  display: inline-flex;
  background: var(--bg-light);
  border-radius: 8px;
  padding: 4px;
  gap: 4px;
}

.view-switcher-btn {
  padding: 8px 16px;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.view-switcher-btn:hover {
  background: rgba(8, 62, 64, 0.05);
  color: var(--primary-color);
}

.view-switcher-btn.active {
  background: white;
  color: var(--primary-color);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Smart Card View */
.card-view-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 1.5rem;
}

.dark .card-view-container {
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .card-view-container {
    grid-template-columns: 1fr;
  }
}

.smart-document-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color);
  transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  user-select: text; /* Allow text selection */
  -webkit-user-select: text;
  -moz-user-select: text;
  -ms-user-select: text;
}

.dark .smart-document-card {
  background: #1e293b; /* slate-800 */
  border-color: #334155; /* slate-700 */
}

.smart-document-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

/* Urgency Styling */
.smart-document-card.overdue {
  border-left: 4px solid var(--danger-color);
  background: #fef2f2;
}

.smart-document-card.overdue::after {
  content: 'TERLAMBAT';
  position: absolute;
  top: 12px;
  right: 12px;
  background: var(--danger-color);
  color: white;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.5px;
  animation: pulse-overdue 2s infinite;
}

@keyframes pulse-overdue {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.smart-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.smart-card-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
  transition: color 0.3s ease;
  cursor: text; /* Show text cursor on title */
  user-select: text;
}

.dark .smart-card-title {
  color: #f1f5f9; /* slate-100 */
}

.smart-card-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  transition: color 0.3s ease;
  cursor: text; /* Show text cursor on subtitle */
  user-select: text;
}

.dark .smart-card-subtitle {
  color: #94a3b8; /* slate-400 */
}

.smart-card-value {
  font-size: 24px;
  font-weight: 700;
  color: var(--success-color);
  margin: 1rem 0;
  cursor: text; /* Show text cursor on value */
  user-select: text;
  -webkit-user-select: text;
  -moz-user-select: text;
  -ms-user-select: text;
}

.smart-card-info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 0.75rem;
  font-size: 14px;
  color: var(--text-secondary);
}

.smart-card-info-row span:not(.user-avatar) {
  cursor: text; /* Show text cursor on info text */
  user-select: text;
  -webkit-user-select: text;
  -moz-user-select: text;
  -ms-user-select: text;
}

.smart-card-info-row i {
  width: 16px;
  color: var(--text-muted);
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-color), var(--info-color));
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 12px;
  margin-left: 8px;
}

/* Workflow Stepper */
.workflow-stepper {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.stepper-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  margin-bottom: 0.75rem;
  letter-spacing: 0.5px;
}

.stepper-steps {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  padding: 0 8px;
}

.stepper-steps::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--border-color);
  z-index: 0;
}

.stepper-step {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: white;
  border: 2px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted);
  position: relative;
  z-index: 1;
  transition: all 0.3s ease;
}

.stepper-step.completed {
  background: var(--success-color);
  border-color: var(--success-color);
  color: white;
}

.stepper-step.active {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: white;
  box-shadow: 0 0 0 4px rgba(8, 62, 64, 0.1);
}

.stepper-step-label {
  position: absolute;
  top: 100%;
  margin-top: 8px;
  font-size: 9px;
  font-weight: 600;
  color: var(--text-muted);
  white-space: nowrap;
  text-align: center;
  width: 60px;
  left: 50%;
  transform: translateX(-50%);
}

.stepper-step.completed .stepper-step-label,
.stepper-step.active .stepper-step-label {
  color: var(--text-primary);
}

/* Table View */
.table-view-container {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color);
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.dark .table-view-container {
  background: #1e293b; /* slate-800 */
  border-color: #334155; /* slate-700 */
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: var(--bg-light);
  border-bottom: 2px solid var(--border-color);
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.dark .modern-table thead {
  background: #0f172a; /* slate-900 */
  border-bottom-color: #334155; /* slate-700 */
}

.modern-table th {
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.3s ease;
}

.dark .modern-table th {
  color: #94a3b8; /* slate-400 */
}

.modern-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  font-size: 14px;
  color: var(--text-primary);
  transition: color 0.3s ease, border-color 0.3s ease;
}

.dark .modern-table td {
  color: #f1f5f9; /* slate-100 */
  border-bottom-color: #334155; /* slate-700 */
}

.modern-table tbody tr {
  transition: background 0.2s ease;
}

.modern-table tbody tr:hover {
  background: var(--bg-light);
  transition: background-color 0.2s ease;
}

.dark .modern-table tbody tr:hover {
  background: #0f172a; /* slate-900 */
}

.modern-table tbody tr.overdue-row {
  background: #fef2f2;
}

.modern-table tbody tr.overdue-row:hover {
  background: #fee2e2;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.proses {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.selesai {
  background: #d1fae5;
  color: #065f46;
}

.mini-progress-bar {
  width: 100px;
  height: 6px;
  background: var(--border-color);
  border-radius: 3px;
  overflow: hidden;
}

.mini-progress-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s ease;
}

.action-btn {
  padding: 6px 12px;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: #0a4f52;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* View Toggle */
.view-container {
  display: none;
}

.view-container.active {
  display: block;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: background-color 0.3s ease;
}

.dark .empty-state {
  background: #1e293b; /* slate-800 */
}

.empty-state-icon {
  font-size: 64px;
  color: var(--text-muted);
  margin-bottom: 1rem;
}

.empty-state-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.dark .empty-state-title {
  color: #f1f5f9; /* slate-100 */
}

.empty-state-text {
  font-size: 14px;
  color: var(--text-muted);
  margin-bottom: 1.5rem;
  transition: color 0.3s ease;
}

.dark .empty-state-text {
  color: #94a3b8; /* slate-400 */
}

/* Pagination Footer */
.pagination-footer {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin-top: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.dark .pagination-footer {
  background: #1e293b; /* slate-800 */
  border-color: #334155; /* slate-700 */
}

.pagination-footer-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.pagination-footer-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-label {
  font-size: 14px;
  color: var(--text-secondary);
  white-space: nowrap;
  transition: color 0.3s ease;
}

.dark .pagination-label {
  color: #94a3b8; /* slate-400 */
}

.pagination-select {
  padding: 6px 32px 6px 12px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 14px;
  background: white;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234a5568' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 8px center;
  background-size: 12px;
}

.dark .pagination-select {
  background: #0f172a; /* slate-900 */
  border-color: #334155; /* slate-700 */
  color: #f1f5f9; /* slate-100 */
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%94a3b8' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
}

.pagination-select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

.pagination-summary {
  font-size: 14px;
  color: var(--text-secondary);
  white-space: nowrap;
  transition: color 0.3s ease;
}

.dark .pagination-summary {
  color: #94a3b8; /* slate-400 */
}

.pagination-nav {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 8px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: white;
  color: var(--text-secondary);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dark .pagination-btn {
  background: #0f172a; /* slate-900 */
  border-color: #334155; /* slate-700 */
  color: #94a3b8; /* slate-400 */
}

.pagination-btn:hover:not(:disabled) {
  background: var(--bg-light);
  border-color: var(--primary-color);
  color: var(--primary-color);
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-btn.active {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: white;
}

.pagination-btn.active:hover {
  background: #0a4f52;
}

.pagination-page-input {
  width: 50px;
  height: 32px;
  padding: 0 8px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 14px;
  text-align: center;
  color: var(--text-primary);
  background: white;
  transition: all 0.2s ease, background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
}

.dark .pagination-page-input {
  background: #0f172a; /* slate-900 */
  border-color: #334155; /* slate-700 */
  color: #f1f5f9; /* slate-100 */
}

.pagination-page-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

.pagination-total-pages {
  font-size: 14px;
  color: var(--text-muted);
  white-space: nowrap;
  margin-left: 0.5rem;
  transition: color 0.3s ease;
}

.dark .pagination-total-pages {
  color: #94a3b8; /* slate-400 */
}

@media (max-width: 768px) {
  .pagination-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-footer-left,
  .pagination-footer-right {
    justify-content: center;
  }

  .pagination-summary {
    text-align: center;
  }
}
</style>

<!-- Dashboard Header - Modern Gradient & Pattern -->
<div class="dashboard-header">
  <div class="container">
    <h1 class="header-title">
      <div class="header-icon-wrapper">
        <i class="fas fa-chart-line"></i>
      </div>
      <span>Dashboard Owner</span>
    </h1>
    <p class="header-subtitle mb-0">Pantau dan kelola semua dokumen perusahaan dengan mudah</p>
  </div>
</div>

<!-- Main Content Container - Flush Left Alignment -->
<div class="container">

  <!-- Smart Analytics Header -->
  <div class="smart-analytics-grid">
    <!-- Card 1: Total Dokumen -->
    <div class="smart-stat-card">
      <div class="smart-stat-header">
        <div class="smart-stat-content">
          <div class="smart-stat-label">Total Dokumen</div>
          <div class="smart-stat-value">{{ number_format($totalDokumen ?? 0, 0, ',', '.') }}</div>
          <div class="smart-stat-trend {{ ($totalDokumenTrend ?? 0) >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ ($totalDokumenTrend ?? 0) >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($totalDokumenTrend ?? 0) }}% minggu ini
          </div>
        </div>
        <div class="smart-stat-icon total">
          <i class="fas fa-file-alt"></i>
        </div>
      </div>
      <div class="sparkline-container">
        <svg class="sparkline-svg" viewBox="0 0 100 40" preserveAspectRatio="none">
          <polyline points="0,30 20,25 40,20 60,15 80,10 100,5" fill="none" stroke="#083E40" stroke-width="2"/>
        </svg>
      </div>
    </div>

    <!-- Card 2: Dokumen Proses -->
    <div class="smart-stat-card">
      <div class="smart-stat-header">
        <div class="smart-stat-content">
          <div class="smart-stat-label">Dokumen Proses</div>
          <div class="smart-stat-value">{{ number_format($dokumenProses ?? 0, 0, ',', '.') }}</div>
          <div class="smart-stat-trend {{ ($dokumenProsesTrend ?? 0) >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ ($dokumenProsesTrend ?? 0) >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($dokumenProsesTrend ?? 0) }}% minggu ini
          </div>
        </div>
        <div class="smart-stat-icon proses">
          <i class="fas fa-clock"></i>
        </div>
      </div>
      <div class="sparkline-container">
        <svg class="sparkline-svg" viewBox="0 0 100 40" preserveAspectRatio="none">
          <polyline points="0,20 20,18 40,22 60,15 80,12 100,10" fill="none" stroke="#ffc107" stroke-width="2"/>
        </svg>
      </div>
    </div>

    <!-- Card 3: Dokumen Selesai -->
    <div class="smart-stat-card">
      <div class="smart-stat-header">
        <div class="smart-stat-content">
          <div class="smart-stat-label">Dokumen Selesai</div>
          <div class="smart-stat-value">{{ number_format($dokumenSelesai ?? 0, 0, ',', '.') }}</div>
          <div class="smart-stat-trend {{ ($dokumenSelesaiTrend ?? 0) >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ ($dokumenSelesaiTrend ?? 0) >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($dokumenSelesaiTrend ?? 0) }}% minggu ini
          </div>
        </div>
        <div class="smart-stat-icon selesai">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="sparkline-container">
        <svg class="sparkline-svg" viewBox="0 0 100 40" preserveAspectRatio="none">
          <polyline points="0,35 20,30 40,25 60,20 80,15 100,10" fill="none" stroke="#889717" stroke-width="2"/>
        </svg>
      </div>
    </div>

    <!-- Card 4: Total Nilai (Rp) -->
    <div class="smart-stat-card">
      <div class="smart-stat-header">
        <div class="smart-stat-content">
          <div class="smart-stat-label">Total Nilai (Rp)</div>
          <div class="smart-stat-value" style="font-size: 22px;">Rp{{ number_format($totalNilai ?? 0, 0, ',', '.') }}</div>
          <div class="smart-stat-trend {{ ($totalNilaiTrend ?? 0) >= 0 ? 'positive' : 'negative' }}">
            <i class="fas fa-arrow-{{ ($totalNilaiTrend ?? 0) >= 0 ? 'up' : 'down' }}"></i>
            {{ abs($totalNilaiTrend ?? 0) }}% minggu ini
          </div>
        </div>
        <div class="smart-stat-icon nilai">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>
      <div class="sparkline-container">
        <svg class="sparkline-svg" viewBox="0 0 100 40" preserveAspectRatio="none">
          <polyline points="0,30 20,28 40,25 60,22 80,18 100,15" fill="none" stroke="#0a4f52" stroke-width="2"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- Control Bar & View Toggles -->
  <div class="control-bar">
    <div class="control-bar-left">
      <form method="GET" action="{{ url('/owner/dashboard') }}" class="d-flex align-items-center gap-2" style="flex: 1; min-width: 0;">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
        <input type="text"
               name="search"
               class="search-input-modern"
               value="{{ $search ?? '' }}"
               placeholder="Cari dokumen...">
        <select name="status" class="filter-select" style="min-width: 150px;">
          <option value="">Semua Status</option>
          <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Proses</option>
          <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
        </select>
        <button type="submit" class="action-btn">
          <i class="fas fa-search me-1"></i> Cari
        </button>
        @if(isset($search) && !empty($search))
        <a href="{{ url('/owner/dashboard') }}" class="action-btn" style="background: var(--text-muted);">
          <i class="fas fa-times me-1"></i> Atur Ulang
        </a>
        @endif
      </form>
    </div>
    <div class="control-bar-right">
      <div class="view-switcher">
        <button class="view-switcher-btn active" data-view="card" onclick="switchView('card')">
          <i class="fas fa-th"></i> Kartu
        </button>
        <button class="view-switcher-btn" data-view="table" onclick="switchView('table')">
          <i class="fas fa-table"></i> Tabel
        </button>
      </div>
    </div>
  </div>

  <!-- Card View -->
  <div id="cardView" class="view-container active">
    @if($documents->count() == 0)
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="empty-state-title">Tidak ada dokumen</div>
        <div class="empty-state-text">
          @if(isset($search) && !empty($search))
            Tidak ada dokumen yang sesuai dengan pencarian "{{ $search }}"
          @else
            Dokumen akan ditampilkan di sini ketika tersedia
          @endif
        </div>
      </div>
    @else
      <div class="card-view-container">
        @foreach($documents as $dokumen)
          <div class="smart-document-card {{ $dokumen['is_overdue'] ? 'overdue' : '' }}"
               data-document-url="{{ url('/owner/workflow/' . $dokumen['id']) }}"
               onclick="handleCardClick(event, '{{ url('/owner/workflow/' . $dokumen['id']) }}')">
            
            <div class="smart-card-header">
              <div>
                <div class="smart-card-title">
                  {{ $dokumen['nomor_agenda'] }}
                </div>
                <div class="smart-card-subtitle">
                  SPP: {{ $dokumen['nomor_spp'] }}
                </div>
              </div>
            </div>

            <div class="smart-card-value">
              Rp {{ number_format($dokumen['nilai_rupiah'], 0, ',', '.') }}
            </div>

            <div class="smart-card-info-row">
              <i class="fas fa-user"></i>
              <span>Posisi:</span>
              <span class="user-avatar">
                {{ substr($dokumen['current_handler_display'] ?? 'N/A', 0, 1) }}
              </span>
              <span>{{ $dokumen['current_handler_display'] ?? 'Belum ada penangan' }}</span>
            </div>

            @if($dokumen['deadline_info'])
            <div class="smart-card-info-row">
              <i class="fas fa-clock"></i>
              <span>Batas Waktu:</span>
              <span class="text-{{ $dokumen['deadline_info']['class'] }}" style="font-weight: 600;">
                {{ $dokumen['deadline_info']['text'] }}
              </span>
            </div>
            @endif

            <!-- Workflow Stepper -->
            <div class="workflow-stepper">
              <div class="stepper-label">Progres Alur Kerja</div>
              <div class="stepper-steps">
                @php
                  $progress = $dokumen['progress_percentage'] ?? 0;
                  $currentStep = min(5, max(1, ceil($progress / 20)));
                @endphp
                @for($i = 1; $i <= 5; $i++)
                  <div class="stepper-step {{ $i <= $currentStep ? ($i == $currentStep ? 'active' : 'completed') : '' }}">
                    {{ $i }}
                    <div class="stepper-step-label">
                      @if($i == 1) Sender
                      @elseif($i == 2) Reviewer
                      @elseif($i == 3) Tax
                      @elseif($i == 4) Accounting
                      @else Payment
                      @endif
                    </div>
                  </div>
                @endfor
              </div>
            </div>

          </div>
        @endforeach
      </div>
    @endif

    <!-- Pagination Footer for Card View -->
    @if($documents->count() > 0)
      @include('owner.partials.pagination-footer', ['paginator' => $documents])
    @endif
  </div>

  <!-- Table View -->
  <div id="tableView" class="view-container">
    @if($documents->count() == 0)
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <div class="empty-state-title">Tidak ada dokumen</div>
        <div class="empty-state-text">
          @if(isset($search) && !empty($search))
            Tidak ada dokumen yang sesuai dengan pencarian "{{ $search }}"
          @else
            Dokumen akan ditampilkan di sini ketika tersedia
          @endif
        </div>
      </div>
    @else
      <div class="table-view-container">
        <table class="modern-table">
          <thead>
            <tr>
              <th>No. Dokumen</th>
              <th>Tgl Masuk</th>
              <th>Nilai (Rp)</th>
              <th>Posisi</th>
              <th>Status</th>
              <th>Progres</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($documents as $dokumen)
              <tr class="clickable-row {{ $dokumen['is_overdue'] ? 'overdue-row' : '' }}" 
                  data-document-url="{{ url('/owner/workflow/' . $dokumen['id']) }}"
                  onclick="handleItemClick(event, '{{ url('/owner/workflow/' . $dokumen['id']) }}')"
                  style="cursor: pointer;">
                <td>
                  <div style="font-weight: 600; color: var(--text-primary);">{{ $dokumen['nomor_agenda'] }}</div>
                  <div class="select-text" style="font-size: 12px; color: var(--text-muted);">{{ $dokumen['nomor_spp'] }}</div>
                </td>
                <td class="select-text" style="color: var(--text-secondary);">{{ $dokumen['tanggal_masuk'] ?? ($dokumen['created_at'] ?? '-') }}</td>
                <td>
                  <div class="select-text" style="font-weight: 700; color: var(--success-color);">
                    Rp {{ number_format($dokumen['nilai_rupiah'], 0, ',', '.') }}
                  </div>
                </td>
                <td>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="user-avatar" style="margin: 0;">
                      {{ substr($dokumen['current_handler_display'] ?? 'N/A', 0, 1) }}
                    </span>
                    <span>{{ $dokumen['current_handler_display'] ?? 'Belum ada penangan' }}</span>
                  </div>
                </td>
                <td>
                  <span class="status-badge {{ $dokumen['progress_percentage'] >= 100 ? 'selesai' : 'proses' }}">
                    {{ $dokumen['progress_percentage'] >= 100 ? 'Selesai' : 'Proses' }}
                  </span>
                </td>
                <td>
                  <div class="mini-progress-bar">
                    <div class="mini-progress-fill" 
                         style="width: {{ $dokumen['progress_percentage'] }}%; background: {{ $dokumen['progress_color'] }};">
                    </div>
                  </div>
                  <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    {{ $dokumen['progress_percentage'] }}%
                  </div>
                </td>
                <td>
                  <button class="action-btn" onclick="event.stopPropagation(); window.location.href='{{ url('/owner/workflow/' . $dokumen['id']) }}'">
                    Lihat
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    <!-- Pagination Footer for Table View -->
    @if($documents->count() > 0)
      @include('owner.partials.pagination-footer', ['paginator' => $documents])
    @endif
  </div>

</div>

<script>
/**
 * Smart Click Handler untuk Card Dokumen
 * Mencegah navigasi jika user sedang melakukan text selection
 */
function handleCardClick(event, url) {
  // Cek apakah ada text yang sedang diseleksi
  const selection = window.getSelection();
  const selectedText = selection.toString().trim();
  
  // Jika ada text yang diseleksi, jangan lakukan navigasi
  if (selectedText.length > 0) {
    event.preventDefault();
    event.stopPropagation();
    return false;
  }
  
  // Cek apakah ini adalah double-click (biasanya untuk select word)
  if (event.detail === 2) {
    // Double-click biasanya untuk select word, tunggu sebentar
    setTimeout(() => {
      const newSelection = window.getSelection();
      if (newSelection.toString().trim().length > 0) {
        // User berhasil select text, jangan navigasi
        return false;
      }
    }, 50);
    return false;
  }
  
  // Cek juga apakah user sedang drag (mouse drag selection)
  if (event.detail === 0 || event.which === 0) {
    // Ini adalah programmatic click atau drag, jangan navigasi
    return false;
  }
  
  // Jika tidak ada selection, lakukan navigasi
  window.location.href = url;
  return true;
}

function switchView(view) {
  // Update buttons
  document.querySelectorAll('.view-switcher-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-view="${view}"]`).classList.add('active');

  // Update views
  document.getElementById('cardView').classList.toggle('active', view === 'card');
  document.getElementById('tableView').classList.toggle('active', view === 'table');

  // Save preference
  localStorage.setItem('dashboardView', view);
}

// Load saved view preference
document.addEventListener('DOMContentLoaded', function() {
  const savedView = localStorage.getItem('dashboardView') || 'card';
  switchView(savedView);
  
  // Tambahkan event listener untuk mencegah navigasi saat text selection
  // Gunakan mousedown untuk deteksi awal selection
  document.querySelectorAll('.smart-document-card, .modern-table tbody tr').forEach(card => {
    let isSelecting = false;
    let startX = 0;
    let startY = 0;
    
    card.addEventListener('mousedown', function(e) {
      isSelecting = false;
      startX = e.clientX;
      startY = e.clientY;
    });
    
    card.addEventListener('mousemove', function(e) {
      // Jika mouse bergerak lebih dari 3px, kemungkinan user sedang drag select
      const deltaX = Math.abs(e.clientX - startX);
      const deltaY = Math.abs(e.clientY - startY);
      if (deltaX > 3 || deltaY > 3) {
        isSelecting = true;
      }
    });
    
    card.addEventListener('mouseup', function(e) {
      // Jika user melakukan drag, set flag
      if (isSelecting) {
        setTimeout(() => {
          const selection = window.getSelection();
          if (selection.toString().trim().length > 0) {
            // User sedang melakukan text selection, jangan navigasi
            e.preventDefault();
            e.stopPropagation();
          }
        }, 10);
      }
    });
  });
});
</script>

@endsection
