@extends('layouts.nexus')

@section('title', 'Notary & Legal Services | ' . config('app.name'))

@section('content')
<div class="service-page fade-in">
    <!-- Service Header -->
    <div class="service-header-card mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(139, 92, 246, 0.05)); border: 1px solid rgba(139, 92, 246, 0.2);">
        <div class="sh-icon" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff;"><i class="fa-solid fa-scale-balanced"></i></div>
        <div class="sh-text">
            <h1>Notary & Legal Hub</h1>
            <p>Draft professional agreements and access online notarization powered by AI.</p>
        </div>
        <div class="sh-badges ml-auto d-none d-md-flex">
            <span class="badge-accent"><i class="fa-solid fa-stamp"></i> AI Assisted</span>
            <span class="badge-accent"><i class="fa-solid fa-shield-halved"></i> Verified</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel-card p-4">
                <div class="tab-strip mb-4">
                    <button class="s-tab active" id="btn-drafting" onclick="showPanel('drafting', this)">Legal Drafting</button>
                    <button class="s-tab" id="btn-vault" onclick="showPanel('vault', this)">My Documents ({{ $myRequests->count() }})</button>
                </div>

                <div id="panel-drafting" class="s-main-panel active">
                    <div class="tab-strip mb-4 sub-tabs" id="category-tabs">
                        @foreach($docTypes as $category => $docs)
                            <button class="s-tab {{ $loop->first ? 'active' : '' }}" onclick="switchCategory('{{ Str::slug($category) }}', this)">
                                {{ $category }}
                            </button>
                        @endforeach
                        <button class="s-tab" onclick="switchCategory('custom', this)">Custom / Other</button>
                    </div>

                    <div id="document-selector">
                        @foreach($docTypes as $category => $docs)
                            <div class="category-panel {{ $loop->first ? 'active' : '' }}" id="{{ Str::slug($category) }}">
                                <div class="row g-3">
                                    @foreach($docs as $doc)
                                        <div class="col-md-6 mb-3">
                                            <div class="doc-item" onclick="selectDocument('{{ $doc->document_type }}', '{{ $doc->price }}', '{{ $doc->requires_court_stamp }}', '{{ $doc->category }}')">
                                                <div class="doc-icon"><i class="fa-solid fa-file-signature"></i></div>
                                                <div class="doc-info">
                                                    <strong>{{ ucwords(str_replace('_', ' ', $doc->document_type)) }}</strong>
                                                    <p class="m-0 small text-muted">₦{{ number_format($doc->price, 2) }}</p>
                                                </div>
                                                <div class="doc-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <!-- Custom Category Panel -->
                        <div class="category-panel" id="custom">
                            <div class="p-4 text-center border-glass rounded-xl">
                                <div class="sh-icon mx-auto mb-3" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="fa-solid fa-pen-nib"></i></div>
                                <h5>Other Legal Documents</h5>
                                <p class="text-muted small mb-4">Need something specific not listed above? We can draft custom agreements for you.</p>
                                <div class="form-group mb-4 text-left">
                                    <label class="small font-weight-bold mb-2">What document do you need?</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-file-pen"></i>
                                        <input type="text" id="custom_doc_name" class="form-control" placeholder="e.g. Intellectual Property Waiver">
                                    </div>
                                </div>
                                <button class="btn btn-primary w-100" onclick="selectDocument('custom', '5000', '0', 'Custom')">
                                    Start Drafting (₦5,000)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- DYNAMIC FORM (Initially Hidden) -->
                    <div id="dynamic-form-container" style="display: none;">
                        <button class="btn btn-link text-muted p-0 mb-3" onclick="backToSelection()">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Back to selection
                        </button>
                        <h4 id="selected-doc-title" class="mb-4"></h4>
                        
                        <form id="notaryDraftForm" onsubmit="submitDraft(event)">
                            <div id="form-fields">
                                <div class="form-group mb-4">
                                    <label class="font-weight-600 mb-2">FullName of Principal involved</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-user"></i>
                                        <input type="text" name="principal_name" class="form-control" placeholder="FullName or Company Name" required>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-600 mb-2">Short summary of parties and details</label>
                                    <div class="input-wrap">
                                        <i class="fa-solid fa-users-viewfinder"></i>
                                        <textarea name="document_details" class="form-control" style="min-height: 120px; padding-top: 15px;" placeholder="Describe the agreement, parties, and specific terms you want included..." required></textarea>
                                    </div>
                                    <small class="text-muted">Our AI uses this context to draft a legally sound document.</small>
                                </div>
                            </div>

                            <input type="hidden" id="selected_doc_type">
                            <input type="hidden" id="selected_doc_price">
                            <input type="hidden" id="selected_doc_court">
                            <input type="hidden" id="selected_doc_category">

                            <button type="submit" id="draftBtn" class="btn btn-primary btn-lg w-100" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6);">
                                <i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Generate Draft with AI
                            </button>
                        </form>
                    </div>

                    <!-- PREVIEW PANEL (Initially Hidden) -->
                    <div id="preview-container" style="display: none;">
                        <div class="preview-header d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Document Draft</h4>
                            <div class="d-flex gap-2">
                                <a href="#" id="draft-download-link" target="_blank" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-download mr-1"></i> PDF Draft</a>
                                <span class="badge badge-warning">Preview</span>
                            </div>
                        </div>
                        
                        <div class="document-preview-box">
                            <div class="watermark">DRAFT</div>
                            <div id="draft-content" class="legal-content"></div>
                        </div>

                        <div class="preview-actions mt-4">
                            <div class="p-3 bg-soft-info rounded mb-4">
                                <p class="small m-0 text-muted">
                                    <i class="fa-solid fa-circle-info mr-1"></i> 
                                    Review your draft. If everything looks good, proceed to payment. Once paid, the watermark will be removed and you can download the official PDF.
                                </p>
                            </div>
                            <button class="btn btn-success btn-lg w-100" onclick="payAndFinalize()" id="payBtn">
                                <i class="fa-solid fa-credit-card mr-2"></i> Pay & Finalize (₦<span id="final-price"></span>)
                            </button>
                            <button class="btn btn-outline w-100 mt-2" onclick="backToFields()">Edit Details</button>
                        </div>
                    </div>
                </div>

                <!-- VAULT PANEL -->
                <div id="panel-vault" class="s-main-panel">
                    @forelse($myRequests as $req)
                        <div class="doc-item mb-3" style="cursor: default;">
                            <div class="doc-icon"><i class="fa-solid fa-file-contract"></i></div>
                            <div class="doc-info">
                                <strong>{{ ucwords(str_replace('_', ' ', $req->document_type)) }}</strong>
                                <p class="m-0 small text-muted">
                                    Ref: {{ $req->reference }} | {{ $req->created_at->format('M d, Y') }}
                                </p>
                                <div class="mt-2">
                                    @if($req->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                        <button class="btn btn-xs btn-primary ml-2" onclick="resumeDraft('{{ $req->id }}', '{{ $req->amount_paid }}', '{{ $req->generated_content }}', '{{ Storage::url($req->draft_pdf_path) }}')">
                                            Pay & Finalize
                                        </button>
                                    @elseif($req->status === 'pending_stamp')
                                        <span class="badge badge-info">Awaiting Authority Stamp</span>
                                    @elseif($req->status === 'completed')
                                        <span class="badge badge-success">Completed & Certified</span>
                                    @endif
                                </div>
                            </div>
                            <div class="doc-actions">
                                @if($req->status === 'completed' && $req->final_pdf_path)
                                    <a href="{{ Storage::url($req->final_pdf_path) }}" target="_blank" class="btn btn-sm btn-success"><i class="fa-solid fa-download"></i> Final PDF</a>
                                @elseif($req->draft_pdf_path)
                                    <a href="{{ Storage::url($req->draft_pdf_path) }}" target="_blank" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-eye"></i> View Draft</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="sh-icon mx-auto mb-3" style="background: rgba(255,255,255,0.05); color: #888;"><i class="fa-solid fa-folder-open"></i></div>
                            <p class="text-muted m-0">No documents found in your vault.</p>
                            <button class="btn btn-link text-primary mt-2" onclick="showPanel('drafting', '#btn-drafting')">Start a new draft</button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card p-4 mb-4">
                <h3 class="h6 font-weight-bold mb-3">Service Guarantees</h3>
                <ul class="process-list p-0 m-0">
                    <li>
                        <div class="p-icon"><i class="fa-solid fa-check"></i></div>
                        <div class="p-text"><strong>Legal Standard</strong> All documents drafted follow Nigerian legal requirements (NBA standards).</div>
                    </li>
                    <li>
                        <div class="p-icon"><i class="fa-solid fa-check"></i></div>
                        <div class="p-text"><strong>Instant Preview</strong> See exactly what you are paying for before spending a dime.</div>
                    </li>
                    <li>
                        <div class="p-icon"><i class="fa-solid fa-check"></i></div>
                        <div class="p-text"><strong>Digital Verification</strong> Every document has a unique ID verifiable on our portal.</div>
                    </li>
                </ul>
            </div>

            <div class="alert alert-info py-3 border-0 small">
                <i class="fa-solid fa-circle-info mr-2"></i> 
                <strong>Note:</strong> Internal agreements are e-stamped immediately. Affidavits are sent to court and processed within 24-48 business hours.
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="current_request_id">
@endsection

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; overflow-x: auto; border-bottom: 2px solid rgba(255, 255, 255, 0.05); padding-bottom: 5px; }
    .s-tab { padding: 12px 20px; background: none; border: none; color: var(--clr-text-muted); cursor: pointer; font-size: 0.85rem; font-weight: 600; border-bottom: 2px solid transparent; transition: all 0.2s; white-space: nowrap; }
    .s-tab.active { color: #8b5cf6; border-bottom-color: #8b5cf6; }

    .sub-tabs .s-tab { font-size: 0.75rem; padding: 10px 15px; }

    .s-main-panel { display: none; }
    .s-main-panel.active { display: block; }

    .category-panel { display: none; }
    .category-panel.active { display: block; animation: fadeIn 0.4s ease; }

    .panel-card { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

    .doc-item { display: flex; align-items: center; gap: 15px; padding: 15px; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.02); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    #document-selector .doc-item:hover { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.08); transform: translateY(-4px); cursor: pointer; box-shadow: 0 12px 20px -5px rgba(139, 92, 246, 0.25); }
    .doc-icon { width: 44px; height: 44px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .doc-info { flex: 1; }
    .doc-arrow { color: rgba(255, 255, 255, 0.15); transition: transform 0.3s ease; }
    #document-selector .doc-item:hover .doc-arrow { transform: translateX(4px); color: #8b5cf6; }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; }

    .process-list { list-style: none; }
    .process-list li { display: flex; gap: 15px; margin-bottom: 20px; }
    .p-icon { width: 24px; height: 24px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; }
    .p-text { font-size: 0.85rem; color: var(--clr-text-muted); }
    .p-text strong { display: block; color: white; margin-bottom: 2px; }

    .document-preview-box { background: white; color: #333; padding: 40px; border-radius: 8px; position: relative; overflow: hidden; min-height: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 8rem; font-weight: 900; color: rgba(0, 0, 0, 0.03); pointer-events: none; z-index: 10; user-select: none; }
    .legal-content { font-family: 'Times New Roman', Times, serif; font-size: 1.05rem; line-height: 1.6; position: relative; z-index: 5; text-align: justify; }
    .legal-content h1 { text-align: center; font-size: 1.6rem; text-decoration: underline; margin-bottom: 30px; font-weight: bold; }
    .bg-soft-info { background: rgba(0, 150, 255, 0.1); }
    .btn-xs { padding: 4px 8px; font-size: 0.75rem; border-radius: 4px; }
</style>

<input type="hidden" id="current_request_id">
@endsection

@push('styles')
<style>
    .tab-strip { display: flex; gap: 0; overflow-x: auto; border-bottom: 2px solid rgba(255, 255, 255, 0.05); padding-bottom: 5px; }
    .s-tab { padding: 12px 20px; background: none; border: none; color: var(--clr-text-muted); cursor: pointer; font-size: 0.85rem; font-weight: 600; border-bottom: 2px solid transparent; transition: all 0.2s; white-space: nowrap; }
    .s-tab.active { color: #8b5cf6; border-bottom-color: #8b5cf6; }

    .category-panel { display: none; }
    .category-panel.active { display: block; animation: fadeIn 0.4s ease; }

    .panel-card { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

    .doc-item { display: flex; align-items: center; gap: 15px; padding: 15px; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.02); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .doc-item:hover { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.08); transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(139, 92, 246, 0.25); }
    .doc-icon { width: 44px; height: 44px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .doc-info { flex: 1; }
    .doc-arrow { color: rgba(255, 255, 255, 0.15); transition: transform 0.3s ease; }
    .doc-item:hover .doc-arrow { transform: translateX(4px); color: #8b5cf6; }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .input-wrap i { position: absolute; left: 15px; color: var(--clr-text-muted); }
    .input-wrap .form-control { padding-left: 45px !important; }

    .process-list { list-style: none; }
    .process-list li { display: flex; gap: 15px; margin-bottom: 20px; }
    .p-icon { width: 24px; height: 24px; background: #8b5cf6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; }
    .p-text { font-size: 0.85rem; color: var(--clr-text-muted); }
    .p-text strong { display: block; color: white; margin-bottom: 2px; }

    .document-preview-box { background: white; color: #333; padding: 40px; border-radius: 8px; position: relative; overflow: hidden; min-height: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 8rem; font-weight: 900; color: rgba(0, 0, 0, 0.05); pointer-events: none; z-index: 10; user-select: none; }
    .legal-content { font-family: 'Times New Roman', Times, serif; font-size: 1.1rem; line-height: 1.6; position: relative; z-index: 5; }
    .legal-content h1 { text-align: center; font-size: 1.8rem; text-decoration: underline; margin-bottom: 30px; font-weight: bold; }
    .bg-soft-info { background: rgba(0, 150, 255, 0.1); }
</style>
@endpush

@push('scripts')
<script>
    function showPanel(panelId, btn) {
        $('.s-main-panel').removeClass('active');
        $('.tab-strip > .s-tab').removeClass('active');
        $('#panel-' + panelId).addClass('active');
        $(btn).addClass('active');
    }

    function switchCategory(id, btn) {
        $('.category-panel').removeClass('active');
        $('#category-tabs .s-tab').removeClass('active');
        $('#' + id).addClass('active');
        $(btn).addClass('active');
    }

    function selectDocument(type, price, court, category) {
        if (type === 'custom') {
            const customName = $('#custom_doc_name').val();
            if (!customName) { Swal.fire('Error', 'Please enter the name of the document you need.', 'error'); return; }
            $('#selected-doc-title').text('Drafting: ' + customName);
            $('#selected_doc_type').val('custom');
            $('#custom_type_name_input').val(customName);
        } else {
            $('#selected-doc-title').text('Drafting: ' + type.replace(/_/g, ' ').toUpperCase());
            $('#selected_doc_type').val(type);
        }
        
        $('#selected_doc_price').val(price);
        $('#selected_doc_court').val(court);
        $('#selected_doc_category').val(category);
        $('#final-price').text(parseFloat(price).toLocaleString());

        $('#document-selector, #category-tabs').fadeOut(300, function() {
            $('#dynamic-form-container').fadeIn(300);
        });
    }

    function backToSelection() {
        $('#dynamic-form-container').fadeOut(300, function() {
            $('#document-selector, #category-tabs').fadeIn(300);
        });
    }

    function submitDraft(e) {
        e.preventDefault();
        const btn = $('#draftBtn');
        const formData = {
            principal_name: $('input[name="principal_name"]').val(),
            details: $('textarea[name="document_details"]').val(),
        };

        const payload = {
            _token: '{{ csrf_token() }}',
            document_type: $('#selected_doc_type').val(),
            custom_type_name: $('#custom_doc_name').val(),
            form_data: formData
        };

        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> AI is drafting your document...');

        $.post('{{ route("services.notary.submit") }}', payload).done(res => {
            if (res.status) {
                $('#current_request_id').val(res.request_id);
                $('#draft-content').html(res.content);
                $('#draft-download-link').attr('href', res.pdf_url);
                
                $('#dynamic-form-container').fadeOut(300, function() {
                    $('#preview-container').fadeIn(300);
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }).fail(xhr => {
            Swal.fire('Error', xhr.responseJSON?.message || 'Drafting failed', 'error');
        }).always(() => {
            btn.prop('disabled', false).html('<i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Generate Draft with AI');
        });
    }

    function payAndFinalize() {
        const requestId = $('#current_request_id').val();
        const btn = $('#payBtn');

        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing Payment...');

        $.post('{{ route("services.notary.pay") }}', {
            _token: '{{ csrf_token() }}',
            request_id: requestId
        }).done(res => {
            if (res.status) {
                Swal.fire({
                    title: 'Payment Successful!',
                    text: res.message,
                    icon: 'success',
                    confirmButtonText: 'Great'
                }).then(() => {
                    location.reload(); // In production, redirect to the vault or download page
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }).fail(xhr => {
            Swal.fire('Error', xhr.responseJSON?.message || 'Payment failed', 'error');
        }).always(() => {
            btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card mr-2"></i> Pay & Finalize');
        });
    }

    function backToFields() {
        $('#preview-container').fadeOut(300, function() {
            $('#dynamic-form-container').fadeIn(300);
        });
    }

    function resumeDraft(id, fee, content, pdfUrl) {
        $('#current_request_id').val(id);
        $('#draft-content').html(content);
        $('#draft-download-link').attr('href', pdfUrl);
        $('#final-price').text(parseFloat(fee).toLocaleString());
        
        showPanel('drafting', '#btn-drafting');
        $('#document-selector, #category-tabs').hide();
        $('#preview-container').show();
    }
</script>
@endpush

