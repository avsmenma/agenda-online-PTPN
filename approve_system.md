APPROVAL_WORKFLOW_IMPLEMENTATION.md                                                                                  │

│                                                                                                                      │

│ # PROMPT IMPLEMENTASI SISTEM APPROVAL DOKUMEN INBOX UNTUK PTPN ONLINE AGENDA SYSTEM                                  │

│                                                                                                                      │

│ ## **OVERVIEW REQUIREMENTS**                                                                                         │

│                                                                                                                      │

│ Implementasikan sistem approval/reject dokumen di mana setiap dokumen yang dikirim harus melewati proses persetujuan │

│  sebelum resmi masuk ke daftar dokumen penerima. Sistem ini akan menambahkan menu **Inbox** untuk user roles: Ibu    │

│ Yuni, Perpajakan, dan Akutansi sebagai pusat persetujuan dokumen masuk.                                              │

│                                                                                                                      │

│ ## **FITUR UTAMA YANG DIIMPLEMENTASIKAN**                                                                            │

│                                                                                                                      │

│ ### 1. **INBOX SYSTEM**                                                                                              │

│ - **Menu Inbox** baru untuk user roles: Ibu Yuni, Perpajakan, Akutansi                                               │

│ - **Daftar tunggu dokumen** yang menunggu persetujuan                                                                │

│ - **Tabel dokumen masuk** dengan informasi lengkap sebelum approve/reject                                            │

│ - **Real-time notifications** saat ada dokumen baru masuk inbox                                                      │

│                                                                                                                      │

│ ### 2. **APPROVAL WORKFLOW**                                                                                         │

│ - **Status baru**: "sedang menunggu di approve" saat dokumen dikirim                                                 │

│ - **Popup alasan reject** jika dokumen ditolak                                                                       │

│ - **Dokumen kembali ke pengirim** dengan status jelas                                                                │

│ - **Auto routing** ke daftar dokumen resmi jika di-approve                                                           │

│                                                                                                                      │

│ ### 3. **ENHANCED NOTIFICATION**                                                                                     │

│ - **Notifikasi terpisah** untuk pengirim dan penerima                                                                │

│ - **Status tracking** real-time untuk setiap tahap                                                                   │

│ - **History lengkap** proses approval yang dapat ditelusuri                                                          │

│                                                                                                                      │

│ ## **TECHNICAL IMPLEMENTATION PLAN**                                                                                 │

│                                                                                                                      │

│ ### **PHASE 1: DATABASE SCHEMA ENHANCEMENT**                                                                         │

│                                                                                                                      │

│ #### **1.1 Tambahkan kolom baru di tabel `dokumen`:**                                                                │

│ ```sql                                                                                                               │

│ ALTER TABLE dokumen ADD COLUMN inbox_approval_for ENUM('IbuB', 'Perpajakan', 'Akutansi') NULL;                       │

│ ALTER TABLE dokumen ADD COLUMN inbox_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending';      │

│ ALTER TABLE dokumen ADD COLUMN inbox_approval_sent_at TIMESTAMP NULL;                                                │

│ ALTER TABLE dokumen ADD COLUMN inbox_approval_responded_at TIMESTAMP NULL;                                           │

│ ALTER TABLE dokumen ADD COLUMN inbox_approval_reason TEXT NULL;                                                      │

│ ALTER TABLE dokumen ADD COLUMN inbox_original_status VARCHAR(50) NULL; -- backup status sebelum masuk inbox          │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **1.2 Tambahkan kolom tracking di `dokumen_activity_logs`:**                                                    │

│ ```sql                                                                                                               │

│ ALTER TABLE dokumen_activity_logs ADD COLUMN action_type ENUM('inbox_sent', 'inbox_approved', 'inbox_rejected',      │

│ 'inbox_returned');                                                                                                   │

│ ALTER TABLE dokumen_activity_logs ADD COLUMN metadata JSON NULL; -- additional data like reason, etc                 │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **1.3 Update Status Enum di `Dokumen.php`:**                                                                    │

