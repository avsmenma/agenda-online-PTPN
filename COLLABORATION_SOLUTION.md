# 🔄 Solusi Kolaborasi Real-time untuk Team Verifikasi

## 📋 Masalah
Beberapa orang mengakses dan mengedit dokumen yang sama secara bersamaan di halaman Team Verifikasi, berpotensi menyebabkan konflik data.

## 🎯 Solusi yang Direkomendasikan

### **Opsi 1: Real-time Activity Indicators (RECOMMENDED - Mudah & Efektif)**
**Seperti Figma, menampilkan siapa yang sedang melihat/mengedit dokumen**

#### Keuntungan:
- ✅ Implementasi relatif mudah (sudah ada Laravel Echo + Pusher)
- ✅ User langsung tahu siapa yang sedang aktif
- ✅ Mencegah konflik tanpa blocking
- ✅ UX yang baik (seperti Google Docs, Figma)

#### Implementasi:
1. **Activity Tracking System**
   - Track siapa yang sedang melihat dokumen (`document_viewers` table)
   - Track siapa yang sedang mengedit (`document_editors` table)
   - Auto-cleanup setelah user tidak aktif (heartbeat system)

2. **Real-time Broadcasting**
   - Event: `DocumentViewed`, `DocumentEditStarted`, `DocumentEditStopped`
   - Channel: `document.{document_id}` atau `team-verifikasi.{document_id}`

3. **UI Indicators**
   - Avatar/cursor indicator di halaman dokumen
   - Badge "Sedang Dilihat oleh: [Nama User]"
   - Warning jika ada yang sedang edit: "⚠️ [Nama] sedang mengedit dokumen ini"

#### Contoh UI:
```
┌─────────────────────────────────────┐
│ 📄 Dokumen #1735                    │
│ 👤 Sedang dilihat:                  │
│    • John Doe (Anda)                │
│    • Jane Smith                     │
│ ⚠️ Jane Smith sedang mengedit...    │
└─────────────────────────────────────┘
```

---

### **Opsi 2: Optimistic Locking dengan Version Control**
**Deteksi konflik saat save, minta user memilih versi terbaru**

#### Keuntungan:
- ✅ Tidak blocking (user bisa edit kapan saja)
- ✅ Deteksi konflik otomatis
- ✅ User bisa pilih versi mana yang dipertahankan

#### Implementasi:
1. **Version Field di Database**
   ```php
   // Migration
   $table->integer('version')->default(1);
   $table->timestamp('last_edited_at')->nullable();
   $table->unsignedBigInteger('last_edited_by')->nullable();
   ```

2. **Conflict Detection**
   ```php
   // Saat update
   if ($dokumen->version !== $request->version) {
       // Konflik terdeteksi!
       return response()->json([
           'conflict' => true,
           'message' => 'Dokumen telah diubah oleh user lain',
           'server_version' => $dokumen->version,
           'server_data' => $dokumen->fresh()
       ]);
   }
   ```

3. **Conflict Resolution UI**
   - Modal dialog saat konflik terdeteksi
   - Tampilkan perbedaan (diff)
   - User pilih: "Gunakan versi saya" atau "Gunakan versi server"

---

### **Opsi 3: Pessimistic Locking (Document Lock)**
**Lock dokumen saat sedang diedit, unlock saat selesai**

#### Keuntungan:
- ✅ Mencegah konflik sepenuhnya
- ✅ Implementasi sederhana
- ✅ Jelas siapa yang sedang edit

#### Kekurangan:
- ❌ User lain harus menunggu
- ❌ Bisa terjadi "stuck lock" jika user lupa unlock

#### Implementasi:
1. **Lock System**
   ```php
   // Migration
   $table->boolean('is_locked')->default(false);
   $table->unsignedBigInteger('locked_by')->nullable();
   $table->timestamp('locked_at')->nullable();
   ```

2. **Lock/Unlock Logic**
   - Lock saat user klik "Edit"
   - Auto-unlock setelah 30 menit tidak aktif
   - Manual unlock saat user save/cancel

3. **UI Feedback**
   - Disable tombol edit jika locked
   - Tampilkan: "🔒 Dokumen sedang diedit oleh [Nama]"

---

### **Opsi 4: Hybrid Approach (BEST PRACTICE)**
**Kombinasi Activity Indicators + Optimistic Locking**

#### Implementasi:
1. **Real-time Activity Indicators** (Opsi 1)
   - User tahu siapa yang sedang aktif
   - Warning jika ada yang sedang edit

2. **Optimistic Locking** (Opsi 2)
   - Deteksi konflik saat save
   - Conflict resolution jika terjadi

3. **Soft Lock Warning**
   - Warning (bukan hard lock) jika ada yang sedang edit
   - User tetap bisa edit, tapi dengan peringatan

---

## 🚀 Rekomendasi Implementasi (Prioritas)

### **Phase 1: Quick Win (1-2 hari)**
Implementasi **Opsi 1 (Activity Indicators)** terlebih dahulu:
- ✅ Cepat diimplementasikan
- ✅ Memberikan value langsung
- ✅ Tidak mengubah workflow yang ada

### **Phase 2: Conflict Prevention (3-5 hari)**
Tambahkan **Opsi 2 (Optimistic Locking)**:
- ✅ Deteksi konflik otomatis
- ✅ Conflict resolution UI

### **Phase 3: Advanced (Optional)**
Jika diperlukan, tambahkan **Soft Lock Warning**:
- ✅ Warning saat ada yang sedang edit
- ✅ Tetap allow edit dengan konfirmasi

---

## 📝 Detail Implementasi Opsi 1 (Activity Indicators)

