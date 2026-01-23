{{-- Document Detail Modal Partial --}}
{{-- Include this in analytics pages to show document details in popup --}}
{{-- Usage: @include('partials.document-detail-modal', ['detailRoute' => 'documents.verifikasi.detail', 'editRoute' =>
'documents.verifikasi.edit']) --}}

<!-- Modal View Document Detail -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 90%; width: 90%;">
        <div class="modal-content" style="height: 90vh; display: flex; flex-direction: column;">
            <!-- Sticky Header -->
            <div class="modal-header"
                style="position: sticky; top: 0; z-index: 1050; background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); border-bottom: none; flex-shrink: 0;">
                <h5 class="modal-title" id="viewDocumentModalLabel"
                    style="color: white; font-weight: 700; font-size: 18px;">
                    <i class="fa-solid fa-file-lines me-2"></i>
                    Detail Dokumen Lengkap
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Scrollable Body -->
            <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 140px); padding: 24px; flex: 1;">
                <input type="hidden" id="view-dokumen-id">

                <!-- Section 1: Identitas Dokumen -->
                <div class="form-section mb-4"
                    style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title"
                            style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-id-card"></i>
                            IDENTITAS DOKUMEN
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Nomor Agenda</label>
                                <div class="detail-value" id="view-nomor-agenda">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Nomor SPP</label>
                                <div class="detail-value" id="view-nomor-spp">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Tanggal SPP</label>
                                <div class="detail-value" id="view-tanggal-spp">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Bulan</label>
                                <div class="detail-value" id="view-bulan">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Tahun</label>
                                <div class="detail-value" id="view-tahun">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Tanggal Masuk</label>
                                <div class="detail-value" id="view-tanggal-masuk">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Kriteria CF</label>
                                <div class="detail-value" id="view-kategori">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Sub Kriteria</label>
                                <div class="detail-value" id="view-jenis-dokumen">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Item Sub Kriteria</label>
                                <div class="detail-value" id="view-jenis-sub-pekerjaan">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-item">
                                <label class="detail-label">Jenis Pembayaran</label>
                                <div class="detail-value" id="view-jenis-pembayaran">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Detail Keuangan & Vendor -->
                <div class="form-section mb-4"
                    style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title"
                            style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            DETAIL KEUANGAN & VENDOR
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="detail-item">
                                <label class="detail-label">Uraian SPP</label>
                                <div class="detail-value" id="view-uraian-spp" style="white-space: pre-wrap;">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Nilai Rupiah</label>
                                <div class="detail-value" id="view-nilai-rupiah"
                                    style="font-weight: 700; color: #083E40;">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Ejaan Nilai Rupiah</label>
                                <div class="detail-value" id="view-ejaan-nilai-rupiah"
                                    style="font-style: italic; color: #666;">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Dibayar Kepada (Vendor)</label>
                                <div class="detail-value" id="view-dibayar-kepada">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Kebun / Unit Kerja</label>
                                <div class="detail-value" id="view-kebun">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Referensi Pendukung -->
                <div class="form-section mb-4"
                    style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title"
                            style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-file-contract"></i>
                            REFERENSI PENDUKUNG
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="detail-item">
                                <label class="detail-label">No. SPK</label>
                                <div class="detail-value" id="view-no-spk">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-item">
                                <label class="detail-label">Tanggal SPK</label>
                                <div class="detail-value" id="view-tanggal-spk">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-item">
                                <label class="detail-label">Tanggal Berakhir SPK</label>
                                <div class="detail-value" id="view-tanggal-berakhir-spk">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="detail-item">
                                <label class="detail-label">Nomor Miro</label>
                                <div class="detail-value" id="view-nomor-miro">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">No. Berita Acara</label>
                                <div class="detail-value" id="view-no-berita-acara">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Tanggal Berita Acara</label>
                                <div class="detail-value" id="view-tanggal-berita-acara">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Nomor PO & PR -->
                <div class="form-section mb-4"
                    style="background: #f8f9fa; border-radius: 12px; padding: 20px; border: 1px solid #e9ecef;">
                    <div class="section-header mb-3">
                        <h6 class="section-title"
                            style="color: #083E40; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-hashtag"></i>
                            NOMOR PO & PR
                        </h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Nomor PO</label>
                                <div class="detail-value" id="view-nomor-po">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <label class="detail-label">Nomor PR</label>
                                <div class="detail-value" id="view-nomor-pr">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer -->
            <div class="modal-footer"
                style="position: sticky; bottom: 0; z-index: 1050; background: white; border-top: 2px solid #e0e0e0; padding: 16px 24px; flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 10px 24px;">
                    <i class="fa-solid fa-times me-2"></i>Tutup
                </button>
                <a href="#" id="view-edit-btn" class="btn"
                    style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; padding: 10px 24px;">
                    <i class="fa-solid fa-pen me-2"></i>Edit Dokumen
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Detail Item Styles for View Modal */
    .detail-item {
        margin-bottom: 8px;
    }

    .detail-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 14px;
        color: #1f2937;
        padding: 8px 12px;
        background: white;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        min-height: 38px;
        display: flex;
        align-items: center;
    }
</style>

