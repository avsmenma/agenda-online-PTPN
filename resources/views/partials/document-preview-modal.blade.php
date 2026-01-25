<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="documentPreviewModalLabel">
                    <i class="fas fa-file-alt me-2"></i>Document Quick View
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Loading State -->
                <div id="preview-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading document data...</p>
                </div>

                <!-- Content (Hidden until loaded) -->
                <div id="preview-content" style="display: none;">
                    <!-- Document Header -->
                    <div class="alert alert-info mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1" id="preview-doc-title">SPP-2024-001</h5>
                                <small class="text-muted" id="preview-doc-subtitle">Loading...</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-secondary fs-6" id="preview-status-badge">Status</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-3" id="previewTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info"
                                type="button" role="tab">
                                <i class="fas fa-info-circle me-1"></i> Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline"
                                type="button" role="tab">
                                <i class="fas fa-clock me-1"></i> Timeline
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments"
                                type="button" role="tab">
                                <i class="fas fa-comments me-1"></i> Comments
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="previewTabContent">
                        <!-- Information Tab -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-3 text-muted">Document Details</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="45%">Nomor Agenda:</th>
                                                    <td id="preview-nomor-agenda">-</td>
                                                </tr>
                                                <tr>
                                                    <th>Nomor SPP:</th>
                                                    <td id="preview-nomor-spp">-</td>
                                                </tr>
                                                <tr>
                                                    <th>Tanggal SPP:</th>
                                                    <td id="preview-tanggal-spp">-</td>
                                                </tr>
                                                <tr>
                                                    <th>Bagian:</th>
                                                    <td id="preview-bagian">-</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-3 text-muted">Payment Info</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th width="45%">Dibayar Kepada:</th>
                                                    <td id="preview-vendor">-</td>
                                                </tr>
                                                <tr>
                                                    <th>Nilai:</th>
                                                    <td class="fw-bold text-success" id="preview-nilai">-</td>
                                                </tr>
                                                <tr>
                                                    <th>Current Handler:</th>
                                                    <td id="preview-handler">-</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-2 text-muted">Uraian</h6>
                                            <p class="mb-0" id="preview-uraian">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Tab -->
                        <div class="tab-pane fade" id="timeline" role="tabpanel">
                            <div id="preview-timeline" class="timeline-container">
                                <!-- Timeline will be loaded here -->
                                <p class="text-muted">No timeline data available</p>
                            </div>
                        </div>

                        <!-- Comments Tab -->
                        <div class="tab-pane fade" id="comments" role="tabpanel">
                            <div id="preview-comments">
                                <!-- Comments will be loaded here -->
                                <p class="text-muted">No comments yet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with Actions -->
            <div class="modal-footer bg-light">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Close
                        </button>
                    </div>
                    <div id="preview-actions">
                        <!-- Actions will be shown based on user role -->
                        <button type="button" class="btn btn-success me-2" id="quick-approve-btn">
                            <i class="fas fa-check me-1"></i> Approve & Send
                        </button>
                        <button type="button" class="btn btn-danger" id="quick-reject-btn">
                            <i class="fas fa-ban me-1"></i> Return to Bagian
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview CSS -->
<style>
    /* Timeline Styles */
    .timeline-container {
        position: relative;
        padding: 20px 0;
        max-height: 400px;
        overflow-y: auto;
    }

    .timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 30px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 25px;
        bottom: -10px;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    .timeline-marker.completed {
        background: #10b981;
        border-color: #10b981;
        color: white;
    }

    .timeline-marker.pending {
        background: #f59e0b;
        border-color: #f59e0b;
        color: white;
    }

    .timeline-marker.current {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }

    .timeline-content {
        background: #f9fafb;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .timeline-content h6 {
        margin: 0 0 8px 0;
        font-weight: 600;
        color: #1f2937;
    }

    .timeline-content small {
        color: #6b7280;
    }

    /* Modal Enhancements */
    .modal-xl {
        max-width: 1140px;
    }

    .nav-tabs .nav-link {
        color: #6b7280;
        border: none;
        border-bottom: 2px solid transparent;
    }

    .nav-tabs .nav-link:hover {
        border-color: #e5e7eb;
    }

    .nav-tabs .nav-link.active {
        color: #0369a1;
        font-weight: 600;
        border-color: #0369a1;
        background: none;
    }

    /* Comment Cards */
    .comment-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 10px;
    }

    .comment-card h6 {
        font-size: 0.875rem;
        margin-bottom: 6px;
        color: #6b7280;
    }

    .comment-card p {
        margin: 0;
        color: #1f2937;
    }

    /* Quick Action Buttons */
    #quick-approve-btn,
    #quick-reject-btn {
        min-width: 150px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-xl {
            max-width: 100%;
            margin: 10px;
        }

        .timeline-item {
            padding-left: 40px;
        }

        #preview-actions {
            width: 100%;
        }

        #quick-approve-btn,
        #quick-reject-btn {
            width: 48%;
            min-width: auto;
            font-size: 0.875rem;
        }
    }
