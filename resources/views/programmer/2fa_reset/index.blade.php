@extends('layouts.app')

@section('title', '2FA Reset Requests')

@section('content')
  <div class="mx-auto max-w-7xl px-4 py-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-xl font-semibold text-slate-900">Manajemen Reset 2FA</h1>
        <p class="mt-1 text-sm text-slate-600">Approve atau reject permintaan reset 2FA dari user.</p>
      </div>
      <a href="{{ route('programmer.dashboard') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Kembali</a>
    </div>

    @if(session('success'))
      <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">ID</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Requester</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Alasan</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Handled</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            @forelse($requests as $req)
              @php
                $badge = match($req->status) {
                  'pending' => 'bg-yellow-100 text-yellow-800',
                  'approved' => 'bg-emerald-100 text-emerald-800',
                  'rejected' => 'bg-rose-100 text-rose-800',
                  default => 'bg-slate-100 text-slate-700',
                };
              @endphp
              <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-sm text-slate-700">#{{ $req->id }}</td>
                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-slate-900">{{ $req->requester->name ?? '-' }}</div>
                  <div class="text-xs text-slate-600">{{ $req->requester->username ?? '-' }} · {{ $req->requester->role ?? '-' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-slate-700" style="max-width:480px;">
                  <div class="truncate" title="{{ $req->reason }}">{{ $req->reason }}</div>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ strtoupper($req->status) }}</span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-700">
                  <div class="text-xs text-slate-600">{{ optional($req->handled_at)->format('d M Y H:i') ?? '-' }}</div>
                  <div class="text-xs text-slate-600">{{ $req->programmer->name ?? '-' }}</div>
                </td>
                <td class="px-4 py-3 text-right">
                  @if($req->status === 'pending')
                    <div class="inline-flex items-center gap-2">
                      <form method="POST" action="{{ url('/programmer/2fa-reset-requests/' . $req->id . '/approve') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Approve</button>
                      </form>
                      <button type="button" class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-medium text-white hover:bg-rose-700" data-open-reject="{{ $req->id }}">Reject</button>
                    </div>
                  @else
                    <span class="text-xs text-slate-500">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-600">Belum ada request reset 2FA.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="border-t border-slate-200 px-4 py-3">
        {{ $requests->links() }}
      </div>
    </div>
  </div>

  @foreach($requests as $req)
    @if($req->status === 'pending')
      <div id="rejectModal-{{ $req->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h2 class="text-base font-semibold text-slate-900">Reject Request #{{ $req->id }}</h2>
              <div class="mt-1 text-sm text-slate-600">Masukkan alasan penolakan.</div>
            </div>
            <button type="button" class="rounded-md p-2 text-slate-500 hover:bg-slate-100" data-close-reject="{{ $req->id }}">✕</button>
          </div>

          <form method="POST" action="{{ url('/programmer/2fa-reset-requests/' . $req->id . '/reject') }}" class="mt-4">
            @csrf
            <textarea name="notes" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-400 focus:outline-none" rows="4" placeholder="Alasan penolakan"></textarea>
            <div class="mt-4 flex justify-end gap-2">
              <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" data-close-reject="{{ $req->id }}">Batal</button>
              <button type="submit" class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-medium text-white hover:bg-rose-700">Reject</button>
            </div>
          </form>
        </div>
      </div>
    @endif
  @endforeach

  <script>
    document.addEventListener('click', (e) => {
      const openId = e.target?.getAttribute?.('data-open-reject')
      if (openId) {
        const modal = document.getElementById(`rejectModal-${openId}`)
        if (modal) modal.classList.remove('hidden')
        return
      }

      const closeId = e.target?.getAttribute?.('data-close-reject')
      if (closeId) {
        const modal = document.getElementById(`rejectModal-${closeId}`)
        if (modal) modal.classList.add('hidden')
      }
    })
  </script>
@endsection