│ ```php                                                                                                               │

│ protected $fillable = [                                                                                              │

│     // existing fields...                                                                                            │

│     'inbox_approval_for',                                                                                            │

│     'inbox_approval_status',                                                                                         │

│     'inbox_approval_sent_at',                                                                                        │

│     'inbox_approval_responded_at',                                                                                   │

│     'inbox_approval_reason',                                                                                         │

│     'inbox_original_status'                                                                                          │

│ ];                                                                                                                   │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ### **PHASE 2: BACKEND IMPLEMENTATION**                                                                              │

│                                                                                                                      │

│ #### **2.1 Update Model Dokumen (`app/Models/Dokumen.php`)**                                                         │

│ ```php                                                                                                               │

│ // Tambahkan methods baru:                                                                                           │

│ public function sendToInbox($recipientRole)                                                                          │

│ {                                                                                                                    │

│     $this->inbox_approval_for = $recipientRole;                                                                      │

│     $this->inbox_approval_status = 'pending';                                                                        │

│     $this->inbox_approval_sent_at = now();                                                                           │

│     $this->inbox_original_status = $this->status;                                                                    │

│     $this->status = 'menunggu_di_approve';                                                                           │

│     $this->save();                                                                                                   │

│                                                                                                                      │

│     // Log activity                                                                                                  │

│     DokumenActivityLog::create([                                                                                     │

│         'dokumen_id' => $this->id,                                                                                   │

│         'action_type' => 'inbox_sent',                                                                               │

│         'user_id' => auth()->id(),                                                                                   │

│         'description' => "Dokumen dikirim ke inbox {$recipientRole} menunggu persetujuan"                            │

│     ]);                                                                                                              │

│                                                                                                                      │

│     // Fire event                                                                                                    │

│     event(new DocumentSentToInbox($this, $recipientRole));                                                           │

│ }                                                                                                                    │

│                                                                                                                      │

│ public function approveInbox()                                                                                       │

│ {                                                                                                                    │

│     $this->inbox_approval_status = 'approved';                                                                       │

│     $this->inbox_approval_responded_at = now();                                                                      │

│                                                                                                                      │

│     // Restore ke status asli atau update ke status appropriate                                                      │

│     $this->status = $this->inbox_original_status ?? 'diterima';                                                      │

│                                                                                                                      │

│     $this->save();                                                                                                   │

│                                                                                                                      │

│     // Log approval                                                                                                  │

│     DokumenActivityLog::create([                                                                                     │

│         'dokumen_id' => $this->id,                                                                                   │

│         'action_type' => 'inbox_approved',                                                                           │

│         'user_id' => auth()->id(),                                                                                   │

│         'description' => "Dokumen disetujui di inbox {$this->inbox_approval_for}"                                    │

│     ]);                                                                                                              │

│                                                                                                                      │

│     // Fire event                                                                                                    │

│     event(new DocumentApprovedInbox($this));                                                                         │

│ }                                                                                                                    │

│                                                                                                                      │

│ public function rejectInbox($reason)                                                                                 │

│ {                                                                                                                    │

│     $this->inbox_approval_status = 'rejected';                                                                       │

│     $this->inbox_approval_reason = $reason;                                                                          │

│     $this->inbox_approval_responded_at = now();                                                                      │

│                                                                                                                      │

│     // Kembalikan ke pengirim dengan status rejected                                                                 │

│     $this->status = 'rejected_dikembalikan';                                                                         │

│     $this->current_handler = null; // Reset ke pengirim awal                                                         │

│                                                                                                                      │

│     $this->save();                                                                                                   │

│                                                                                                                      │

│     // Log rejection                                                                                                 │

│     DokumenActivityLog::create([                                                                                     │

│         'dokumen_id' => $this->id,                                                                                   │

│         'action_type' => 'inbox_rejected',                                                                           │

│         'user_id' => auth()->id(),                                                                                   │

│         'description' => "Dokumen ditolak di inbox {$this->inbox_approval_for}. Alasan: {$reason}",                  │

│         'metadata' => ['reason' => $reason]                                                                          │

│     ]);                                                                                                              │

│                                                                                                                      │

│     // Fire event                                                                                                    │

│     event(new DocumentRejectedInbox($this, $reason));                                                                │

│ }                                                                                                                    │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **2.2 Buat Events Baru:**                                                                                       │

