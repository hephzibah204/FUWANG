{{--
    Word-style rich text editor for admin compose forms (broadcasts).
    A toolbar over a contenteditable surface; the HTML syncs into the
    named textarea on every change and on submit. The server sanitises
    with App\Support\RichText before storing or sending, so only safe
    formatting (bold, italic, headings, lists, links) survives.
    Usage: @include('admin.partials.rich-text-editor', ['name' => 'message', 'value' => old('message'), 'placeholder' => 'Type your message here...'])
--}}
@php
    $rteId = 'rte_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name);
    $rteValue = $value ?? '';
    $rtePlaceholder = $placeholder ?? 'Type your message here...';
@endphp
<div class="rich-text-editor">
    <div class="btn-group btn-group-sm mb-0 flex-wrap" role="toolbar" aria-label="Formatting" data-rte-toolbar="{{ $rteId }}">
        <button type="button" class="btn btn-outline-secondary" data-cmd="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-cmd="italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
        <button type="button" class="btn btn-outline-secondary fw-bold" data-cmd="formatBlock" data-arg="H2" title="Heading">H2</button>
        <button type="button" class="btn btn-outline-secondary fw-bold" data-cmd="formatBlock" data-arg="H3" title="Subheading">H3</button>
        <button type="button" class="btn btn-outline-secondary" data-cmd="formatBlock" data-arg="P" title="Normal text">Text</button>
        <button type="button" class="btn btn-outline-secondary" data-cmd="insertUnorderedList" title="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-cmd="insertOrderedList" title="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-cmd="createLink" title="Add link"><i class="fa-solid fa-link"></i></button>
        <button type="button" class="btn btn-outline-secondary" data-cmd="removeFormat" title="Clear formatting"><i class="fa-solid fa-eraser"></i></button>
    </div>
    <div id="{{ $rteId }}_surface" contenteditable="true"
         class="form-control bg-dark text-white border-secondary"
         style="min-height: 150px; max-height: 320px; overflow-y: auto; border-top-left-radius: 0;"
         data-placeholder="{{ $rtePlaceholder }}">@if(\App\Support\RichText::isHtml($rteValue)){!! $rteValue !!}@else{{ $rteValue }}@endif</div>
    <textarea name="{{ $name }}" id="{{ $rteId }}_field" class="d-none" aria-hidden="true">{{ $rteValue }}</textarea>
    <div class="form-text text-white-50">Format like a Word document: bold, headings, lists and links carry through into the email and the in-app message.</div>
</div>

@push('scripts')
<script>
(function () {
    var surface = document.getElementById('{{ $rteId }}_surface');
    var field = document.getElementById('{{ $rteId }}_field');
    if (!surface || !field) { return; }

    function sync() { field.value = surface.innerHTML; }
    surface.addEventListener('input', sync);
    surface.addEventListener('blur', sync);
    var form = surface.closest('form');
    if (form) { form.addEventListener('submit', sync); }
    sync();

    var toolbar = document.querySelector('[data-rte-toolbar="{{ $rteId }}"]');
    if (!toolbar) { return; }
    toolbar.querySelectorAll('[data-cmd]').forEach(function (btn) {
        btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
        btn.addEventListener('click', function () {
            var cmd = btn.getAttribute('data-cmd');
            surface.focus();
            if (cmd === 'createLink') {
                var url = window.prompt('Paste the link address (starting with https://):', 'https://');
                if (url && /^(https?:\/\/|mailto:)/i.test(url.trim())) {
                    document.execCommand('createLink', false, url.trim());
                } else if (url) {
                    window.alert('That link was not added. Links must start with https://, http:// or mailto:.');
                }
            } else if (cmd === 'formatBlock') {
                document.execCommand('formatBlock', false, btn.getAttribute('data-arg'));
            } else {
                document.execCommand(cmd, false, null);
            }
            sync();
        });
    });
})();
</script>
@endpush
