# PROMPT INSTRUKSI: Integrasi Real-time Dashboard Pembayaran & Tracking Dokumen

## KONTEKS SISTEM

Aplikasi Laravel "Agenda Online PTPN" adalah sistem manajemen dokumen keuangan dengan alur workflow multi-role:
**Unit Pengaju (Creator/Ibu Tarapul) → Verifikasi (Ibu Yuni) → Pajak (Perpajakan) → Akuntansi (Akutansi) → Pembayaran**

**Arsitektur yang Ada:**
- Laravel application dengan role-based middleware (`CheckRole.php`)
- Model `Dokumen.php` dengan 25+ enum status workflow
- Model `DocumentTracking.php` untuk audit trail dengan method `logAction()`
- Controller `DashboardPembayaranController.php` untuk role pembayaran
- View `resources/views/pembayaranNEW/dashboardPembayaran.blade.php`
- Helper `DokumenHelper.php` untuk validasi dokumen
- Pattern workflow: `ibuA` → `ibuB` → `perpajakan` → `akutansi` → `pembayaran`

---

## TUJUAN PERUBAHAN

Memperbarui logika visibilitas dan interaksi pada **Role Pembayaran** agar dapat:
1. Memantau dokumen sejak awal dibuat (oleh Unit Pengaju) secara real-time
2. Membatasi akses interaksi hingga dokumen disetujui oleh Akuntansi
3. Menyediakan tracking workflow untuk dokumen yang belum siap diproses

---

## REQUIREMENT 1: Sinkronisasi Data Real-time ke Pembayaran

### Spesifikasi:
- **Logika**: Setiap kali dokumen baru dibuat (create) oleh Unit Pengaju, dokumen tersebut harus **secara otomatis dan real-time muncul** pada List/Table dashboard Role Pembayaran
- **Catatan**: Tidak perlu menunggu dokumen melewati Verifikasi/Pajak/Akuntansi untuk tampil di list Pembayaran

### Implementasi Teknis:
1. **Update query di `DashboardPembayaranController@dokumens()`**:
   - Ubah base query agar include semua dokumen dengan status berikut:
     - `draft`, `menunggu_di_approve`
     - `sent_to_ibub`, `processed_by_ibub`
     - `sent_to_perpajakan`, `processed_by_perpajakan`
     - `sent_to_akutansi`, `processed_by_akutansi`
     - `sent_to_pembayaran` (existing)
   - **Tidak perlu filter** berdasarkan `current_handler` atau `status = 'sent_to_pembayaran'`
   - Gunakan query: `Dokumen::whereNotNull('nomor_agenda')` sebagai base query (sudah ada di codebase)

2. **Pastikan real-time visibility**:
   - Dokumen yang baru dibuat langsung muncul tanpa perlu action tambahan
   - Query harus include dokumen dengan `status` yang masih dalam proses di role lain

---

## REQUIREMENT 2: Manajemen Status Khusus Role Pembayaran

### Spesifikasi:
- **Decoupled Status**: Meskipun dokumen di sisi Unit Pengaju memiliki status alur (misal: Draft → Menunggu Approved → Approved), tampilan status pada Role Pembayaran harus berbeda (decoupled)
- **Default Status**: Saat dokumen baru masuk ke list Pembayaran, status yang tertampil adalah **"Belum Siap Bayar"**
- **Persistent Status**: Status "Belum Siap Bayar" bersifat persistent selama dokumen masih diproses di tahap Verifikasi, Pajak, atau Akuntansi

### Implementasi Teknis:
1. **Tambah accessor/method di Model `Dokumen.php`**:
   ```php
   // Method untuk menentukan status khusus role pembayaran
   public function getPaymentStatusAttribute(): string
   {
       // Jika sudah diproses akutansi atau dikirim ke pembayaran
       if (in_array($this->status, ['processed_by_akutansi', 'sent_to_pembayaran', 'processed_pembayaran'])) {
           return 'siap_bayar';
       }
       
       // Default: belum siap bayar (untuk semua status lainnya)
       return 'belum_siap_bayar';
   }
   ```

2. **Atau gunakan conditional logic di Blade view**:
   - Di `dashboardPembayaran.blade.php`, tambahkan logic:
     - Jika `status` = `processed_by_akutansi`, `sent_to_pembayaran`, atau `processed_pembayaran` → tampilkan "Siap Bayar"
     - Selain itu → tampilkan "Belum Siap Bayar"

3. **Gunakan helper function jika diperlukan**:
   - Bisa ditambahkan di `DokumenHelper.php` method `getPaymentStatus($dokumen)`