│ `app/Events/DocumentSentToInbox.php`                                                                                 │

│ ```php                                                                                                               │

│ class DocumentSentToInbox                                                                                            │

│ {                                                                                                                    │

│     public $dokumen;                                                                                                 │

│     public $recipientRole;                                                                                           │

│                                                                                                                      │

│     public function __construct(Dokumen $dokumen, $recipientRole)                                                    │

│     {                                                                                                                │

│         $this->dokumen = $dokumen;                                                                                   │

│         $this->recipientRole = $recipientRole;                                                                       │

│     }                                                                                                                │

│                                                                                                                      │

│     public function broadcastOn()                                                                                    │

│     {                                                                                                                │

│         return new PrivateChannel('inbox.' . strtolower($this->recipientRole));                                      │

│     }                                                                                                                │

│ }                                                                                                                    │

│ ```                                                                                                                  │

│                                                                                                                      │

│ `app/Events/DocumentApprovedInbox.php`                                                                               │

│ `app/Events/DocumentRejectedInbox.php`                                                                               │

│                                                                                                                      │

│ #### **2.3 Buat InboxController Baru:**                                                                              │

│ `app/Http/Controllers/InboxController.php`                                                                           │

│ ```php                                                                                                               │

│ class InboxController extends Controller                                                                             │

│ {                                                                                                                    │

│     public function index()                                                                                          │

│     {                                                                                                                │

│         $user = auth()->user();                                                                                      │

│         $documents = Dokumen::where('inbox_approval_for', $user->role)                                               │

│                           ->where('inbox_approval_status', 'pending')                                                │

│                           ->with(['creator'])                                                                        │

│                           ->latest('inbox_approval_sent_at')                                                         │

│                           ->paginate(10);                                                                            │

│                                                                                                                      │

│         return view('inbox.index', compact('documents'));                                                            │

│     }                                                                                                                │

│                                                                                                                      │

│     public function show(Dokumen $dokumen)                                                                           │

│     {                                                                                                                │

│         // Validate user has access to this inbox                                                                    │

│         if ($dokumen->inbox_approval_for !== auth()->user()->role) {                                                 │

│             abort(403);                                                                                              │

│         }                                                                                                            │

│                                                                                                                      │

│         return view('inbox.show', compact('dokumen'));                                                               │

│     }                                                                                                                │

│                                                                                                                      │

│     public function approve(Request $request, Dokumen $dokumen)                                                      │

│     {                                                                                                                │

│         $dokumen->approveInbox();                                                                                    │

│                                                                                                                      │

│         return redirect()->route('inbox.index')                                                                      │

│                         ->with('success', 'Dokumen berhasil disetujui dan masuk ke daftar dokumen resmi.');          │

│     }                                                                                                                │

│                                                                                                                      │

│     public function reject(Request $request, Dokumen $dokumen)                                                       │

│     {                                                                                                                │

│         $request->validate([                                                                                         │

│             'reason' => 'required|string|max:500'                                                                    │

│         ]);                                                                                                          │

│                                                                                                                      │

│         $dokumen->rejectInbox($request->reason);                                                                     │

│                                                                                                                      │

│         return redirect()->route('inbox.index')                                                                      │

│                         ->with('success', 'Dokumen ditolak dan dikembalikan ke pengirim dengan alasan: ' .           │

│ $request->reason);                                                                                                   │

│     }                                                                                                                │

│ }                                                                                                                    │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **2.4 Update DokumenController:**                                                                               │

│ ```php                                                                                                               │

│ // Di method store/forward, ganti direct forwarding dengan sendToInbox:                                              │

│ public function forwardToDepartment(Request $request, Dokumen $dokumen)                                              │

