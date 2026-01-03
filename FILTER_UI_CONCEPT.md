# Konsep UI Modern untuk Filter Dokumen

## Fitur Filter yang Diminta:
1. Bagian
2. Vendor/Dibayar Kepada
3. Kriteria CF
4. Sub Kriteria (cascading dari Kriteria CF)
5. Item Sub Kriteria (cascading dari Sub Kriteria)
6. Kebun
7. Belum dibayar
8. Siap dibayar
9. Sudah dibayar

## Konsep UI:

### 1. Filter Panel (Expandable/Collapsible)
- Panel filter yang bisa di-expand/collapse dengan tombol toggle
- Default: Collapsed (tersembunyi)
- Ketika expanded: Menampilkan semua filter options
- Badge counter menunjukkan jumlah filter aktif

### 2. Filter Layout
- Grid layout responsif (2-3 kolom pada desktop, 1 kolom pada mobile)
- Setiap filter memiliki label dan dropdown/searchable select
- Filter status pembayaran menggunakan radio buttons atau checkbox

### 3. Live Reload
- Ketika filter dipilih, langsung reload halaman dengan parameter filter
- Menggunakan JavaScript untuk submit form otomatis
- Loading indicator saat reload

### 4. Active Filter Badges
- Menampilkan badge untuk setiap filter yang aktif
- Badge bisa di-click untuk menghapus filter tersebut
- Badge dengan warna berbeda untuk setiap jenis filter

### 5. Reset Button
- Tombol "Reset Filter" untuk menghapus semua filter sekaligus
- Tombol "Clear All" untuk menghapus semua filter aktif

### 6. Searchable Dropdowns
- Dropdown dengan search functionality untuk filter yang memiliki banyak opsi
- Menggunakan Select2 atau custom searchable dropdown

## Implementasi:

### Backend (Controller):
- Method `getFilterData()` untuk mendapatkan data filter
- Update `getDocumentsWithTracking()` untuk menerapkan filter

### Frontend (View):
- Filter panel dengan expand/collapse functionality
- Form dengan semua filter inputs
- JavaScript untuk live reload dan cascading dropdowns
- CSS untuk styling modern