### 1. Database Migration
```php
// database/migrations/xxxx_create_document_activities_table.php
Schema::create('document_activities', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('dokumen_id');
    $table->unsignedBigInteger('user_id');
    $table->string('activity_type'); // 'viewing', 'editing'
    $table->timestamp('last_activity_at');
    $table->timestamps();
    
    $table->unique(['dokumen_id', 'user_id', 'activity_type']);
    $table->foreign('dokumen_id')->references('id')->on('dokumens');
    $table->foreign('user_id')->references('id')->on('users');
});
```

### 2. Event Broadcasting
```php
// app/Events/DocumentActivityChanged.php
class DocumentActivityChanged implements ShouldBroadcast
{
    public function __construct(
        public $dokumenId,
        public $userId,
        public $userName,
        public $activityType, // 'viewing', 'editing', 'left'
        public $timestamp
    ) {}

    public function broadcastOn()
    {
        return new Channel("document.{$this->dokumenId}");
    }
}
```

### 3. Controller Logic
```php
// app/Http/Controllers/InboxController.php
public function trackActivity($dokumenId, $activityType)
{
    DocumentActivity::updateOrCreate(
        [
            'dokumen_id' => $dokumenId,
            'user_id' => auth()->id(),
            'activity_type' => $activityType
        ],
        ['last_activity_at' => now()]
    );
    
    broadcast(new DocumentActivityChanged(
        $dokumenId,
        auth()->id(),
        auth()->user()->name,
        $activityType,
        now()
    ));
}
```

### 4. Frontend JavaScript
```javascript
// resources/js/document-activity.js
window.Echo.channel(`document.${documentId}`)
    .listen('.DocumentActivityChanged', (e) => {
        updateActivityIndicator(e.userId, e.userName, e.activityType);
    });

// Heartbeat - kirim activity setiap 30 detik
setInterval(() => {
    fetch(`/api/documents/${documentId}/activity`, {
        method: 'POST',
        body: JSON.stringify({ activity_type: 'viewing' })
    });
}, 30000);
```

### 5. UI Component
```blade
<!-- resources/views/inbox/show.blade.php -->
<div id="activity-indicators" class="activity-panel">
    <div class="activity-header">
        <i class="fas fa-users"></i> Aktivitas Dokumen
    </div>
    <div id="viewers-list">
        <!-- Dynamic: Updated via JavaScript -->
    </div>
    <div id="editors-warning" class="alert alert-warning" style="display: none;">
        ⚠️ <span id="editor-name"></span> sedang mengedit dokumen ini
    </div>
</div>
```

---

## 🎨 UI/UX Recommendations

### Activity Panel Design:
```
┌─────────────────────────────┐
│ 👥 Aktivitas Dokumen        │
├─────────────────────────────┤
│ 👤 Sedang melihat:          │
│    • John Doe (Anda) 🟢     │
│    • Jane Smith 🟢          │
│                             │
│ ✏️ Sedang mengedit:         │
│    • Jane Smith ⚠️          │
└─────────────────────────────┘
```

### Warning Badge:
```
┌─────────────────────────────┐
│ ⚠️ Peringatan               │
│ Jane Smith sedang mengedit  │
│ dokumen ini. Perubahan Anda │
│ mungkin akan konflik.       │
│                             │
│ [Lanjutkan Edit] [Batal]    │
└─────────────────────────────┘
```

---

## 🔧 Alternative: Simple Solution (Tanpa Real-time)

Jika real-time terlalu kompleks, bisa menggunakan **polling**:

```javascript
// Poll setiap 5 detik
setInterval(() => {
    fetch(`/api/documents/${documentId}/activities`)
        .then(res => res.json())
        .then(data => updateActivityIndicators(data));
}, 5000);
```

**Keuntungan:**
- ✅ Tidak perlu WebSocket
- ✅ Lebih sederhana
- ✅ Tetap efektif

**Kekurangan:**
- ❌ Delay 5 detik (bukan real-time)
- ❌ Lebih banyak request ke server

---

## 📊 Comparison Table

| Solusi | Kompleksitas | Efektivitas | UX | Rekomendasi |
|--------|--------------|-------------|-----|-------------|
| Activity Indicators | ⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ✅ **RECOMMENDED** |
| Optimistic Locking | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ✅ Recommended |
| Pessimistic Locking | ⭐⭐ | ⭐⭐⭐ | ⭐⭐ | ⚠️ Tidak disarankan |
| Hybrid Approach | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ✅ **BEST** |

---

## 🎯 Kesimpulan & Rekomendasi

**Untuk Team Verifikasi, saya rekomendasikan:**

1. **Mulai dengan Activity Indicators (Opsi 1)**
   - Implementasi cepat
   - Memberikan visibility langsung
   - User tahu siapa yang sedang aktif

2. **Tambahkan Optimistic Locking (Opsi 2)**
   - Deteksi konflik saat save
   - Conflict resolution UI

3. **Kombinasi keduanya = Hybrid Approach**
   - Best of both worlds
   - UX terbaik
   - Mencegah konflik efektif

**Ini mirip dengan Google Docs/Figma:**
- User melihat siapa yang sedang aktif (Activity Indicators)
- Konflik ditangani saat save dengan conflict resolution (Optimistic Locking)
- Tidak ada hard lock yang blocking (user tetap bisa edit)

---

## 📚 Referensi

- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Pusher Channels](https://pusher.com/channels)
- [Optimistic Locking Pattern](https://en.wikipedia.org/wiki/Optimistic_concurrency_control)
- [Google Docs Collaboration](https://workspace.google.com/products/docs/)