│ {                                                                                                                    │

│     $department = $request->department;                                                                              │

│                                                                                                                      │

│     // Kirim ke inbox department                                                                                     │

│     $dokumen->sendToInbox($department);                                                                              │

│                                                                                                                      │

│     return redirect()->back()->with('success', "Dokumen dikirim ke inbox {$department} menunggu persetujuan.");      │

│ }                                                                                                                    │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ### **PHASE 3: FRONTEND IMPLEMENTATION**                                                                             │

│                                                                                                                      │

│ #### **3.1 Update Routes (`routes/web.php`):**                                                                       │

│ ```php                                                                                                               │

│ // Inbox routes                                                                                                      │

│ Route::middleware(['auth', 'role:IbuB,Perpajakan,Akutansi'])->group(function () {                                    │

│     Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');                                    │

│     Route::get('/inbox/{dokumen}', [InboxController::class, 'show'])->name('inbox.show');                            │

│     Route::post('/inbox/{dokumen}/approve', [InboxController::class, 'approve'])->name('inbox.approve');             │

│     Route::post('/inbox/{dokumen}/reject', [InboxController::class, 'reject'])->name('inbox.reject');                │

│ });                                                                                                                  │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **3.2 Update Navigation di Layout:**                                                                            │

│ ```html                                                                                                              │

│ <!-- Di sidebar navigation untuk role IbuB, Perpajakan, Akutansi -->                                                 │

│ @if(in_array(auth()->user()->role, ['IbuB', 'Perpajakan', 'Akutansi']))                                              │

│ <li class="nav-item">                                                                                                │

│     <a href="{{ route('inbox.index') }}" class="nav-link">                                                           │

│         <i class="nav-icon fas fa-inbox"></i>                                                                        │

│         <p>Inbox</p>                                                                                                 │

│         @if($pendingInboxCount > 0)                                                                                  │

│             <span class="badge badge-danger right">{{ $pendingInboxCount }}</span>                                   │

│         @endif                                                                                                       │

│     </a>                                                                                                             │

│ </li>                                                                                                                │

│ @endif                                                                                                               │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **3.3 Buat Inbox Views:**                                                                                       │

│ `resources/views/inbox/index.blade.php`                                                                              │

│ ```html                                                                                                              │

│ @extends('layouts.app')                                                                                              │

│                                                                                                                      │

│ @section('title', 'Inbox - Dokumen Menunggu Persetujuan')                                                            │

│                                                                                                                      │

│ @section('content')                                                                                                  │

│ <div class="content-wrapper">                                                                                        │

│     <div class="content-header">                                                                                     │

│         <div class="container-fluid">                                                                                │

│             <div class="row mb-2">                                                                                   │

│                 <div class="col-sm-6">                                                                               │

│                     <h1 class="m-0">Inbox Dokumen</h1>                                                               │

│                 </div>                                                                                               │

│                 <div class="col-sm-6">                                                                               │

│                     <ol class="breadcrumb float-sm-right">                                                           │

│                         <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>                 │

│                         <li class="breadcrumb-item active">Inbox</li>                                                │

│                     </ol>                                                                                            │

│                 </div>                                                                                               │

│             </div>                                                                                                   │

│         </div>                                                                                                       │

│     </div>                                                                                                           │

│                                                                                                                      │

│     <section class="content">                                                                                        │

│         <div class="container-fluid">                                                                                │

│             <!-- Pending Documents Table -->                                                                         │

│             <div class="card">                                                                                       │

│                 <div class="card-header">                                                                            │

│                     <h3 class="card-title">                                                                          │

│                         <i class="fas fa-clock"></i> Dokumen Menunggu Persetujuan                                    │

│                     </h3>                                                                                            │

│                     <div class="card-tools">                                                                         │

│                         <span class="badge badge-info">{{ $documents->count() }} Dokumen</span>                      │

│                     </div>                                                                                           │

│                 </div>                                                                                               │

│                 <div class="card-body">                                                                              │

│                     <table class="table table-bordered table-striped">                                               │

│                         <thead>                                                                                      │

│                             <tr>                                                                                     │

│                                 <th>No. Agenda</th>                                                                  │

│                                 <th>No. SPP</th>                                                                     │

│                                 <th>Uraian</th>                                                                      │

│                                 <th>Pengirim</th>                                                                    │

│                                 <th>Tanggal Kirim</th>                                                               │

│                                 <th>Nilai</th>                                                                       │

│                                 <th>Aksi</th>                                                                        │

│                             </tr>                                                                                    │

│                         </thead>                                                                                     │

│                         <tbody>                                                                                      │

│                             @forelse($documents as $dokumen)                                                         │

│                             <tr>                                                                                     │

│                                 <td>{{ $dokumen->nomor_agenda }}</td>                                                │

│                                 <td>{{ $dokumen->nomor_spp }}</td>                                                   │

│                                 <td>{{ $dokumen->uraian_spp }}</td>                                                  │

│                                 <td>{{ $dokumen->creator->name }}</td>                                               │

│                                 <td>{{ $dokumen->inbox_approval_sent_at->format('d/m/Y H:i') }}</td>                 │

│                                 <td class="text-right">Rp {{ number_format($dokumen->nilai_rupiah, 0, ',', '.')      │

│ }}</td>                                                                                                              │

│                                 <td>                                                                                 │

│                                     <div class="btn-group">                                                          │

│                                         <a href="{{ route('inbox.show', $dokumen) }}" class="btn btn-sm btn-info">   │

│                                             <i class="fas fa-eye"></i> Lihat                                         │

│                                         </a>                                                                         │

│                                     </div>                                                                           │

│                                 </td>                                                                                │

│                             </tr>                                                                                    │

│                             @empty                                                                                   │

│                             <tr>                                                                                     │

│                                 <td colspan="7" class="text-center">Tidak ada dokumen menunggu persetujuan</td>      │

│                             </tr>                                                                                    │

│                             @endforelse                                                                              │

│                         </tbody>                                                                                     │

│                     </table>                                                                                         │

│                 </div>                                                                                               │

│             </div>                                                                                                   │

│         </div>                                                                                                       │

│     </section>                                                                                                       │

│ </div>                                                                                                               │

│ @endsection                                                                                                          │

│ ```                                                                                                                  │