---

## REQUIREMENT 3: Trigger Perubahan Status

### Spesifikasi:
- **State Transition**: Status pada Role Pembayaran hanya boleh berubah dari "Belum Siap Bayar" menjadi "Siap Bayar" apabila terdapat trigger spesifik dari Role Akuntansi
- **Trigger**: Action "Kirim ke Pembayaran" (atau penyelesaian proses) oleh Role Akuntansi

### Implementasi Teknis:
1. **Modifikasi logic di `DashboardAkutansiController`**:
   - Saat Akutansi mengirim dokumen ke pembayaran (action "Kirim ke Pembayaran"):
     - Update `status` menjadi `sent_to_pembayaran` atau `processed_by_akutansi`
     - **Log tracking action** menggunakan `DocumentTracking::logAction()`:
       ```php
       DocumentTracking::logAction(
           $dokumen->id, 
           'marked_ready_for_payment', // atau gunakan action yang sudah ada: 'sent_to_pembayaran'
           'akutansi',
           [
               'previous_status' => $dokumen->status,
               'marked_ready_at' => now()->toDateTimeString()
           ]
       );
       ```

2. **Pastikan status update**:
   - Setelah action "Kirim ke Pembayaran", status dokumen harus berubah menjadi salah satu:
     - `processed_by_akutansi`
     - `sent_to_pembayaran`
   - Status "Siap Bayar" aktif hanya setelah status dokumen mencapai tahap ini

3. **Gunakan existing pattern**:
   - Ikuti pattern yang sudah ada di `DashboardAkutansiController` untuk update status
   - Gunakan `ActivityLogHelper::logSent()` jika diperlukan untuk activity log

---

## REQUIREMENT 4: UI/UX Implementation - Conditional UI/UX

### Spesifikasi:
Perilaku item pada daftar dokumen Role Pembayaran bergantung pada statusnya:

#### **Kondisi A: Status = "Belum Siap Bayar"**
- **Action Button**: Tampilkan icon "Mata" (View Tracking) di column "Aksi"
- **Fungsi Icon**: Membuka modal/halaman **Workflow Tracking System** (seperti view admin: `/owner/workflow/{id}`) untuk melihat posisi dokumen saat ini
- **Restriksi**: 
  - User **tidak bisa** mengklik item/row untuk melihat detail lengkap dokumen
  - User **tidak bisa** memproses pembayaran
  - Hanya tracking flow yang diizinkan
- **Styling**: Baris dengan opacity/indikator non-clickable (misal: `opacity: 0.7`, `cursor: not-allowed`)

#### **Kondisi B: Status = "Siap Bayar"**
- **Action Button**: Hilangkan icon "Mata" (tidak tampil)
- **Interaksi**: Baris dokumen (row) menjadi **clickable**
- **Fungsi**: Jika diklik, sistem menampilkan halaman **Full Data Dokumen** untuk diproses pembayarannya
- **Styling**: Baris clickable dengan hover effect (misal: `cursor: pointer`, `hover:bg-gray-50`)

### Implementasi Teknis:

1. **Update `dashboardPembayaran.blade.php`**:

   **a. Tambahkan logic untuk menentukan status pembayaran:**
   ```php
   @php
       $paymentStatus = 'belum_siap_bayar';
       if (in_array($dokumen->status, ['processed_by_akutansi', 'sent_to_pembayaran', 'processed_pembayaran'])) {
           $paymentStatus = 'siap_bayar';
       }
   @endphp
   ```

   **b. Conditional rendering untuk row:**
   ```php
   <tr 
       @if($paymentStatus === 'siap_bayar')
           onclick="window.location='/dokumensPembayaran/{{ $dokumen->id }}/detail'"
           style="cursor: pointer;"
           class="hover:bg-gray-50"
       @else
           style="opacity: 0.7; cursor: not-allowed;"
       @endif
   >
   ```

   **c. Conditional rendering untuk column "Aksi":**
   ```php
   <td class="text-center">
       @if($paymentStatus === 'belum_siap_bayar')
           <a href="/owner/workflow/{{ $dokumen->id }}" 
              target="_blank"
              class="text-blue-600 hover:text-blue-800"
              title="Lihat Tracking Workflow">
               <i class="fas fa-eye"></i>
           </a>
       @else
           {{-- Icon tidak ditampilkan untuk status "Siap Bayar" --}}
       @endif
   </td>
   ```

