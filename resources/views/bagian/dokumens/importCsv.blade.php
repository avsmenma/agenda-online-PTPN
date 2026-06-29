@extends('layouts/app')
@section('content')

    {{-- Semua gaya di-scope ke .imp agar tidak bocor ke halaman lain (versi lama
         meng-override selektor global seperti h2). --}}
    <style>
        .imp {
            --imp-teal: #083E40;
            --imp-teal-2: #0a5e54;
            --imp-olive: #889717;
            --imp-ink: #0f172a;
            --imp-muted: #64748b;
            --imp-line: #e6e9ee;
            --imp-bg: #f6f8f7;
            --imp-ok: #15a36e;
            --imp-warn: #d98a04;
            --imp-err: #e1483b;
            color: var(--imp-ink);
        }

        .dark .imp {
            --imp-ink: #e8edf2;
            --imp-muted: #94a3b8;
            --imp-line: #2a3645;
            --imp-bg: #0f172a;
        }

        /* ---------- Command header ---------- */
        .imp-hero {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 28px 30px;
            background: linear-gradient(120deg, var(--imp-teal) 0%, var(--imp-teal-2) 55%, var(--imp-olive) 140%);
            color: #fff;
            box-shadow: 0 18px 40px -22px rgba(8, 62, 64, .8);
        }

        .imp-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(420px 180px at 88% -40%, rgba(255, 255, 255, .18), transparent 70%);
            pointer-events: none;
        }

        .imp-hero .eyebrow {
            text-transform: uppercase;
            letter-spacing: .22em;
            font-size: 11px;
            font-weight: 700;
            opacity: .82;
            margin-bottom: 6px;
        }

        .imp-hero h1 {
            font-size: clamp(22px, 3vw, 30px);
            font-weight: 800;
            letter-spacing: -.01em;
            margin: 0;
            line-height: 1.1;
        }

        .imp-hero p {
            margin: 8px 0 0;
            opacity: .9;
            font-size: 14px;
            max-width: 56ch;
        }

        .imp-hero .hero-mark {
            position: absolute;
            right: 26px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 76px;
            opacity: .16;
        }

        /* ---------- Stepper ---------- */
        .imp-steps {
            display: flex;
            gap: 10px;
            margin: 18px 0 26px;
        }

        .imp-steps .s {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid var(--imp-line);
            transition: all .25s ease;
        }

        .dark .imp-steps .s {
            background: #131c2b;
        }

        .imp-steps .s .dot {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 14px;
            background: var(--imp-bg);
            color: var(--imp-muted);
            flex: none;
        }

        .imp-steps .s .lbl small {
            display: block;
            font-size: 10px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--imp-muted);
        }

        .imp-steps .s .lbl strong {
            font-size: 14px;
        }

        .imp-steps .s.active {
            border-color: transparent;
            background: linear-gradient(120deg, var(--imp-teal), var(--imp-teal-2));
            color: #fff;
            box-shadow: 0 12px 26px -16px rgba(8, 62, 64, .9);
        }

        .imp-steps .s.active .dot {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .imp-steps .s.active .lbl small {
            color: rgba(255, 255, 255, .8);
        }

        .imp-steps .s.done .dot {
            background: var(--imp-ok);
            color: #fff;
        }

        /* ---------- Cards ---------- */
        .imp-card {
            background: #fff;
            border: 1px solid var(--imp-line);
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 1px 0 rgba(16, 24, 40, .02);
        }

        .dark .imp-card {
            background: #131c2b;
        }

        .imp-card>.head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .imp-card>.head .ttl {
            font-weight: 700;
            font-size: 17px;
        }

        .imp-card>.head .ico {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: var(--imp-bg);
            color: var(--imp-teal);
        }

        /* ---------- Dropzone ---------- */
        .imp-drop {
            border: 2px dashed var(--imp-line);
            border-radius: 16px;
            padding: 46px 24px;
            text-align: center;
            cursor: pointer;
            background:
                radial-gradient(140% 120% at 50% -20%, rgba(8, 62, 64, .04), transparent 60%);
            transition: border-color .2s, background .2s, transform .05s;
        }

        .imp-drop:hover {
            border-color: var(--imp-olive);
        }

        .imp-drop.dragover {
            border-color: var(--imp-teal);
            background: linear-gradient(120deg, rgba(8, 62, 64, .07), rgba(136, 151, 23, .07));
        }

        .imp-drop .disc {
            width: 74px;
            height: 74px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 30px;
            color: #fff;
            background: linear-gradient(135deg, var(--imp-teal), var(--imp-olive));
            box-shadow: 0 14px 26px -14px rgba(8, 62, 64, .8);
        }

        .imp-drop h5 {
            margin: 0 0 4px;
            font-weight: 700;
        }

        .imp-drop p {
            margin: 0 0 16px;
            color: var(--imp-muted);
            font-size: 13px;
        }

        .imp-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            padding: 11px 22px;
            border-radius: 11px;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
            background: linear-gradient(120deg, var(--imp-teal), var(--imp-teal-2));
            transition: transform .08s, box-shadow .2s, filter .2s;
            box-shadow: 0 10px 22px -12px rgba(8, 62, 64, .9);
        }

        .imp-btn:hover {
            color: #fff;
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .imp-btn:active {
            transform: translateY(0);
        }

        .imp-btn.ghost {
            background: transparent;
            color: var(--imp-muted);
            border: 1px solid var(--imp-line);
            box-shadow: none;
        }

        .imp-btn.ghost:hover {
            color: var(--imp-ink);
            border-color: var(--imp-muted);
        }

        /* file chip */
        .imp-filechip {
            display: none;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
            padding: 12px 14px;
            border: 1px solid var(--imp-line);
            border-radius: 12px;
            background: var(--imp-bg);
        }

        .imp-filechip .fi {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--imp-ok);
            flex: none;
        }

        .imp-filechip .meta strong {
            display: block;
            font-size: 14px;
            word-break: break-all;
        }

        .imp-filechip .meta small {
            color: var(--imp-muted);
        }

        /* ---------- Hint / format note ---------- */
        .imp-note {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid var(--imp-line);
            background: var(--imp-bg);
            font-size: 13px;
            color: var(--imp-muted);
        }

        .imp-note i {
            color: var(--imp-teal);
            margin-top: 2px;
        }

        .imp-note code {
            color: var(--imp-ink);
            font-weight: 600;
            background: transparent;
        }

        /* ---------- Stat strip (segmented) ---------- */
        .imp-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 22px;
        }

        @media (max-width: 640px) {
            .imp-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .imp-stat {
            border: 1px solid var(--imp-line);
            border-radius: 14px;
            padding: 16px 18px;
            background: #fff;
            position: relative;
        }

        .dark .imp-stat {
            background: #131c2b;
        }

        .imp-stat .n {
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .imp-stat .k {
            margin-top: 6px;
            font-size: 12px;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--imp-muted);
        }

        .imp-stat::before {
            content: '';
            position: absolute;
            left: 0;
            top: 14px;
            bottom: 14px;
            width: 4px;
            border-radius: 4px;
            background: var(--imp-muted);
            opacity: .4;
        }

        .imp-stat.ok::before { background: var(--imp-ok); opacity: 1; }
        .imp-stat.warn::before { background: var(--imp-warn); opacity: 1; }
        .imp-stat.err::before { background: var(--imp-err); opacity: 1; }
        .imp-stat.ok .n { color: var(--imp-ok); }
        .imp-stat.warn .n { color: var(--imp-warn); }
        .imp-stat.err .n { color: var(--imp-err); }

        /* ---------- Issue panels (scrollable) ---------- */
        .imp-panel {
            display: none;
            border: 1px solid var(--imp-line);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .imp-panel .ph {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 14px;
            border-bottom: 1px solid var(--imp-line);
            background: var(--imp-bg);
        }

        .imp-panel.is-err .ph { color: var(--imp-err); }
        .imp-panel.is-warn .ph { color: var(--imp-warn); }

        .imp-panel .ph .badge {
            margin-left: auto;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 999px;
            color: #fff;
        }

        .imp-panel.is-err .badge { background: var(--imp-err); }
        .imp-panel.is-warn .badge { background: var(--imp-warn); }

        .imp-panel .pb {
            max-height: 260px;
            overflow: auto;
        }

        .imp-row {
            display: flex;
            gap: 12px;
            padding: 10px 16px;
            border-bottom: 1px solid var(--imp-line);
            font-size: 13px;
        }

        .imp-row:last-child {
            border-bottom: none;
        }

        .imp-row .tag {
            flex: none;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            font-family: ui-monospace, "SF Mono", Menlo, monospace;
            font-size: 12px;
            color: var(--imp-muted);
            min-width: 110px;
        }

        /* ---------- Preview table ---------- */
        .imp-tablewrap {
            border: 1px solid var(--imp-line);
            border-radius: 14px;
            overflow: auto;
            max-height: 360px;
        }

        table.imp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        table.imp-table th {
            position: sticky;
            top: 0;
            background: var(--imp-teal);
            color: #fff;
            text-align: left;
            padding: 11px 14px;
            font-weight: 600;
            white-space: nowrap;
        }

        table.imp-table td {
            padding: 9px 14px;
            border-bottom: 1px solid var(--imp-line);
            white-space: nowrap;
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        table.imp-table tr:nth-child(even) td {
            background: rgba(0, 0, 0, .015);
        }

        /* ---------- Import progress ---------- */
        .imp-progress {
            height: 10px;
            border-radius: 999px;
            background: var(--imp-line);
            overflow: hidden;
        }

        .imp-progress > i {
            display: block;
            height: 100%;
            width: 0;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--imp-teal), var(--imp-olive));
            transition: width .3s ease;
        }

        .imp-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 18px 0;
            font-size: 14px;
            user-select: none;
        }

        .imp-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        @media (max-width: 640px) {
            .imp-steps { flex-direction: column; }
        }
    </style>

    <div class="imp">
        <header class="imp-hero">
            <i class="fa-solid fa-file-csv hero-mark"></i>
            <div class="eyebrow">Buku Agenda Online 2026 · PTPN IV Regional V</div>
            <h1>Import Dokumen dari CSV</h1>
            <p>Unggah berkas CSV agenda, tinjau hasil validasi per baris, lalu impor. Baris bermasalah otomatis dilewati — data yang ada tidak ditimpa.</p>
        </header>

        <!-- Stepper -->
        <nav class="imp-steps" aria-label="Langkah import">
            <div class="s active" id="step1">
                <div class="dot">1</div>
                <div class="lbl"><small>Langkah 1</small><strong>Unggah Berkas</strong></div>
            </div>
            <div class="s" id="step2">
                <div class="dot">2</div>
                <div class="lbl"><small>Langkah 2</small><strong>Tinjau & Validasi</strong></div>
            </div>
            <div class="s" id="step3">
                <div class="dot">3</div>
                <div class="lbl"><small>Langkah 3</small><strong>Impor Data</strong></div>
            </div>
        </nav>

        <!-- File input lives OUTSIDE the dropzone on purpose: kalau input ada di
             dalam dropzone, klik .click()-nya mem-bubble balik ke handler dropzone
             dan memicu dialog berulang (bug "minta pilih file terus"). -->
        <input type="file" id="csvFile" accept=".csv,.xlsx,.xls" hidden>

        <!-- Step 1: Upload -->
        <section class="imp-card" id="uploadSection">
            <div class="head">
                <span class="ico"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                <span class="ttl">Unggah Berkas CSV</span>
            </div>

            <div class="imp-drop" id="uploadArea">
                <div class="disc"><i class="fa-solid fa-file-csv"></i></div>
                <h5>Tarik &amp; letakkan berkas di sini</h5>
                <p>atau pilih dari komputer Anda — format .csv, maksimal 10 MB</p>
                <button type="button" class="imp-btn" id="btnPick">
                    <i class="fa-solid fa-folder-open"></i> Pilih Berkas
                </button>
            </div>

            <div class="imp-filechip" id="fileInfo">
                <span class="fi"><i class="fa-solid fa-check"></i></span>
                <div class="meta">
                    <strong id="fileName"></strong>
                    <small id="fileSize"></small>
                </div>
            </div>

            <div class="imp-note">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    Kolom yang dibaca: <code>Agenda</code>, <code>Bulan</code>, <code>Tahun</code>,
                    <code>No PP/SPP</code>, <code>Tanggal PP/SPP</code>, <code>Tanggal Masuk</code>,
                    <code>Dibayarkan kepada</code>, <code>Uraian</code>, <code>Nilai</code>, beserta kolom SPK/BA/PO bila ada.
                    Kolom lain (mis. Kontrol, LINK, pajak) diabaikan. Dokumen dengan Nomor Agenda yang sudah ada akan dilewati.
                </div>
            </div>
        </section>

        <!-- Step 2: Preview -->
        <section class="imp-card" id="previewSection" style="display:none;">
            <div class="head">
                <span class="ico"><i class="fa-solid fa-list-check"></i></span>
                <span class="ttl">Tinjau &amp; Validasi Data</span>
            </div>

            <div class="imp-stats">
                <div class="imp-stat"><div class="n" id="totalRows">0</div><div class="k">Total Baris</div></div>
                <div class="imp-stat ok"><div class="n" id="validRows">0</div><div class="k">Valid</div></div>
                <div class="imp-stat warn"><div class="n" id="warningRows">0</div><div class="k">Duplikat</div></div>
                <div class="imp-stat err"><div class="n" id="errorRows">0</div><div class="k">Error</div></div>
            </div>

            <div class="imp-panel is-err" id="errorsList">
                <div class="ph"><i class="fa-solid fa-circle-xmark"></i> Baris error (dilewati saat impor)
                    <span class="badge" id="errBadge">0</span></div>
                <div class="pb" id="errorsContent"></div>
            </div>

            <div class="imp-panel is-warn" id="warningsList">
                <div class="ph"><i class="fa-solid fa-triangle-exclamation"></i> Duplikat (dilewati saat impor)
                    <span class="badge" id="warnBadge">0</span></div>
                <div class="pb" id="warningsContent"></div>
            </div>

            <div style="font-weight:700;font-size:14px;margin:6px 0 10px;">Pratinjau Data <span style="color:var(--imp-muted);font-weight:500;">(10 baris pertama)</span></div>
            <div class="imp-tablewrap">
                <table class="imp-table">
                    <thead id="previewHeaders"></thead>
                    <tbody id="previewBody"></tbody>
                </table>
            </div>

            <label class="imp-check">
                <input type="checkbox" id="skipDuplicates" checked>
                Lewati dokumen yang sudah ada (berdasarkan Nomor Agenda)
            </label>

            <div class="imp-actions">
                <button type="button" class="imp-btn" id="btnImport"><i class="fa-solid fa-upload"></i> Mulai Impor</button>
                <button type="button" class="imp-btn ghost" onclick="location.reload()"><i class="fa-solid fa-arrow-left"></i> Batal</button>
            </div>
        </section>

        <!-- Step 3: Importing -->
        <section class="imp-card" id="importSection" style="display:none;">
            <div class="head">
                <span class="ico"><i class="fa-solid fa-spinner fa-spin"></i></span>
                <span class="ttl">Mengimpor Data…</span>
            </div>
            <div class="imp-progress"><i id="importProgress"></i></div>
            <p style="text-align:center;margin-top:14px;color:var(--imp-muted);" id="importStatus">Memproses data…</p>
        </section>

        <!-- Step 4: Result -->
        <section class="imp-card" id="resultSection" style="display:none;">
            <div class="head">
                <span class="ico" style="background:rgba(21,163,110,.12);color:var(--imp-ok);"><i class="fa-solid fa-circle-check"></i></span>
                <span class="ttl">Impor Selesai</span>
            </div>

            <div class="imp-stats" style="grid-template-columns:repeat(3,1fr);">
                <div class="imp-stat ok"><div class="n" id="importedCount">0</div><div class="k">Berhasil diimpor</div></div>
                <div class="imp-stat warn"><div class="n" id="skippedCount">0</div><div class="k">Dilewati</div></div>
                <div class="imp-stat err"><div class="n" id="failedCount">0</div><div class="k">Gagal</div></div>
            </div>

            <div class="imp-note">
                <i class="fa-solid fa-hashtag"></i>
                <div><strong style="color:var(--imp-ink);">Batch ID:</strong> <span id="batchId"></span><br>
                    Simpan ID ini untuk menelusuri dokumen hasil impor.</div>
            </div>

            <div class="imp-actions">
                <a href="/dokumens" class="imp-btn"><i class="fa-solid fa-list"></i> Lihat Daftar Dokumen</a>
                <button type="button" class="imp-btn ghost" onclick="location.reload()"><i class="fa-solid fa-rotate-right"></i> Impor Lagi</button>
            </div>
        </section>
    </div>

    <script>
        (function () {
            let uploadedFilePath = '';

            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('csvFile');
            const btnPick = document.getElementById('btnPick');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            // --- File picker (single trigger; input is outside dropzone) ---
            function openPicker() { fileInput.value = ''; fileInput.click(); }

            uploadArea.addEventListener('click', openPicker);
            btnPick.addEventListener('click', function (e) { e.stopPropagation(); openPicker(); });
            fileInput.addEventListener('change', handleFile);

            // --- Drag & drop ---
            ['dragenter', 'dragover'].forEach(ev => uploadArea.addEventListener(ev, e => {
                e.preventDefault(); uploadArea.classList.add('dragover');
            }));
            ['dragleave', 'drop'].forEach(ev => uploadArea.addEventListener(ev, e => {
                e.preventDefault(); uploadArea.classList.remove('dragover');
            }));
            uploadArea.addEventListener('drop', e => {
                const files = e.dataTransfer.files;
                if (files && files.length) { fileInput.files = files; handleFile(); }
            });

            function handleFile() {
                const file = fileInput.files[0];
                if (!file) return;

                const ext = file.name.slice(file.name.lastIndexOf('.')).toLowerCase();
                if (!['.csv', '.xlsx', '.xls'].includes(ext)) {
                    return alert('Berkas harus berformat .csv, .xlsx, atau .xls');
                }
                if (file.size > 10 * 1024 * 1024) {
                    return alert('Berkas terlalu besar. Maksimal 10 MB.');
                }

                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatSize(file.size);
                document.getElementById('fileInfo').style.display = 'flex';
                uploadFile(file);
            }

            function uploadFile(file) {
                const fd = new FormData();
                fd.append('csv_file', file);
                fetch('/documents/import/upload', {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: fd
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            uploadedFilePath = data.file_path;
                            goPreview(data.preview);
                        } else { alert('Gagal mengunggah: ' + data.message); }
                    })
                    .catch(err => { console.error(err); alert('Terjadi kesalahan saat mengunggah berkas.'); });
            }

            // --- Step 2 ---
            function goPreview(preview) {
                setStep(1, 'done'); setStep(2, 'active');
                document.getElementById('uploadSection').style.display = 'none';
                document.getElementById('previewSection').style.display = 'block';
                renderPreview(preview.headers, preview.rows);
                validateAll();
            }

            function renderPreview(headers, rows) {
                const head = document.getElementById('previewHeaders');
                const body = document.getElementById('previewBody');
                const cols = headers.slice(0, 8);
                head.innerHTML = '<tr>' + cols.map(h => `<th>${esc(h)}</th>`).join('') + '</tr>';
                body.innerHTML = rows.map(row =>
                    '<tr>' + row.slice(0, 8).map(c => `<td>${esc(c) || '<span style="color:#aaa">—</span>'}</td>`).join('') + '</tr>'
                ).join('');
            }

            function validateAll() {
                ['totalRows', 'validRows', 'warningRows', 'errorRows'].forEach(id =>
                    document.getElementById(id).textContent = '…');

                fetch('/documents/import/preview', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ file_path: uploadedFilePath })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) return;
                        const v = data.validation;
                        document.getElementById('totalRows').textContent = v.total;
                        document.getElementById('validRows').textContent = v.valid;
                        document.getElementById('warningRows').textContent = v.warnings;
                        document.getElementById('errorRows').textContent = v.errors;

                        renderIssues('errorsList', 'errorsContent', 'errBadge', v.errors, v.error_details, 'errors');
                        renderIssues('warningsList', 'warningsContent', 'warnBadge', v.warnings, v.warning_details, 'warnings');
                    });
            }

            function renderIssues(panelId, bodyId, badgeId, count, details, key) {
                const panel = document.getElementById(panelId);
                if (!count) { panel.style.display = 'none'; return; }
                panel.style.display = 'block';
                document.getElementById(badgeId).textContent = count;
                document.getElementById(bodyId).innerHTML = (details || []).map(d =>
                    `<div class="imp-row"><span class="tag">Baris ${d.row} · ${esc(d.nomor_agenda)}</span><span>${esc((d[key] || []).join(', '))}</span></div>`
                ).join('') + (count > (details || []).length
                    ? `<div class="imp-row" style="color:var(--imp-muted)"><span class="tag">…</span><span>dan ${count - details.length} baris lainnya</span></div>`
                    : '');
            }

            // --- Step 3 ---
            document.getElementById('btnImport').addEventListener('click', () => {
                setStep(2, 'done'); setStep(3, 'active');
                document.getElementById('previewSection').style.display = 'none';
                document.getElementById('importSection').style.display = 'block';
                runImport();
            });

            function runImport() {
                const skip = document.getElementById('skipDuplicates').checked;
                const bar = document.getElementById('importProgress');
                let p = 0;
                const t = setInterval(() => { p = Math.min(p + 8, 90); bar.style.width = p + '%'; }, 220);

                fetch('/documents/import', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ file_path: uploadedFilePath, skip_duplicates: skip })
                })
                    .then(r => r.json())
                    .then(data => {
                        clearInterval(t); bar.style.width = '100%';
                        if (data.success) setTimeout(() => showResult(data.result), 450);
                        else alert('Gagal mengimpor: ' + data.message);
                    })
                    .catch(err => { clearInterval(t); console.error(err); alert('Terjadi kesalahan saat mengimpor data.'); });
            }

            function showResult(r) {
                setStep(3, 'done');
                document.getElementById('importSection').style.display = 'none';
                document.getElementById('resultSection').style.display = 'block';
                document.getElementById('importedCount').textContent = r.imported;
                document.getElementById('skippedCount').textContent = r.skipped;
                document.getElementById('failedCount').textContent = r.failed;
                document.getElementById('batchId').textContent = r.batch_id;
            }

            // --- helpers ---
            function setStep(n, state) {
                const el = document.getElementById('step' + n);
                el.classList.remove('active', 'done');
                if (state) el.classList.add(state);
            }
            function esc(s) {
                return String(s ?? '').replace(/[&<>"']/g, m =>
                    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
            }
            function formatSize(b) {
                if (!b) return '0 B';
                const k = 1024, u = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(b) / Math.log(k));
                return Math.round(b / Math.pow(k, i) * 100) / 100 + ' ' + u[i];
            }
        })();
    </script>

@endsection