│                                                                                                                      │

│ `resources/views/inbox/show.blade.php`                                                                               │

│ ```html                                                                                                              │

│ @extends('layouts.app')                                                                                              │

│                                                                                                                      │

│ @section('title', 'Detail Dokumen - Inbox')                                                                          │

│                                                                                                                      │

│ @section('content')                                                                                                  │

│ <div class="content-wrapper">                                                                                        │

│     <div class="content-header">                                                                                     │

│         <!-- Header content -->                                                                                      │

│     </div>                                                                                                           │

│                                                                                                                      │

│     <section class="content">                                                                                        │

│         <div class="container-fluid">                                                                                │

│             <div class="row">                                                                                        │

│                 <div class="col-md-8">                                                                               │

│                     <!-- Document Details Card -->                                                                   │

│                     <div class="card">                                                                               │

│                         <div class="card-header">                                                                    │

│                             <h3 class="card-title">Detail Dokumen</h3>                                               │

│                         </div>                                                                                       │

│                         <div class="card-body">                                                                      │

│                             <table class="table table-bordered">                                                     │

│                                 <tr>                                                                                 │

│                                     <th width="150">No. Agenda</th>                                                  │

│                                     <td>{{ $dokumen->nomor_agenda }}</td>                                            │

│                                 </tr>                                                                                │

│                                 <tr>                                                                                 │

│                                     <th>No. SPP</th>                                                                 │

│                                     <td>{{ $dokumen->nomor_spp }}</td>                                               │

│                                 </tr>                                                                                │

│                                 <tr>                                                                                 │

│                                     <th>Tanggal SPP</th>                                                             │

│                                     <td>{{ $dokumen->tanggal_spp->format('d/m/Y') }}</td>                            │

│                                 </tr>                                                                                │

│                                 <tr>                                                                                 │

│                                     <th>Uraian SPP</th>                                                              │

│                                     <td>{{ $dokumen->uraian_spp }}</td>                                              │

│                                 </tr>                                                                                │

│                                 <tr>                                                                                 │

│                                     <th>Nilai Rupiah</th>                                                            │

│                                     <td class="text-right">Rp {{ number_format($dokumen->nilai_rupiah, 0, ',', '.')  │

│ }}</td>                                                                                                              │

│                                 </tr>                                                                                │

│                                 <tr>                                                                                 │

│                                     <th>Kategori</th>                                                                │

│                                     <td>{{ $dokumen->kategori }}</td>                                                │

│                                 </tr>                                                                                │

│                                 <tr>                                                                                 │

│                                     <th>Pengirim</th>                                                                │

│                                     <td>{{ $dokumen->creator->name }} ({{ $dokumen->creator->role }})</td>           │

│                                 </tr>                                                                                │

│                                 <tr>                                                                                 │

│                                     <th>Dikirim ke Inbox</th>                                                        │

│                                     <td>{{ $dokumen->inbox_approval_sent_at->format('d/m/Y H:i') }}</td>             │

│                                 </tr>                                                                                │

│                             </table>                                                                                 │

│                         </div>                                                                                       │

│                     </div>                                                                                           │

│                                                                                                                      │

│                     <!-- Action Buttons -->                                                                          │

│                     <div class="card">                                                                               │

│                         <div class="card-body text-center">                                                          │

│                             <form id="approveForm" method="POST" action="{{ route('inbox.approve', $dokumen) }}"     │

│ style="display: inline;">                                                                                            │

│                                 @csrf                                                                                │

│                                 <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Apakah │

│  Anda yakin menyetujui dokumen ini?')">                                                                              │

│                                     <i class="fas fa-check"></i> Approve                                             │

│                                 </button>                                                                            │

│                             </form>                                                                                  │

│                                                                                                                      │

│                             <button type="button" class="btn btn-danger btn-lg ml-2" data-toggle="modal"             │

│ data-target="#rejectModal">                                                                                          │

│                                 <i class="fas fa-times"></i> Reject                                                  │

│                             </button>                                                                                │

│                         </div>                                                                                       │

│                     </div>                                                                                           │

│                 </div>                                                                                               │

│                                                                                                                      │

│                 <div class="col-md-4">                                                                               │

│                     <!-- Status Card -->                                                                             │

│                     <div class="card bg-info">                                                                       │

│                         <div class="card-header">                                                                    │

│                             <h3 class="card-title">Status Dokumen</h3>                                               │

│                         </div>                                                                                       │

│                         <div class="card-body">                                                                      │

│                             <p><strong>Status Saat Ini:</strong></p>                                                 │

│                             <h4>Menunggu Persetujuan</h4>                                                            │

│                             <small>Dikirim: {{ $dokumen->inbox_approval_sent_at->diffForHumans() }}</small>          │

│                         </div>                                                                                       │

│                     </div>                                                                                           │

│                 </div>                                                                                               │

│             </div>                                                                                                   │

│         </div>                                                                                                       │

│     </section>                                                                                                       │

│ </div>                                                                                                               │

│                                                                                                                      │

│ <!-- Reject Modal -->                                                                                                │

│ <div class="modal fade" id="rejectModal" tabindex="-1">                                                              │

│     <div class="modal-dialog">                                                                                       │

│         <div class="modal-content">                                                                                  │

│             <form method="POST" action="{{ route('inbox.reject', $dokumen) }}">                                      │

│                 @csrf                                                                                                │

│                 <div class="modal-header">                                                                           │

│                     <h4 class="modal-title">Reject Dokumen</h4>                                                      │

│                     <button type="button" class="close" data-dismiss="modal">&times;</button>                        │

│                 </div>                                                                                               │

│                 <div class="modal-body">                                                                             │

│                     <div class="form-group">                                                                         │

│                         <label for="reason">Alasan Reject <span class="text-danger">*</span></label>                 │

│                         <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>       │

│                     </div>                                                                                           │

│                 </div>                                                                                               │

│                 <div class="modal-footer">                                                                           │

│                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>              │

│                     <button type="submit" class="btn btn-danger">Reject Dokumen</button>                             │

│                 </div>                                                                                               │

│             </form>                                                                                                  │

│         </div>                                                                                                       │

│     </div>                                                                                                           │

│ </div>                                                                                                               │

│ @endsection                                                                                                          │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ### **PHASE 4: NOTIFICATION SYSTEM ENHANCEMENT**                                                                     │

│                                                                                                                      │

│ #### **4.1 Real-time JavaScript untuk Inbox:**                                                                       │

│ ```javascript                                                                                                        │

