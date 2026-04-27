@php
  static $handlerBagianOptions = null;

  if ($handlerBagianOptions === null) {
      $handlerBagianOptions = \App\Models\Bagian::active()->ordered()->get(['kode', 'nama']);
  }

  $normalizeHandler = function ($value) {
      $value = strtolower(trim((string) $value));
      return match ($value) {
          'verifikasi', 'team verifikasi', 'tim verifikasi', 'ibu yuni', 'ibu b' => 'team_verifikasi',
          'akuntansi', 'tim akuntansi', 'team akuntansi' => 'akutansi',
          default => str_replace(' ', '_', $value),
      };
  };

  $currentUserRole = $normalizeHandler(auth()->user()?->role ?? '');
  $currentHandler = $normalizeHandler($dokumen->current_handler ?? 'operator');
  $pendingStatus = $dokumen->relationLoaded('roleStatuses')
      ? $dokumen->roleStatuses->firstWhere('status', \App\Models\DokumenStatus::STATUS_PENDING)
      : $dokumen->roleStatuses()->where('status', \App\Models\DokumenStatus::STATUS_PENDING)->latest('status_changed_at')->first();

  $selectedHandler = $pendingStatus ? $normalizeHandler($pendingStatus->role_code) : $currentHandler;

  if (($dokumen->status ?? null) === 'returned_to_bidang' && $dokumen->return_source) {
      $selectedHandler = 'bagian_' . strtolower($dokumen->return_source);
  }

  $isOperationalRole = !in_array($currentUserRole, ['admin', 'programmer'], true);
  $canChangeHandler = $isOperationalRole && !$pendingStatus && $currentUserRole === $currentHandler;
  $isReturnedDocument = \Illuminate\Support\Str::startsWith((string) ($dokumen->status ?? ''), 'returned_');
  $showBagianOptions = $currentUserRole === 'team_verifikasi'
      || $isReturnedDocument
      || \Illuminate\Support\Str::startsWith($selectedHandler, 'bagian_');

  $baseHandlerOptions = [
      'operator' => 'Operator',
      'team_verifikasi' => 'Tim Verifikasi',
      'perpajakan' => 'Tim Perpajakan',
      'akutansi' => 'Tim Akuntansi',
      'pembayaran' => 'Tim Pembayaran',
  ];
@endphp

<select class="document-handler-select"
  data-document-id="{{ $dokumen->id }}"
  data-original-value="{{ $selectedHandler }}"
  {{ $canChangeHandler ? '' : 'disabled' }}
  title="{{ $canChangeHandler ? 'Ubah pengurus dokumen' : 'Hanya pengurus dokumen saat ini yang dapat mengubah' }}"
  onclick="event.stopPropagation()">
  @foreach($baseHandlerOptions as $value => $label)
    <option value="{{ $value }}" {{ $selectedHandler === $value ? 'selected' : '' }}>{{ $label }}</option>
  @endforeach

  @if($showBagianOptions && $handlerBagianOptions->isNotEmpty())
    <optgroup label="Bagian">
      @foreach($handlerBagianOptions as $bagian)
        @php $bagianValue = 'bagian_' . strtolower($bagian->kode); @endphp
        <option value="{{ $bagianValue }}" {{ $selectedHandler === $bagianValue ? 'selected' : '' }}>
          {{ $bagian->nama ?: $bagian->kode }}
        </option>
      @endforeach
    </optgroup>
  @endif
</select>

