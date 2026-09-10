@php
    $perPage = $paginator->perPage();
    $total = $paginator->total();
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();
    $queryParams = request()->except(['page', 'per_page']);
@endphp

@if($paginator->total() > 0)
    <div class="perpage-top-bar">
        <label class="perpage-top-label">Baris per halaman:</label>
        <select class="perpage-top-select" onchange="changePerPageTop(this.value)">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            <option value="all" {{ request('per_page') === 'all' || $perPage >= $total ? 'selected' : '' }}>Semua</option>
        </select>
        <span class="perpage-top-info">
            Menampilkan {{ $from ?? 0 }} - {{ $to ?? 0 }} dari {{ number_format($total, 0, ',', '.') }} hasil
        </span>
    </div>

    <style>
        .perpage-top-bar {
            padding: 10px 16px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .perpage-top-label {
            font-weight: 500;
            color: #495057;
            margin: 0;
            font-size: 13px;
        }
        .perpage-top-select {
            padding: 6px 10px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            font-size: 13px;
            min-width: 65px;
            color: #495057;
        }
        .perpage-top-info {
            color: #6c757d;
            font-size: 13px;
            white-space: nowrap;
        }
        .dark .perpage-top-bar {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        .dark .perpage-top-label {
            color: #cbd5e1 !important;
        }
        .dark .perpage-top-select {
            background: #334155 !important;
            border-color: #475569 !important;
            color: #f1f5f9 !important;
        }
        .dark .perpage-top-info {
            color: #94a3b8 !important;
        }
    </style>

    <script>
        function changePerPageTop(value) {
            const params = new URLSearchParams(window.location.search);
            params.set('per_page', value);
            params.set('page', '1');
            window.location.href = window.location.pathname + '?' + params.toString();
        }
    </script>
@endif