│ // Di dashboard layout                                                                                               │

│ if(in_array(auth()->user()->role, ['IbuB', 'Perpajakan', 'Akutansi'])) {                                             │

│     // Listen for new inbox documents                                                                                │

│     Echo.private('inbox.' . '{{ strtolower(auth()->user()->role) }}')                                                │

│         .listen('DocumentSentToInbox', (e) => {                                                                      │

│             // Show notification                                                                                     │

│             showNotification(`Dokumen baru dari ${e.dokumen.creator.name} menunggu persetujuan Anda`, 'info');       │

│                                                                                                                      │

│             // Update inbox counter                                                                                  │

│             updateInboxCounter();                                                                                    │

│                                                                                                                      │

│             // Auto refresh inbox page if user is there                                                              │

│             if(window.location.pathname.includes('/inbox')) {                                                        │

│                 setTimeout(() => location.reload(), 2000);                                                           │

│             }                                                                                                        │

│         });                                                                                                          │

│ }                                                                                                                    │

│                                                                                                                      │

│ function updateInboxCounter() {                                                                                      │

│     fetch('/api/inbox-count')                                                                                        │

│         .then(response => response.json())                                                                           │

│         .then(data => {                                                                                              │

│             const badge = document.querySelector('.inbox-badge');                                                    │

│             if(badge && data.count > 0) {                                                                            │

│                 badge.textContent = data.count;                                                                      │

│                 badge.style.display = 'inline-block';                                                                │

│             }                                                                                                        │

│         });                                                                                                          │

│ }                                                                                                                    │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **4.2 API Endpoint untuk Inbox Count:**                                                                         │

