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

@if($paginator->total() > 0)
<div class="pagination-enhanced-wrapper" style="margin-top: 24px; padding: 20px; background: #f8f9fa; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
  <!-- Left Section: Rows per page & Summary -->
  <div class="pagination-enhanced-left" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
    <label style="font-weight: 500; color: #495057; margin: 0;">Baris per halaman:</label>
    <select class="pagination-enhanced-select" onchange="changePerPageEnhanced(this.value)" style="padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 8px; background: white; cursor: pointer; font-size: 14px; min-width: 70px;">
      <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
      <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
    </select>
    
    <span class="pagination-enhanced-summary" style="color: #6c757d; font-size: 14px; white-space: nowrap;">
      Menampilkan {{ $from ?? 0 }} - {{ $to ?? 0 }} dari {{ number_format($total, 0, ',', '.') }} hasil
    </span>
  </div>

  <!-- Right Section: Navigation Controls -->
  <div class="pagination-enhanced-right" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
    <!-- Previous Button -->
    <button class="pagination-enhanced-btn" 
            onclick="goToPageEnhanced({{ $currentPage - 1 }})"
            {{ $currentPage == 1 ? 'disabled' : '' }}
            style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background: {{ $currentPage == 1 ? '#e0e0e0' : 'white' }}; color: {{ $currentPage == 1 ? '#9e9e9e' : '#083E40' }}; border-radius: 10px; cursor: {{ $currentPage == 1 ? 'not-allowed' : 'pointer' }}; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; min-width: 44px; height: 44px;">
      <i class="fa-solid fa-chevron-left"></i>
    </button>

    <!-- Page Number Input -->
    <input type="number" 
           class="pagination-enhanced-page-input" 
           value="{{ $currentPage }}" 
           min="1" 
           max="{{ $lastPage }}"
           onchange="goToPageEnhanced(this.value)"
           onkeypress="if(event.key === 'Enter') goToPageEnhanced(this.value)"
           style="padding: 10px 12px; border: 2px solid rgba(8, 62, 64, 0.15); border-radius: 10px; background: white; color: #083E40; font-size: 14px; font-weight: 600; text-align: center; width: 60px; height: 44px; outline: none; transition: all 0.3s ease;">
    
    <span style="color: #6c757d; font-size: 14px; white-space: nowrap;">dari {{ $lastPage }} halaman</span>

    <!-- Next Button -->
    <button class="pagination-enhanced-btn" 
            onclick="goToPageEnhanced({{ $currentPage + 1 }})"
            {{ $currentPage >= $lastPage ? 'disabled' : '' }}
            style="padding: 10px 16px; border: 2px solid rgba(8, 62, 64, 0.15); background: {{ $currentPage >= $lastPage ? '#e0e0e0' : 'white' }}; color: {{ $currentPage >= $lastPage ? '#9e9e9e' : '#083E40' }}; border-radius: 10px; cursor: {{ $currentPage >= $lastPage ? 'not-allowed' : 'pointer' }}; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; min-width: 44px; height: 44px;">
      <i class="fa-solid fa-chevron-right"></i>
    </button>
  </div>
</div>

<script>
function changePerPageEnhanced(value) {
  const url = new URL(window.location.href);
  // Preserve all query parameters except page (reset to 1)
  const params = new URLSearchParams(window.location.search);
  params.set('per_page', value);
  params.set('page', '1');
  window.location.href = window.location.pathname + '?' + params.toString();
}

function goToPageEnhanced(page) {
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
  const params = new URLSearchParams(window.location.search);
  params.set('page', pageNum);
  window.location.href = window.location.pathname + '?' + params.toString();
}

// Add focus and hover effects for page input
document.addEventListener('DOMContentLoaded', function() {
  const pageInputs = document.querySelectorAll('.pagination-enhanced-page-input');
  pageInputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.style.borderColor = '#083E40';
      this.style.boxShadow = '0 0 0 3px rgba(8, 62, 64, 0.1)';
    });
    input.addEventListener('blur', function() {
      this.style.borderColor = 'rgba(8, 62, 64, 0.15)';
      this.style.boxShadow = 'none';
    });
  });
});

// Add hover effects
document.addEventListener('DOMContentLoaded', function() {
  const buttons = document.querySelectorAll('.pagination-enhanced-btn:not(:disabled)');
  buttons.forEach(btn => {
    btn.addEventListener('mouseenter', function() {
      if (!this.disabled && !this.classList.contains('active')) {
        this.style.background = '#f0f0f0';
        this.style.borderColor = 'rgba(8, 62, 64, 0.3)';
      }
    });
    btn.addEventListener('mouseleave', function() {
      if (!this.disabled && !this.classList.contains('active')) {
        this.style.background = 'white';
        this.style.borderColor = 'rgba(8, 62, 64, 0.15)';
      }
    });
  });
});
</script>

<style>
@media (max-width: 768px) {
  .pagination-enhanced-wrapper {
    flex-direction: column;
    align-items: stretch !important;
  }
  
  .pagination-enhanced-left,
  .pagination-enhanced-right {
    justify-content: center;
    width: 100%;
  }
  
  .pagination-enhanced-summary {
    text-align: center;
    width: 100%;
    margin-top: 8px;
  }
}
</style>
@endif