2. **Pastikan route untuk workflow tracking sudah ada**:
   - Route `/owner/workflow/{id}` harus accessible untuk role pembayaran
   - Jika belum ada, tambahkan route atau gunakan route yang sudah ada untuk tracking

3. **Styling tambahan**:
   - Tambahkan CSS class untuk hover effect pada row yang clickable
   - Gunakan existing CSS framework (Bootstrap/Tailwind) yang sudah digunakan di project

4. **Pastikan responsive design**:
   - Maintain existing responsive design dan CSS classes
   - Test di mobile view jika diperlukan

---

## IMPLEMENTASI STEPS (Urutan Pengerjaan)

1. **Update `DashboardPembayaranController@dokumens()` query**
   - Ubah base query agar include semua dokumen (tidak hanya yang `sent_to_pembayaran`)
   - Test query untuk memastikan dokumen baru langsung muncul

2. **Tambah status logic accessor di `Dokumen.php` model**
   - Tambahkan method `getPaymentStatusAttribute()` atau helper function
   - Test logic untuk memastikan status "Belum Siap Bayar" vs "Siap Bayar" benar

3. **Update view `dashboardPembayaran.blade.php` dengan conditional UI**
   - Implement conditional rendering untuk row clickability
   - Implement conditional rendering untuk icon "Mata"
   - Test UI untuk kedua kondisi (Belum Siap Bayar vs Siap Bayar)

4. **Modifikasi trigger di `DashboardAkutansiController`**
   - Tambahkan `DocumentTracking::logAction()` saat akutansi complete processing
   - Pastikan status update ke `processed_by_akutansi` atau `sent_to_pembayaran`
   - Test trigger untuk memastikan status berubah setelah action

5. **Update routes jika diperlukan**
   - Pastikan route `/owner/workflow/{id}` accessible untuk role pembayaran
   - Tambahkan route untuk detail pembayaran jika belum ada: `/dokumensPembayaran/{id}/detail`

6. **Test role access dan middleware protection**
   - Pastikan middleware `role:Pembayaran` masih berfungsi
   - Test akses untuk memastikan user pembayaran tidak bisa akses fitur yang tidak diizinkan

---

## PATTERN YANG HARUS DIIKUTI

1. **Gunakan existing helper**:
   - `DokumenHelper::isDocumentLocked()` untuk validasi dokumen
   - `DocumentTracking::logAction()` untuk audit trail
   - `ActivityLogHelper::logSent()` untuk activity log

2. **Ikuti existing middleware**:
   - Gunakan middleware `role:Pembayaran` yang sudah ada
   - Pastikan `CheckRole.php` middleware masih berfungsi

3. **Follow existing blade template structure**:
   - Gunakan struktur yang sama dengan file di folder `pembayaranNEW/`
   - Maintain existing responsive design dan CSS classes
   - Gunakan existing icon library (Font Awesome) yang sudah digunakan

4. **Maintain code consistency**:
   - Ikuti naming convention yang sudah ada
   - Gunakan existing database column names
   - Follow existing query patterns

5. **Error handling**:
   - Tambahkan try-catch jika diperlukan
   - Log errors menggunakan `Log::error()` atau `Log::info()`

---

## CATATAN PENTING

1. **Jangan hapus existing functionality**: Pastikan fitur yang sudah ada tetap berfungsi
2. **Backward compatibility**: Pastikan perubahan tidak merusak data atau functionality yang sudah ada
3. **Performance**: Pastikan query tidak terlalu berat, gunakan eager loading jika diperlukan
4. **Security**: Pastikan role-based access control tetap berfungsi dengan baik
5. **Testing**: Test semua scenario (dokumen baru, dokumen di proses, dokumen siap bayar)

---

## VALIDASI SETELAH IMPLEMENTASI

Setelah implementasi, pastikan:
- ✅ Dokumen baru langsung muncul di list pembayaran
- ✅ Status "Belum Siap Bayar" tampil untuk dokumen yang belum diproses akutansi
- ✅ Status "Siap Bayar" tampil setelah akutansi mengirim ke pembayaran
- ✅ Icon "Mata" muncul untuk dokumen "Belum Siap Bayar"
- ✅ Icon "Mata" tidak muncul untuk dokumen "Siap Bayar"
- ✅ Row clickable hanya untuk dokumen "Siap Bayar"
- ✅ Workflow tracking bisa diakses dari icon "Mata"
- ✅ Detail pembayaran bisa diakses dari row click untuk dokumen "Siap Bayar"
- ✅ Middleware protection masih berfungsi
- ✅ Tidak ada error di console atau log

---

**END OF PROMPT**