│ ```php                                                                                                               │

│ // routes/api.php                                                                                                    │

│ Route::middleware(['auth'])->get('/inbox-count', function() {                                                        │

│     $count = Dokumen::where('inbox_approval_for', auth()->user()->role)                                              │

│                    ->where('inbox_approval_status', 'pending')                                                       │

│                    ->count();                                                                                        │

│                                                                                                                      │

│     return response()->json(['count' => $count]);                                                                    │

│ });                                                                                                                  │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ### **PHASE 5: WORKFLOW INTEGRATION**                                                                                │

│                                                                                                                      │

│ #### **5.1 Update DokumenController untuk kirim ke inbox:**                                                          │

│ ```php                                                                                                               │

│ // Di method untuk mengirim dokumen                                                                                  │

│ public function sendToIbuB(Request $request, Dokumen $dokumen)                                                       │

│ {                                                                                                                    │

│     // Kirim ke inbox Ibu B (Ibu Yuni)                                                                               │

│     $dokumen->sendToInbox('IbuB');                                                                                   │

│                                                                                                                      │

│     return redirect()->back()                                                                                        │

│                     ->with('success', 'Dokumen berhasil dikirim ke Ibu Yuni dan menunggu persetujuan.');             │

│ }                                                                                                                    │

│                                                                                                                      │

│ public function sendToPerpajakan(Request $request, Dokumen $dokumen)                                                 │

│ {                                                                                                                    │

│     $dokumen->sendToInbox('Perpajakan');                                                                             │

│                                                                                                                      │

│     return redirect()->back()                                                                                        │

│                     ->with('success', 'Dokumen berhasil dikirim ke Perpajakan dan menunggu persetujuan.');           │

│ }                                                                                                                    │

│                                                                                                                      │

│ public function sendToAkutansi(Request $request, Dokumen $dokumen)                                                   │

│ {                                                                                                                    │

│     $dokumen->sendToInbox('Akutansi');                                                                               │

│                                                                                                                      │

│     return redirect()->back()                                                                                        │

│                     ->with('success', 'Dokumen berhasil dikirim ke Akutansi dan menunggu persetujuan.');             │

│ }                                                                                                                    │