@once
  <style>
    .document-handler-select {
      min-width: 170px;
      max-width: 220px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 7px 9px;
      background: #fff;
      color: #0f172a;
      font-size: 12px;
      font-weight: 600;
      line-height: 1.2;
    }

    .document-handler-select:disabled {
      background: #f1f5f9;
      color: #64748b;
      cursor: not-allowed;
    }

    .document-handler-select.is-saving {
      opacity: .7;
    }
  </style>

  <script>
    const documentHandlerPositionKey = `document-handler-position:${window.location.pathname}${window.location.search}`;
    let pendingDocumentHandlerSelect = null;

    if ('scrollRestoration' in history) {
      history.scrollRestoration = 'manual';
    }

    function getDocumentHandlerScrollBox(select) {
      return select?.closest('.table-responsive')
        || document.querySelector('#documentTableContainer .table-responsive')
        || document.querySelector('.table-responsive');
    }

    function saveDocumentHandlerPosition(select) {
      if (!select) return;

      pendingDocumentHandlerSelect = select;
      const scrollBox = getDocumentHandlerScrollBox(select);
      sessionStorage.setItem(documentHandlerPositionKey, JSON.stringify({
        windowX: window.scrollX,
        windowY: window.scrollY,
        tableLeft: scrollBox ? scrollBox.scrollLeft : 0,
        tableTop: scrollBox ? scrollBox.scrollTop : 0,
        documentId: select?.dataset.documentId || null,
      }));
    }

    function reloadDocumentHandlerView(select) {
      saveDocumentHandlerPosition(select);

      if (typeof window.refreshDocumentTable === 'function') {
        const refreshResult = window.refreshDocumentTable();
        if (refreshResult && typeof refreshResult.finally === 'function') {
          refreshResult.finally(() => restoreDocumentHandlerPosition());
        } else {
          restoreDocumentHandlerPosition();
        }
        return;
      }

      window.location.reload();
    }

    function restoreDocumentHandlerPosition() {
      const storedPosition = sessionStorage.getItem(documentHandlerPositionKey);
      if (!storedPosition) return;

      sessionStorage.removeItem(documentHandlerPositionKey);

      let position;
      try {
        position = JSON.parse(storedPosition);
      } catch (error) {
        return;
      }

      const applyPosition = () => {
        const select = position.documentId
          ? document.querySelector(`.document-handler-select[data-document-id="${position.documentId}"]`)
          : null;
        const scrollBox = getDocumentHandlerScrollBox(select);

        if (scrollBox) {
          scrollBox.scrollLeft = position.tableLeft || 0;
          scrollBox.scrollTop = position.tableTop || 0;
        }

        window.scrollTo(position.windowX || 0, position.windowY || 0);
      };

      requestAnimationFrame(() => {
        applyPosition();
        requestAnimationFrame(applyPosition);
      });

      [50, 150, 300, 600].forEach((delay) => setTimeout(applyPosition, delay));
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', restoreDocumentHandlerPosition, { once: true });
    } else {
      restoreDocumentHandlerPosition();
    }

    document.addEventListener('change', async function (event) {
      const select = event.target.closest('.document-handler-select');
      if (!select) return;

      event.stopPropagation();

      const previousValue = select.dataset.originalValue;
      const nextValue = select.value;

      if (!nextValue || nextValue === previousValue) {
        select.value = previousValue;
        return;
      }

      select.disabled = true;
      select.classList.add('is-saving');

      try {
        const response = await fetch(`/documents/${select.dataset.documentId}/handler`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({ target_handler: nextValue }),
        });

        const result = await response.json().catch(() => ({}));

        if (!response.ok || result.success !== true) {
          throw new Error(result.message || 'Gagal mengubah pengurus dokumen.');
        }

        select.dataset.originalValue = nextValue;
        saveDocumentHandlerPosition(select);

        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: result.message || 'Pengurus dokumen berhasil diubah.',
            timer: 1300,
            showConfirmButton: false,
          }).then(() => reloadDocumentHandlerView(select));
        } else {
          reloadDocumentHandlerView(select);
        }
      } catch (error) {
        select.value = previousValue;
        alert(error.message || 'Gagal mengubah pengurus dokumen.');
        select.disabled = false;
        select.classList.remove('is-saving');
      }
    }, true);

    window.addEventListener('beforeunload', function () {
      if (pendingDocumentHandlerSelect) {
        saveDocumentHandlerPosition(pendingDocumentHandlerSelect);
      }
    });
  </script>
@endonce