<script>
    // Document Detail Modal Functions
    const documentDetailRoute = '{{ $detailRoute ?? "documents.verifikasi.detail" }}';
    const documentEditRoute = '{{ $editRoute ?? "documents.verifikasi.edit" }}';

    function openViewDocumentModal(docId) {
        // Set document ID
        document.getElementById('view-dokumen-id').value = docId;

        // Determine the base URL based on the current page context
        let baseUrl = '';
        if (documentDetailRoute.includes('verifikasi')) {
            baseUrl = '/documents/verifikasi';
        } else if (documentDetailRoute.includes('perpajakan')) {
            baseUrl = '/documents/perpajakan';
        } else if (documentDetailRoute.includes('akutansi')) {
            baseUrl = '/documents/akutansi';
        } else {
            baseUrl = '/documents/verifikasi';
        }

        // Set edit button URL
        document.getElementById('view-edit-btn').href = `${baseUrl}/${docId}/edit`;

        // Load document data via AJAX
        fetch(`${baseUrl}/${docId}/detail`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Document data received:', data);
                if (data.success && data.dokumen) {
                    const dok = data.dokumen;

                    // Identitas Dokumen
                    document.getElementById('view-nomor-agenda').textContent = dok.nomor_agenda || '-';
                    document.getElementById('view-nomor-spp').textContent = dok.nomor_spp || '-';
                    document.getElementById('view-tanggal-spp').textContent = dok.tanggal_spp ? formatDetailDate(dok.tanggal_spp) : '-';
                    document.getElementById('view-bulan').textContent = dok.bulan || '-';
                    document.getElementById('view-tahun').textContent = dok.tahun || '-';
                    document.getElementById('view-tanggal-masuk').textContent = dok.tanggal_masuk ? formatDetailDateTime(dok.tanggal_masuk) : '-';
                    document.getElementById('view-jenis-dokumen').textContent = dok.jenis_dokumen || '-';
                    document.getElementById('view-jenis-sub-pekerjaan').textContent = dok.jenis_sub_pekerjaan || '-';
                    document.getElementById('view-kategori').textContent = dok.kategori || '-';
                    document.getElementById('view-jenis-pembayaran').textContent = dok.jenis_pembayaran || '-';

                    // Detail Keuangan & Vendor
                    document.getElementById('view-uraian-spp').textContent = dok.uraian_spp || '-';
                    document.getElementById('view-nilai-rupiah').textContent = dok.nilai_rupiah ? 'Rp. ' + formatDetailNumber(dok.nilai_rupiah) : '-';
                    document.getElementById('view-ejaan-nilai-rupiah').textContent = dok.nilai_rupiah ? terbilang(dok.nilai_rupiah) + ' rupiah' : '-';
                    document.getElementById('view-dibayar-kepada').textContent = dok.dibayar_kepada || '-';
                    document.getElementById('view-kebun').textContent = dok.kebun || '-';

                    // Referensi Pendukung
                    document.getElementById('view-no-spk').textContent = dok.no_spk || '-';
                    document.getElementById('view-tanggal-spk').textContent = dok.tanggal_spk ? formatDetailDate(dok.tanggal_spk) : '-';
                    document.getElementById('view-tanggal-berakhir-spk').textContent = dok.tanggal_berakhir_spk ? formatDetailDate(dok.tanggal_berakhir_spk) : '-';
                    document.getElementById('view-nomor-miro').textContent = dok.nomor_miro || '-';
                    document.getElementById('view-no-berita-acara').textContent = dok.no_berita_acara || '-';
                    document.getElementById('view-tanggal-berita-acara').textContent = dok.tanggal_berita_acara ? formatDetailDate(dok.tanggal_berita_acara) : '-';

                    // Nomor PO & PR
                    const poList = dok.dokumen_pos && dok.dokumen_pos.length > 0
                        ? dok.dokumen_pos.map(po => po.nomor_po).join(', ')
                        : '-';
                    const prList = dok.dokumen_prs && dok.dokumen_prs.length > 0
                        ? dok.dokumen_prs.map(pr => pr.nomor_pr).join(', ')
                        : '-';
                    document.getElementById('view-nomor-po').textContent = poList;
                    document.getElementById('view-nomor-pr').textContent = prList;

                    // Show modal after data is loaded
                    const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                    modal.show();
                } else {
                    console.error('Invalid response format:', data);
                    alert('Gagal memuat data dokumen: ' + (data.message || 'Format respons tidak valid'));
                }
            })
            .catch(error => {
                console.error('Error loading document:', error);
                alert('Gagal memuat data dokumen: ' + error.message);
            });
    }

    // Helper functions for formatting
    function formatDetailDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatDetailDateTime(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function formatDetailNumber(num) {
        if (!num) return '0';
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // Terbilang function
    function terbilang(angka) {
        const huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        let temp = '';

        if (angka < 12) {
            temp = ' ' + huruf[angka];
        } else if (angka < 20) {
            temp = terbilang(angka - 10) + ' belas';
        } else if (angka < 100) {
            temp = terbilang(Math.floor(angka / 10)) + ' puluh' + terbilang(angka % 10);
        } else if (angka < 200) {
            temp = ' seratus' + terbilang(angka - 100);
        } else if (angka < 1000) {
            temp = terbilang(Math.floor(angka / 100)) + ' ratus' + terbilang(angka % 100);
        } else if (angka < 2000) {
            temp = ' seribu' + terbilang(angka - 1000);
        } else if (angka < 1000000) {
            temp = terbilang(Math.floor(angka / 1000)) + ' ribu' + terbilang(angka % 1000);
        } else if (angka < 1000000000) {
            temp = terbilang(Math.floor(angka / 1000000)) + ' juta' + terbilang(angka % 1000000);
        } else if (angka < 1000000000000) {
            temp = terbilang(Math.floor(angka / 1000000000)) + ' milyar' + terbilang(angka % 1000000000);
        } else if (angka < 1000000000000000) {
            temp = terbilang(Math.floor(angka / 1000000000000)) + ' triliun' + terbilang(angka % 1000000000000);
        }

        return temp.trim();
    }
</script>