│ ```                                                                                                                  │

│                                                                                                                      │

│ #### **5.2 Update Status Display:**                                                                                  │

│ ```php                                                                                                               │

│ // Di DokumenHelper.php atau view helper                                                                             │

│ function getInboxStatusBadge($status) {                                                                              │

│     $badges = [                                                                                                      │

│         'pending' => '<span class="badge badge-warning">Menunggu Persetujuan</span>',                                │

│         'approved' => '<span class="badge badge-success">Disetujui</span>',                                          │

│         'rejected' => '<span class="badge badge-danger">Ditolak</span>'                                              │

│     ];                                                                                                               │

│                                                                                                                      │

│     return $badges[$status] ?? '';                                                                                   │

│ }                                                                                                                    │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ## **TESTING PLAN**                                                                                                  │

│                                                                                                                      │

│ ### **1. User Flow Testing:**                                                                                        │

│ 1. **Ibu A creates document** → Document status becomes "menunggu_di_approve"                                        │

│ 2. **Ibu A sends to Ibu B** → Ibu B gets notification, document appears in Ibu B's inbox                             │

│ 3. **Ibu B checks inbox** → Can see document details                                                                 │

│ 4. **Ibu B approves** → Document moves to Ibu B's main document list                                                 │

│ 5. **Ibu B rejects** → Document returns to Ibu A with reason                                                         │

│                                                                                                                      │

│ ### **2. Database Testing:**                                                                                         │

│ - Verify all new columns are populated correctly                                                                     │

│ - Test activity logging for all inbox actions                                                                        │

│ - Verify status transitions work properly                                                                            │

│                                                                                                                      │

│ ### **3. Notification Testing:**                                                                                     │

│ - Test real-time notifications for new inbox documents                                                               │

│ - Verify counter updates work correctly                                                                              │

│ - Test notification persistence                                                                                      │

│                                                                                                                      │

│ ### **4. Permission Testing:**                                                                                       │

│ - Verify only IbuB, Perpajakan, Akutansi can access inbox                                                            │

│ - Test that users can only approve/reject documents sent to their role                                               │

│ - Verify document creators cannot approve their own documents                                                        │

│                                                                                                                      │

│ ## **DEPLOYMENT INSTRUCTIONS**                                                                                       │

│                                                                                                                      │

│ ### **1. Database Migration:**                                                                                       │

│ ```bash                                                                                                              │

│ php artisan make:migration add_inbox_approval_fields_to_dokumen_table                                                │

│ php artisan migrate                                                                                                  │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ### **2. Clear Caches:**                                                                                             │

│ ```bash                                                                                                              │

│ php artisan config:clear                                                                                             │

│ php artisan cache:clear                                                                                              │

│ php artisan view:clear                                                                                               │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ### **3. Test Real-time Features:**                                                                                  │

│ ```bash                                                                                                              │

│ php artisan queue:work --timeout=60                                                                                  │

│ ```                                                                                                                  │

│                                                                                                                      │

│ ## **ENHANCEMENT IDEAS (Future)**                                                                                    │

│                                                                                                                      │

│ 1. **Batch Approval** - Approve multiple documents at once                                                           │

│ 2. **Auto-approval Rules** - Automatic approval based on criteria                                                    │

│ 3. **Email Notifications** - Email alerts for new inbox documents                                                    │

│ 4. **Mobile Responsive Inbox** - Better mobile experience                                                            │

│ 5. **Document Preview** - Preview documents before approval                                                          │

│ 6. **Approval History** - Complete audit trail view                                                                  │

│ 7. **Escalation Rules** - Auto-escalation if not approved within timeframe                                           │

│                                                                                                                      │

│ ---                                                                                                                  │

│                                                                                                                      │

│ **Catatan:** Implementasi ini memanfaatkan arsitektur yang sudah ada (events, real-time notifications, activity      │

│ logging) dan mengintegrasikan sistem approval secara seamless dengan workflow dokumen yang sudah established. Semua  │

│ komponen dibuat modular untuk memudahkan maintenance dan future enhancements