</style>

<!-- Document Preview JavaScript -->
<script>
    /**
     * Document Preview Modal Handler
     */
    class DocumentPreview {
        constructor() {
            this.modal = null;
            this.currentDocId = null;
            this.init();
        }

        init() {
            const modalEl = document.getElementById('documentPreviewModal');
            if (modalEl) {
                this.modal = new bootstrap.Modal(modalEl);
                this.setupEventListeners();
                this.setupKeyboardShortcuts();
            }
        }

        /**
         * Open preview modal
         */
        open(documentId) {
            this.currentDocId = documentId;
            this.modal.show();
            this.loadDocument(documentId);
        }

        /**
         * Load document data via AJAX
         */
        async loadDocument(docId) {
            try {
                // Show loading
                document.getElementById('preview-loading').style.display = 'block';
                document.getElementById('preview-content').style.display = 'none';

                // Fetch data
                const response = await fetch(`/api/documents/${docId}/preview`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Failed to load document');
                }

                // Populate modal
                this.populateDocument(result.document);
                this.populateTimeline(result.timeline);
                this.populateComments(result.comments);

                // Show content
                document.getElementById('preview-loading').style.display = 'none';
                document.getElementById('preview-content').style.display = 'block';

            } catch (error) {
                console.error('Error loading document:', error);
                document.getElementById('preview-loading').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load document preview. ${error.message}
                </div>
            `;
            }
        }

        /**
         * Populate document information
         */
        populateDocument(doc) {
            // Header
            document.getElementById('preview-doc-title').textContent = doc.nomor_spp || doc.nomor_agenda || 'Document';
            document.getElementById('preview-doc-subtitle').textContent = `Created by ${doc.created_by || 'Unknown'} on ${doc.created_at || '-'}`;
            document.getElementById('preview-status-badge').textContent = doc.status_display || doc.status || 'Unknown';

            // Details
            document.getElementById('preview-nomor-agenda').textContent = doc.nomor_agenda || '-';
            document.getElementById('preview-nomor-spp').textContent = doc.nomor_spp || '-';
            document.getElementById('preview-tanggal-spp').textContent = doc.tanggal_spp || '-';
            document.getElementById('preview-bagian').textContent = doc.bagian || '-';
            document.getElementById('preview-vendor').textContent = doc.dibayar_kepada || '-';
            document.getElementById('preview-nilai').textContent = doc.nilai_formatted || '-';
            document.getElementById('preview-handler').textContent = doc.handler_display || '-';
            document.getElementById('preview-uraian').textContent = doc.uraian || '-';
        }

        /**
         * Populate timeline
         */
        populateTimeline(timeline) {
            const container = document.getElementById('preview-timeline');

            if (!timeline || timeline.length === 0) {
                container.innerHTML = '<p class="text-muted">No timeline data available</p>';
                return;
            }

            container.innerHTML = '';

            timeline.forEach((item) => {
                const statusClass = item.is_completed ? 'completed' : (item.is_current ? 'current' : 'pending');
                const icon = item.is_completed ? 'check' : (item.is_current ? 'clock' : 'hourglass');

                const timelineItem = `
                <div class="timeline-item">
                    <div class="timeline-marker ${statusClass}">
                        <i class="fas fa-${icon}"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>${item.stage_display || item.stage}</h6>
                        <small class="d-block">
                            ${item.received_at ? `Received: ${item.received_at}` : 'Not received yet'}
                        </small>
                        ${item.processed_at ? `<small class="d-block">Processed: ${item.processed_at}</small>` : ''}
                        ${item.duration ? `<small class="d-block text-info">Duration: ${item.duration}</small>` : ''}
                        ${item.status ? `<span class="badge bg-secondary mt-1">${item.status}</span>` : ''}
                    </div>
                </div>
            `;
                container.innerHTML += timelineItem;
            });
        }

        /**
         * Populate comments
         */
        populateComments(comments) {
            const container = document.getElementById('preview-comments');

            if (!comments || comments.length === 0) {
                container.innerHTML = '<p class="text-muted">No comments yet</p>';
                return;
            }

            container.innerHTML = '';

            comments.forEach(comment => {
                const commentCard = `
                <div class="comment-card">
                    <h6>${comment.user || 'User'} - ${comment.stage || ''} <small class="text-muted">${comment.created_at || ''}</small></h6>
                    <p>${comment.notes || comment.action || 'No comment'}</p>
                </div>
            `;
                container.innerHTML += commentCard;
            });
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Quick approve
            const approveBtn = document.getElementById('quick-approve-btn');
            if (approveBtn) {
                approveBtn.addEventListener('click', () => this.quickApprove());
            }

            // Quick reject
            const rejectBtn = document.getElementById('quick-reject-btn');
            if (rejectBtn) {
                rejectBtn.addEventListener('click', () => this.quickReject());
            }
        }

        /**
         * Setup keyboard shortcuts
         */
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Only if modal is open
                const modalEl = document.getElementById('documentPreviewModal');
                if (!modalEl || !modalEl.classList.contains('show')) {
                    return;
                }

                // Ctrl+A to approve
                if (e.ctrlKey && e.key === 'a') {
                    e.preventDefault();
                    this.quickApprove();
                }

                // Ctrl+R to reject
                if (e.ctrlKey && e.key === 'r') {
                    e.preventDefault();
                    this.quickReject();
                }
            });
        }

        /**
         * Quick approve action
         */
        async quickApprove() {
            if (!confirm('Approve this document and send to Perpajakan?')) {
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                const response = await fetch(`/api/documents/${this.currentDocId}/quick-approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Document approved successfully!');
                    this.modal.hide();
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    this.showError(result.message || 'Failed to approve document');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError('An error occurred while approving document');
            }
        }

        /**
         * Quick reject action
         */
        async quickReject() {
            const reason = prompt('Enter reason for returning to Bagian:');
            if (!reason || reason.trim() === '') {
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                const response = await fetch(`/api/documents/${this.currentDocId}/quick-reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ reason: reason.trim() })
                });

                const result = await response.json();

                if (result.success) {
                    this.showSuccess('Document returned to Bagian');
                    this.modal.hide();
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    this.showError(result.message || 'Failed to return document');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError('An error occurred while returning document');
            }
        }

        // Helper functions
        showSuccess(message) {
            alert('✅ ' + message);
        }

        showError(message) {
            alert('❌ Error: ' + message);
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        window.documentPreview = new DocumentPreview();
    });

    // Global function to open preview
    function openDocumentPreview(docId) {
        if (window.documentPreview) {
            window.documentPreview.open(docId);
        }
    }
</script>