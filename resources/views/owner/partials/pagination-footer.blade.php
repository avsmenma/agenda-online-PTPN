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

