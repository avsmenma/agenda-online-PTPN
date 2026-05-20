@extends('layouts.programmer')

@section('title', 'Evaluasi Asisten Virtual')
@section('page_title', 'Evaluasi Asisten Virtual')
@section('page_subtitle', 'Pantau pertanyaan gagal, feedback user, dan test case Asisten Virtual')

@section('content')
<style>
  .ae-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
  }
  .ae-stat {
    padding: 18px;
  }
  .ae-stat label {
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .ae-stat strong {
    color: #0f172a;
    display: block;
    font-size: 26px;
    line-height: 1.1;
    margin-top: 6px;
  }
  .ae-filter {
    padding: 16px;
  }
  .ae-table th {
    color: #64748b;
    font-size: 11px;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .ae-table td {
    color: #1f2937;
    font-size: 13px;
    vertical-align: top;
  }
  .ae-pill {
    border-radius: 999px;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 8px;
  }
  .ae-pill.warn { background:#fff7ed; color:#c2410c; }
  .ae-pill.bad { background:#fff1f2; color:#be123c; }
  .ae-pill.good { background:#ecfdf5; color:#047857; }
  .ae-pill.info { background:#eff6ff; color:#2563eb; }
  .ae-question {
    color: #0f172a;
    font-weight: 800;
    max-width: 360px;
  }
  .ae-muted {
    color: #64748b;
    font-size: 12px;
  }
  .ae-answer {
    color: #475569;
    max-width: 420px;
  }
  .ae-list {
    margin: 0;
    padding-left: 18px;
  }
  .ae-list li {
    margin-bottom: 6px;
  }
</style>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3 mb-4">
  @foreach([
    ['label' => 'Total Log', 'value' => $stats['total'], 'class' => 'info'],
    ['label' => 'Perlu Review', 'value' => $stats['needs_review'], 'class' => 'warn'],
    ['label' => 'Ambigu', 'value' => $stats['ambiguous'], 'class' => 'bad'],
    ['label' => 'Error', 'value' => $stats['error'], 'class' => 'bad'],
    ['label' => 'Sudah Diperbaiki', 'value' => $stats['fixed'], 'class' => 'good'],
  ] as $stat)
    <div class="col-md">
      <div class="ae-card ae-stat">
        <label>{{ $stat['label'] }}</label>
        <strong>{{ number_format($stat['value']) }}</strong>
      </div>
    </div>
  @endforeach
</div>

<div class="ae-card ae-filter mb-4">
  <form method="GET" action="{{ route('programmer.assistant-evaluation') }}" class="row g-3 align-items-end">
    <div class="col-md-2">
      <label class="form-label small fw-bold text-muted">Scope</label>
      <select name="scope" class="form-select form-select-sm">
        <option value="review" @selected(request('scope', 'review') === 'review')>Perlu review</option>
        <option value="all" @selected(request('scope') === 'all')>Semua log</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small fw-bold text-muted">Status</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">Semua status</option>
        @foreach($statuses as $value => $label)
          <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small fw-bold text-muted">Kategori</label>
      <select name="category" class="form-select form-select-sm">
        <option value="">Semua kategori</option>
        @foreach($categories as $value => $label)
          <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small fw-bold text-muted">Feedback</label>
      <select name="feedback" class="form-select form-select-sm">
        <option value="">Semua feedback</option>
        <option value="helpful" @selected(request('feedback') === 'helpful')>Membantu</option>
        <option value="not_helpful" @selected(request('feedback') === 'not_helpful')>Tidak sesuai</option>
        <option value="wrong_answer" @selected(request('feedback') === 'wrong_answer')>Jawaban salah</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small fw-bold text-muted">Cari</label>
      <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Pertanyaan/jawaban">
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-primary btn-sm flex-fill" type="submit">
        <i class="fas fa-filter me-1"></i> Filter
      </button>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('programmer.assistant-evaluation') }}">Reset</a>
    </div>
    <div class="col-12 d-flex justify-content-end">
      <a class="btn btn-outline-success btn-sm" href="{{ route('programmer.assistant-evaluation.export', request()->query()) }}">
        <i class="fas fa-file-csv me-1"></i> Export CSV
      </a>
    </div>
  </form>
</div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="ae-card">
      <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-bold">Log Pertanyaan Perlu Evaluasi</h6>
        <div class="ae-muted">Berisi pertanyaan ambigu, gagal, tanpa data, error, atau feedback tidak sesuai.</div>
      </div>
      <div class="table-responsive">
        <table class="table ae-table align-middle mb-0">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Pertanyaan</th>
              <th>Intent</th>
              <th>Status</th>
              <th>Hasil</th>
              <th>Jawaban</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($interactions as $interaction)
              <tr>
                <td>
                  <div>{{ $interaction->created_at?->format('d/m/Y') }}</div>
                  <div class="ae-muted">{{ $interaction->created_at?->format('H:i') }} | {{ $interaction->latency_ms ?? '-' }} ms</div>
                  <div class="ae-muted">{{ $interaction->user?->name ?? 'User' }} ({{ $interaction->user_role ?? '-' }})</div>
                </td>
                <td>
                  <div class="ae-question">{{ $interaction->question }}</div>
                  @if($interaction->feedback)
                    <div class="mt-2 ae-pill {{ $interaction->feedback === 'helpful' ? 'good' : 'bad' }}">
                      {{ str_replace('_', ' ', $interaction->feedback) }}
                    </div>
                  @endif
                  @if($interaction->feedback_reason)
                    <div class="ae-muted mt-1">{{ \Illuminate\Support\Str::limit($interaction->feedback_reason, 120) }}</div>
                  @endif
                </td>
                <td>
                  <code>{{ $interaction->intent ?? '-' }}</code>
                  <div class="ae-muted">{{ $interaction->internal_service ?? '-' }}</div>
                  <div class="ae-muted">AI: {{ $interaction->ai_called ? ($interaction->ai_provider ?? 'ya') : 'tidak' }}</div>
                </td>
                <td>
                  <span class="ae-pill {{ in_array($interaction->result_status, ['answered'], true) ? 'good' : 'warn' }}">
                    {{ $statuses[$interaction->result_status] ?? $interaction->result_status }}
                  </span>
                  @if($interaction->failure_category)
                    <div class="ae-muted mt-2">{{ $categories[$interaction->failure_category] ?? $interaction->failure_category }}</div>
                  @endif
                  @if($interaction->fixed_at)
                    <div class="ae-pill good mt-2">fixed</div>
                  @endif
                </td>
                <td>
                  <div>{{ number_format($interaction->result_count) }} tampil</div>
                  <div class="ae-muted">{{ $interaction->result_total !== null ? number_format($interaction->result_total) . ' total' : 'total -' }}</div>
                </td>
                <td>
                  <div class="ae-answer">{{ \Illuminate\Support\Str::limit($interaction->answer, 220) }}</div>
                  @if($interaction->failure_reason)
                    <div class="ae-muted mt-2">{{ \Illuminate\Support\Str::limit($interaction->failure_reason, 160) }}</div>
                  @endif
                </td>
                <td>
                  @if(!$interaction->fixed_at)
                    <form method="POST" action="{{ route('programmer.assistant-evaluation.fixed', $interaction) }}">
                      @csrf
                      <input type="text" name="fix_note" class="form-control form-control-sm mb-2" placeholder="Catatan perbaikan">
                      <button type="submit" class="btn btn-sm btn-outline-primary w-100">Tandai fixed</button>
                    </form>
                  @else
                    <div class="ae-muted">
                      {{ $interaction->fixedBy?->name ?? 'Programmer' }}<br>
                      {{ $interaction->fixed_at?->format('d/m/Y H:i') }}
                    </div>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">Belum ada pertanyaan yang perlu review.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="p-3 border-top">
        {{ $interactions->links() }}
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="ae-card mb-4">
      <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-bold">Rekomendasi Otomatis</h6>
      </div>
      <div class="p-3">
        <div class="mb-3">
          <div class="fw-bold small text-muted mb-2">Intent sering gagal</div>
          <ul class="ae-list">
            @forelse($recommendations['by_intent'] as $label => $total)
              <li><code>{{ $label }}</code> muncul {{ $total }} kali</li>
            @empty
              <li>Belum ada intent bermasalah.</li>
            @endforelse
          </ul>
        </div>
        <div class="mb-3">
          <div class="fw-bold small text-muted mb-2">Kategori gagal</div>
          <ul class="ae-list">
            @forelse($recommendations['by_category'] as $label => $total)
              <li>{{ $categories[$label] ?? $label }}: {{ $total }}</li>
            @empty
              <li>Belum ada kategori gagal.</li>
            @endforelse
          </ul>
        </div>
        <div>
          <div class="fw-bold small text-muted mb-2">Pertanyaan berulang</div>
          <ul class="ae-list">
            @forelse($recommendations['frequent_questions'] as $item)
              <li>"{{ $item['question'] }}" ({{ $item['total'] }}x)</li>
            @empty
              <li>Belum ada pola pertanyaan berulang.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <div class="ae-card">
      <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-bold">Test Case Pertanyaan</h6>
        <div class="ae-muted">Jalankan batch test dengan <code>php artisan assistant:test</code>.</div>
      </div>
      <div class="p-3">
        <form method="POST" action="{{ route('programmer.assistant-evaluation.test-cases.store') }}" class="mb-3">
          @csrf
          <input name="name" class="form-control form-control-sm mb-2" placeholder="Nama test case" required>
          <textarea name="question" class="form-control form-control-sm mb-2" rows="2" placeholder="Pertanyaan user" required></textarea>
          <input name="expected_intent" class="form-control form-control-sm mb-2" placeholder="Expected intent, contoh: unpaid_documents">
          <textarea name="expected_params_json" class="form-control form-control-sm mb-2" rows="2" placeholder='Expected params JSON, contoh: {"payment_statuses":["belum_dibayar"]}'></textarea>
          <select name="expected_result_type" class="form-select form-select-sm mb-2">
            <option value="any">Any</option>
            <option value="summary">Summary</option>
            <option value="list">List</option>
            <option value="list_or_empty">List atau kosong</option>
            <option value="no_data">No data</option>
            <option value="clarification">Clarification</option>
          </select>
          <label class="form-check small mb-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            Aktifkan di batch test
          </label>
          <button class="btn btn-sm btn-primary w-100" type="submit">Tambah test case</button>
        </form>

        <div class="table-responsive">
          <table class="table table-sm ae-table">
            <thead>
              <tr>
                <th>Pertanyaan</th>
                <th>Intent</th>
                <th>Last</th>
              </tr>
            </thead>
            <tbody>
              @foreach($testCases as $case)
                <tr>
                  <td>
                    <div class="fw-bold">{{ \Illuminate\Support\Str::limit($case->question, 70) }}</div>
                    <div class="ae-muted">{{ $case->is_active ? 'aktif' : 'nonaktif' }}</div>
                  </td>
                  <td><code>{{ $case->expected_intent ?? '-' }}</code></td>
                  <td>
                    <span class="ae-pill {{ $case->last_status === 'pass' ? 'good' : ($case->last_status === 'fail' ? 'bad' : 'info') }}">
                      {{ $case->last_status ?? 'belum dites' }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
