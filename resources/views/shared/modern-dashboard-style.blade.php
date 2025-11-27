{{-- SHARED MODERN DASHBOARD STYLES --}}
<style>
:root {
  --primary: #083E40;
  --primary-light: #0a4f52;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;
  --purple: #8b5cf6;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-900: #111827;
}

* { box-sizing: border-box; }

body {
  background: var(--gray-50) !important;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
  color: var(--gray-900);
  font-size: 14px;
  line-height: 1.5;
}

.container { max-width: 1400px; margin: 0 auto; padding: 0 1.5rem; }

/* PAGE HEADER */
.page-header {
  background: linear-gradient(135deg, var(--header-color-1, var(--primary)) 0%, var(--header-color-2, var(--primary-light)) 100%);
  padding: 2.5rem 0;
  margin-bottom: 2rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
}

.page-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  border-radius: 50%;
}

.page-title {
  color: white;
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.5px;
}

.page-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 16px;
  margin: 0;
  font-weight: 400;
}

.page-timestamp {
  color: rgba(255, 255, 255, 0.7);
  font-size: 13px;
  margin-top: 0.5rem;
}

/* KPI CARDS */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: white;
  border-radius: 14px;
  padding: 1.75rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-left: 4px solid var(--card-color, var(--primary));
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.kpi-card::after {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, var(--card-color, var(--primary)) 0%, transparent 70%);
  opacity: 0.05;
  border-radius: 50%;
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.kpi-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--gray-600);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  background: var(--icon-bg, var(--gray-100));
  color: var(--icon-color, var(--primary));
}

.kpi-value {
  font-size: 36px;
  font-weight: 700;
  color: var(--gray-900);
  line-height: 1;
  margin-bottom: 0.5rem;
}

.kpi-trend {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 13px;
  font-weight: 500;
  color: var(--gray-600);
}

/* ACTION BUTTONS */
.action-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.action-btn {
  background: white;
  border: 2px solid var(--gray-200);
  border-radius: 12px;
  padding: 1.25rem;
  text-align: center;
  text-decoration: none;
  color: var(--gray-900);
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.action-btn:hover {
  background: var(--btn-hover-color, var(--primary));
  color: white;
  transform: translateY(-2px);
  border-color: var(--btn-hover-color, var(--primary));
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.action-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  transition: all 0.2s;
}

.action-btn:hover .action-icon {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

/* CONTENT CARDS */
.content-card {
  background: white;
  border-radius: 14px;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid var(--gray-200);
  margin-bottom: 1.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--gray-100);
}

.card-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-action {
  font-size: 13px;
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.card-action:hover { color: var(--primary-light); }

/* SEARCH BOX */
.search-box {
  position: relative;
  margin-bottom: 1.5rem;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid var(--gray-300);
  border-radius: 12px;
  font-size: 15px;
  transition: all 0.2s;
  background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'%3E%3C/path%3E%3C/svg%3E") no-repeat 12px center;
  background-size: 20px;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

/* STATUS BADGES */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.875rem;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.status-success { background: #d1fae5; color: #065f46; }
.status-warning { background: #fef3c7; color: #92400e; }
.status-danger { background: #fee2e2; color: #991b1b; }
.status-info { background: #dbeafe; color: #1e40af; }
.status-purple { background: #ede9fe; color: #5b21b6; }

/* DOCUMENT ITEMS */
.doc-list { display: flex; flex-direction: column; gap: 0.75rem; }

.doc-item {
  padding: 1.25rem;
  border: 1px solid var(--gray-200);
  border-radius: 10px;
  background: white;
  transition: all 0.2s;
}

.doc-item:hover {
  background: var(--gray-50);
  border-color: var(--primary);
  transform: translateX(4px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.doc-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.75rem;
}

.doc-number {
  font-weight: 600;
  color: var(--gray-900);
  font-size: 15px;
}

.doc-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.75rem;
  font-size: 13px;
  color: var(--gray-600);
}

.doc-detail-item strong {
  display: block;
  color: var(--gray-700);
  margin-bottom: 0.25rem;
}

/* QUICK STATS */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-item {
  text-align: center;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  border: 1px solid var(--gray-200);
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: var(--stat-color, var(--primary));
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 13px;
  color: var(--gray-600);
  font-weight: 500;
}

/* EMPTY STATE */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--gray-600);
}

.empty-icon {
  font-size: 64px;
  color: var(--gray-300);
  margin-bottom: 1rem;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .kpi-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
  .action-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
}

@media (max-width: 768px) {
  .page-title { font-size: 24px; }
  .kpi-grid, .action-grid, .quick-stats { grid-template-columns: 1fr; }
  .kpi-value { font-size: 28px; }
  .doc-details { grid-template-columns: 1fr; }
}

/* ANIMATIONS */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-in { animation: fadeInUp 0.5s ease-out forwards; }
.fade-in:nth-child(1) { animation-delay: 0.05s; }
.fade-in:nth-child(2) { animation-delay: 0.1s; }
.fade-in:nth-child(3) { animation-delay: 0.15s; }
.fade-in:nth-child(4) { animation-delay: 0.2s; }
.fade-in:nth-child(5) { animation-delay: 0.25s; }
.fade-in:nth-child(6) { animation-delay: 0.3s; }
</style>