@php
    $currentPage = $paginator->currentPage();
    $perPage = $paginator->perPage();
    $total = $paginator->total();
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();
    $lastPage = $paginator->lastPage();
    
    // Get current query parameters
    $queryParams = request()->except(['page', 'per_page']);
@endphp

<style>
.pagination-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  border-radius: 0 0 12px 12px;
  margin-top: 1rem;
}

.pagination-footer-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.pagination-label {
  font-weight: 500;
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
  white-space: nowrap;
}

.pagination-select {
  padding: 0.5rem 2rem 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  font-size: 0.875rem;
  color: #334155;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  min-width: 70px;
}

.pagination-select:focus {
  outline: none;
  border-color: #083E40;
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

.pagination-summary {
  color: #64748b;
  font-size: 0.875rem;
  white-space: nowrap;
}

.pagination-footer-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  padding: 0;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #083E40;
  color: white;
  border-color: #083E40;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f1f5f9;
}

.pagination-page-input {
  width: 60px;
  height: 36px;
  padding: 0 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  text-align: center;
  font-size: 0.875rem;
  font-weight: 500;
  color: #334155;
  background: white;
}

.pagination-page-input:focus {
  outline: none;
  border-color: #083E40;
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

.pagination-page-input::-webkit-outer-spin-button,
.pagination-page-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.pagination-page-input[type=number] {
  -moz-appearance: textfield;
}

.pagination-total-pages {
  color: #64748b;
  font-size: 0.875rem;
  white-space: nowrap;
}

@media (max-width: 640px) {
  .pagination-footer {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }
  
  .pagination-footer-left {
    width: 100%;
    justify-content: center;
    text-align: center;
  }
  
  .pagination-footer-right {
    width: 100%;
    justify-content: center;
  }
  
  .pagination-summary {
    width: 100%;
    text-align: center;
    order: 3;
  }
}
</style>

<div class="pagination-footer">
  <!-- Left Section: Rows per page & Summary -->
  <div class="pagination-footer-left">
    <label class="pagination-label">Baris per halaman:</label>
    <select class="pagination-select" onchange="changePerPage(this.value)">
      <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
      <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
    </select>
    
    <span class="pagination-summary">
      Menampilkan {{ $from ?? 0 }} - {{ $to ?? 0 }} dari {{ number_format($total, 0, ',', '.') }} hasil
    </span>
  </div>

  <!-- Right Section: Navigation Controls -->
  <div class="pagination-footer-right">
    <!-- Previous Button -->
    <button class="pagination-btn" 
            onclick="goToPage({{ $currentPage - 1 }})"
            {{ $currentPage == 1 ? 'disabled' : '' }}
            title="Halaman sebelumnya">
      <i class="fas fa-chevron-left"></i>
    </button>

    <!-- Page Number Input -->
    <input type="number" 
           class="pagination-page-input" 
           value="{{ $currentPage }}" 
           min="1" 
           max="{{ $lastPage }}"
           onchange="goToPage(this.value)"
           onkeypress="if(event.key === 'Enter') goToPage(this.value)">
    
    <span class="pagination-total-pages">dari {{ $lastPage }} halaman</span>

    <!-- Next Button -->
    <button class="pagination-btn" 
            onclick="goToPage({{ $currentPage + 1 }})"
            {{ $currentPage >= $lastPage ? 'disabled' : '' }}
            title="Halaman berikutnya">
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>
</div>

<script>
function changePerPage(value) {
  const url = new URL(window.location.href);
  // Preserve all query parameters except page (reset to 1)
  url.searchParams.set('per_page', value);
  url.searchParams.set('page', '1');
  window.location.href = url.toString();
}

function goToPage(page) {
  const url = new URL(window.location.href);
  let pageNum = parseInt(page);
  const lastPage = {{ $lastPage }};
  
  // Validate page number
  if (isNaN(pageNum) || pageNum < 1) {
    pageNum = 1;
  } else if (pageNum > lastPage) {
    pageNum = lastPage;
  }
  
  // Preserve all query parameters
  url.searchParams.set('page', pageNum);
  window.location.href = url.toString();
}

// Handle Enter key on page input
document.addEventListener('DOMContentLoaded', function() {
  const pageInputs = document.querySelectorAll('.pagination-page-input');
  pageInputs.forEach(input => {
    input.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        goToPage(this.value);
      }
    });
  });
});
</script>





