@extends('layouts.nexus')

@section('title', 'WhatsApp Widget Settings')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.settings.index') }}" class="btn btn-dark rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1);">
                <i class="fa fa-arrow-left text-white"></i>
            </a>
            <div>
                <h3 class="text-white mb-0 fw-bold"><i class="fa-brands fa-whatsapp text-success mr-2"></i> WhatsApp Widget Settings</h3>
                <p class="text-white-50 mb-0">Configure the floating WhatsApp button across your application.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Configuration Form -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <form action="{{ route('admin.settings.whatsapp_widget.update') }}" method="POST" id="whatsappConfigForm">
                @csrf
                
                <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">General Settings</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Enable Widget</label>
                        <select name="whatsapp_enabled" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" onchange="updatePreview()">
                            <option value="1" {{ $settings['whatsapp_enabled'] == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ $settings['whatsapp_enabled'] == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">WhatsApp Number (with country code)</label>
                        <input type="text" name="whatsapp_number" class="form-control text-white rounded-3" value="{{ $settings['whatsapp_number'] }}" placeholder="e.g. 2348000000000" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" oninput="updatePreview()">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="text-white-50 small mb-2">Pre-filled Message</label>
                        <input type="text" name="whatsapp_prefilled_message" class="form-control text-white rounded-3" value="{{ $settings['whatsapp_prefilled_message'] }}" placeholder="Hello, I need help with..." style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Display Pages</label>
                        <select name="whatsapp_display_pages" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                            <option value="all" {{ $settings['whatsapp_display_pages'] == 'all' ? 'selected' : '' }}>All Pages</option>
                            <option value="auth" {{ $settings['whatsapp_display_pages'] == 'auth' ? 'selected' : '' }}>Logged-in Users Only</option>
                            <option value="guest" {{ $settings['whatsapp_display_pages'] == 'guest' ? 'selected' : '' }}>Guests Only</option>
                        </select>
                    </div>
                </div>

                <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">Appearance & Positioning</h5>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Position</label>
                        <select name="whatsapp_position" id="whatsapp_position" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" onchange="updatePreview()">
                            <option value="bottom-right" {{ $settings['whatsapp_position'] == 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                            <option value="bottom-left" {{ $settings['whatsapp_position'] == 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                            <option value="top-right" {{ $settings['whatsapp_position'] == 'top-right' ? 'selected' : '' }}>Top Right</option>
                            <option value="top-left" {{ $settings['whatsapp_position'] == 'top-left' ? 'selected' : '' }}>Top Left</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Animation Style</label>
                        <select name="whatsapp_animation" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" onchange="updatePreview()">
                            <option value="none" {{ $settings['whatsapp_animation'] == 'none' ? 'selected' : '' }}>None</option>
                            <option value="bounce" {{ $settings['whatsapp_animation'] == 'bounce' ? 'selected' : '' }}>Bounce</option>
                            <option value="pulse" {{ $settings['whatsapp_animation'] == 'pulse' ? 'selected' : '' }}>Pulse</option>
                            <option value="fade" {{ $settings['whatsapp_animation'] == 'fade' ? 'selected' : '' }}>Fade In</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">X Offset (px)</label>
                        <input type="number" id="whatsapp_x_offset" name="whatsapp_x_offset" class="form-control text-white rounded-3" value="{{ $settings['whatsapp_x_offset'] }}" min="0" max="500" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" oninput="updatePreview()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">Y Offset (px)</label>
                        <input type="number" id="whatsapp_y_offset" name="whatsapp_y_offset" class="form-control text-white rounded-3" value="{{ $settings['whatsapp_y_offset'] }}" min="0" max="500" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" oninput="updatePreview()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">Button Size (px)</label>
                        <input type="number" id="whatsapp_size" name="whatsapp_size" class="form-control text-white rounded-3" value="{{ $settings['whatsapp_size'] }}" min="30" max="100" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" oninput="updatePreview()">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Main Color</label>
                        <div class="d-flex align-items-center">
                            <input type="color" id="whatsapp_color" name="whatsapp_color" class="form-control form-control-color border-0 p-0 mr-2 bg-transparent" value="{{ $settings['whatsapp_color'] }}" style="width: 40px; height: 40px; cursor: pointer;" oninput="updatePreview()">
                            <span class="text-white-50 font-monospace" id="color_hex">{{ $settings['whatsapp_color'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-white-50 small mb-2">Hover Color</label>
                        <div class="d-flex align-items-center">
                            <input type="color" id="whatsapp_hover_color" name="whatsapp_hover_color" class="form-control form-control-color border-0 p-0 mr-2 bg-transparent" value="{{ $settings['whatsapp_hover_color'] }}" style="width: 40px; height: 40px; cursor: pointer;">
                            <span class="text-white-50 font-monospace" id="hover_color_hex">{{ $settings['whatsapp_hover_color'] }}</span>
                        </div>
                    </div>
                </div>

                <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">Operating Hours</h5>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">Timezone</label>
                        <select name="whatsapp_timezone" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                            @foreach(timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" {{ $settings['whatsapp_timezone'] == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">Start Time</label>
                        <input type="time" name="whatsapp_operating_hours_start" class="form-control text-white rounded-3" value="{{ $settings['whatsapp_operating_hours_start'] }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="text-white-50 small mb-2">End Time</label>
                        <input type="time" name="whatsapp_operating_hours_end" class="form-control text-white rounded-3" value="{{ $settings['whatsapp_operating_hours_end'] }}" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="col-12">
                        <small class="text-white-50"><i class="fa fa-info-circle mr-1"></i> Widget will only display during these hours. To show 24/7, set Start Time to 00:00 and End Time to 23:59.</small>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-success rounded-pill px-5"><i class="fa fa-save mr-2"></i> Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 p-4 sticky-top" style="top: 20px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important; min-height: 400px; overflow: hidden; position: relative;">
            <h5 class="text-white fw-bold mb-3"><i class="fa fa-eye mr-2"></i> Live Preview</h5>
            <p class="text-white-50 small">This box simulates your website window. The widget position and size update in real-time.</p>
            
            <div id="preview-window" class="rounded border border-secondary position-relative w-100 mt-4" style="height: 300px; background: #1a1d21; overflow: hidden;">
                <!-- Simulated Widget -->
                <div id="preview-widget" class="position-absolute d-flex align-items-center justify-content-center text-white rounded-circle shadow-lg transition-all" style="cursor: pointer;">
                    <i class="fa-brands fa-whatsapp fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updatePreview() {
        const enabled = document.querySelector('[name="whatsapp_enabled"]').value;
        const position = document.getElementById('whatsapp_position').value;
        const size = document.getElementById('whatsapp_size').value;
        const color = document.getElementById('whatsapp_color').value;
        const xOffset = document.getElementById('whatsapp_x_offset').value;
        const yOffset = document.getElementById('whatsapp_y_offset').value;
        const animation = document.querySelector('[name="whatsapp_animation"]').value;
        
        document.getElementById('color_hex').innerText = color;
        document.getElementById('hover_color_hex').innerText = document.getElementById('whatsapp_hover_color').value;

        const widget = document.getElementById('preview-widget');
        
        if (enabled == '0') {
            widget.style.display = 'none';
            return;
        } else {
            widget.style.display = 'flex';
        }

        widget.style.width = size + 'px';
        widget.style.height = size + 'px';
        widget.style.backgroundColor = color;

        // Reset positions
        widget.style.bottom = 'auto';
        widget.style.top = 'auto';
        widget.style.left = 'auto';
        widget.style.right = 'auto';

        // Apply Position and Offsets
        if (position.includes('bottom')) {
            widget.style.bottom = yOffset + 'px';
        } else {
            widget.style.top = yOffset + 'px';
        }

        if (position.includes('right')) {
            widget.style.right = xOffset + 'px';
        } else {
            widget.style.left = xOffset + 'px';
        }

        // Apply Animation classes
        widget.className = 'position-absolute d-flex align-items-center justify-content-center text-white rounded-circle shadow-lg transition-all';
        if (animation === 'bounce') {
            widget.classList.add('animate__animated', 'animate__bounce', 'animate__infinite', 'animate__slower');
        } else if (animation === 'pulse') {
            widget.classList.add('animate__animated', 'animate__pulse', 'animate__infinite');
        }
    }

    // Attach event listeners for color text updates
    document.getElementById('whatsapp_color').addEventListener('input', function() {
        document.getElementById('color_hex').innerText = this.value;
        updatePreview();
    });
    document.getElementById('whatsapp_hover_color').addEventListener('input', function() {
        document.getElementById('hover_color_hex').innerText = this.value;
    });

    // Initial render
    document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<!-- Include Animate.css for preview animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endsection
