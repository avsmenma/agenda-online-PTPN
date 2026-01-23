@extends('layouts.app')

@section('title', 'Dokumen Menunggu Persetujuan - Agenda Online PTPN')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Dokumen Menunggu Persetujuan</h2>

    @if($dokumensPending->count() > 0)
        <div class="alert alert-info">
            <i class="fas fa-bell"></i> Anda memiliki {{ $dokumensPending->count() }} dokumen yang menunggu persetujuan
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No. Agenda</th>
                            <th>No. SPP</th>
                            <th>Uraian</th>
                            <th>Nilai</th>
                            <th>Dikirim Oleh</th>
                            <th>Tanggal Kirim</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dokumensPending as $dok)
                        <tr id="dokumen-row-{{ $dok->id }}">
                            <td>{{ $dok->nomor_agenda }}</td>
                            <td>{{ $dok->nomor_spp }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($dok->uraian_spp, 50) }}</td>
                            <td>{{ $dok->formatted_nilai_rupiah }}</td>
                            <td>{{ $dok->current_handler }}</td>
                            <td>{{ $dok->pending_approval_at->format('d M Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-success btn-accept"
                                        data-id="{{ $dok->id }}"
                                        data-nomor="{{ $dok->nomor_agenda }}">
                                    <i class="fas fa-check"></i> Terima
                                </button>
                                <button class="btn btn-sm btn-danger btn-reject"
                                        data-id="{{ $dok->id }}"
                                        data-nomor="{{ $dok->nomor_agenda }}">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                                <button class="btn btn-sm btn-info btn-detail"
                                        data-id="{{ $dok->id }}">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-secondary">
            <i class="fas fa-inbox"></i> Tidak ada dokumen yang menunggu persetujuan
        </div>
    @endif
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Anda akan menolak dokumen: <strong id="reject-nomor-agenda"></strong></p>
                <div class="form-group">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejection-reason" rows="4"
                              placeholder="Minimal 10 karakter..."></textarea>
                    <small class="text-danger" id="rejection-error" style="display:none;"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-reject">Tolak Dokumen</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let currentDokumenId = null;

    // Accept Document
    $('.btn-accept').click(function() {
        const dokumenId = $(this).data('id');
        const nomorAgenda = $(this).data('nomor');

        if (confirm(`Anda yakin ingin menerima dokumen ${nomorAgenda}?`)) {
            $.ajax({
                url: `/Team Verifikasi/dokumen/${dokumenId}/accept`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        // Remove row from table
                        $(`#dokumen-row-${dokumenId}`).fadeOut(500, function() {
                            $(this).remove();
                            // Reload jika tidak ada lagi pending
                            if ($('tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        }
    });

    // Reject Document - Show Modal
    $('.btn-reject').click(function() {
        currentDokumenId = $(this).data('id');
        const nomorAgenda = $(this).data('nomor');

        $('#reject-nomor-agenda').text(nomorAgenda);
        $('#rejection-reason').val('');
        $('#rejection-error').hide();
        $('#rejectModal').modal('show');
    });

    // Confirm Reject
    $('#confirm-reject').click(function() {
        const reason = $('#rejection-reason').val().trim();

        if (reason.length < 10) {
            $('#rejection-error').text('Alasan penolakan minimal 10 karakter').show();
            return;
        }

        $.ajax({
            url: `/Team Verifikasi/dokumen/${currentDokumenId}/reject`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                rejection_reason: reason
            },
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    alert(response.message);
                    // Remove row
                    $(`#dokumen-row-${currentDokumenId}`).fadeOut(500, function() {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        // Handle validation errors
                        const errors = xhr.responseJSON.errors;
                        const firstError = Object.values(errors)[0];
                        errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                    }
                } else if (xhr.status === 422) {
                    errorMessage = 'Validasi gagal. Pastikan alasan penolakan sudah diisi dengan benar.';
                }
                $('#rejection-error').text(errorMessage).show();
            }
        });
    });

    // Detail Document
    $('.btn-detail').click(function() {
        const dokumenId = $(this).data('id');
        window.open(`/dokumensB/${dokumenId}/edit`, '_blank');
    });
});
</script>
@endpush
@endsection


