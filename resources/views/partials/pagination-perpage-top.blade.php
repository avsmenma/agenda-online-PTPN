@php
    $perPage = $paginator->perPage();
    $total = $paginator->total();
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();
    $queryParams = request()->except(['page', 'per_page']);
@endphp

@if($paginator->total() > 0)
    <div class="perpage-top-bar"
        style="padding: 10px 16px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
        <label style="font-weight: 500; color: #495057; margin: 0; font-size: 13px;">Baris per halaman:</label>
        <select class="perpage-top-select" onchange="changePerPageTop(this.value)"
            style="padding: 6px 10px; border: 1px solid #dee2e6; border-radius: 8px; background: white; cursor: pointer; font-size: 13px; min-width: 65px;">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            <option value="all" {{ $perPage >= $total ? 'selected' : '' }}>Semua</option>
        </select>
        <span style="color: #6c757d; font-size: 13px; white-space: nowrap;">
            Menampilkan {{ $from ?? 0 }} - {{ $to ?? 0 }} dari {{ number_format($total, 0, ',', '.') }} hasil
        </span>
    </div>

    <script>
        function changePerPageTop(value) {
            const params = new URLSearchParams(window.location.search);
            params.set('per_page', value);
            params.set('page', '1');
            window.location.href = window.location.pathname + '?' + params.toString();
        }
    </script>
@endif