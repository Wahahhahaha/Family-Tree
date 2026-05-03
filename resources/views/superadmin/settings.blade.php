@extends('layouts.app')

@section('title', __('management_setting.title'))
@section('body-class', 'page-family-tree page-management-setting')

@section('content')
@php
    $storedLogoPath = trim((string) ($systemSettings['logo_path'] ?? ''));
    $logoPreviewUrl = trim((string) ($systemSettings['logo_url'] ?? ''));
    if ($logoPreviewUrl === '' && $storedLogoPath !== '') {
        $logoPreviewUrl = (preg_match('#^https?://#i', $storedLogoPath) || str_starts_with($storedLogoPath, 'data:'))
            ? $storedLogoPath
            : asset(ltrim($storedLogoPath, '/'));
    }

    $resolvePhotoUrl = function (string $path): string {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(?:https?:|data:|blob:)#i', $path)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    };

    $landing = $landingPageSettings ?? [];
    $activeTab = $activeTab ?? 'website';
    $canAccessWebsiteSettings = !empty($canAccessWebsiteSettings);
    $landingPhotoFields = [
        'head_of_family_photo' => [
            'label' => __('management_setting.head_of_family_photo'),
            'help' => __('management_setting.visible_in_hero_card'),
        ],
        'created_by_photo' => [
            'label' => __('management_setting.created_by_photo'),
            'help' => __('management_setting.shown_on_created_by'),
        ],
        'designed_by_photo' => [
            'label' => __('management_setting.designed_by_photo'),
            'help' => __('management_setting.shown_on_designed_by'),
        ],
        'approved_by_photo' => [
            'label' => __('management_setting.approved_by_photo'),
            'help' => __('management_setting.shown_on_approved_by'),
        ],
        'acknowledged_by_photo' => [
            'label' => __('management_setting.acknowledged_by_photo'),
            'help' => __('management_setting.shown_on_acknowledged_by'),
        ],
    ];
@endphp

<div class="wrapper">
    <section class="settings-card settings-shell">
        <div class="settings-head settings-head--split">
            <div>
                <h2>{{ __('management_setting.heading') }}</h2>
                <p>{{ __('management_setting.description') }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div id="settingsAjaxAlert" class="alert-success hidden"></div>

        <div class="settings-tabs" role="tablist" aria-label="{{ __('management_setting.heading') }}">
            @if ($canAccessWebsiteSettings)
                <button type="button" class="settings-tab-btn {{ $activeTab === 'website' ? 'is-active' : '' }}" data-settings-tab="website">
                    {{ __('management_setting.website_settings') }}
                </button>
            @endif
            <button type="button" class="settings-tab-btn {{ $activeTab === 'landing' ? 'is-active' : '' }}" data-settings-tab="landing">
                {{ __('management_setting.landing_page_settings') }}
            </button>
        </div>

        <div class="settings-panels">
            @if ($canAccessWebsiteSettings)
                <section class="settings-panel {{ $activeTab === 'website' ? 'is-active' : '' }}" data-settings-panel="website" aria-labelledby="websiteSettingsTitle">
                    <div class="settings-panel-intro">
                        <div>
                            <h3 id="websiteSettingsTitle">{{ __('management_setting.website_settings') }}</h3>
                            <p>{{ __('management_setting.website_intro') }}</p>
                        </div>
                        <span class="settings-panel-tag">{{ __('management_setting.superadmin_only') }}</span>
                    </div>

                    <form id="systemSettingsForm" method="POST" action="{{ route('management.setting.save') }}" enctype="multipart/form-data" class="settings-form settings-form--single">
                        @csrf

                        <div class="settings-field full-width">
                            <label for="websiteName">{{ __('management_setting.website_name') }}</label>
                            <input
                                id="websiteName"
                                type="text"
                                name="website_name"
                                value="{{ old('website_name', $systemSettings['website_name'] ?? 'Family Tree System') }}"
                                placeholder="{{ __('management_setting.enter_website_name') }}"
                                required
                            >
                        </div>

                        <div class="settings-field full-width">
                            <label for="systemLogoInput">{{ __('management_setting.system_logo') }}</label>
                            <input id="systemLogoInput" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg" data-photo-preview-target="systemLogoPreview" data-photo-placeholder="systemLogoPlaceholder">
                            <small>{{ __('management_setting.allowed_logo_types') }}</small>
                        </div>

                        <div class="settings-preview full-width">
                            <span>{{ __('management_setting.current_logo_preview') }}</span>
                            <div class="settings-logo-box">
                                <img
                                    id="systemLogoPreview"
                                    src="{{ $logoPreviewUrl }}"
                                    alt="{{ __('management_setting.current_logo_preview') }}"
                                    class="{{ $logoPreviewUrl === '' ? 'hidden' : '' }}"
                                >
                                <div id="systemLogoPlaceholder" class="logo-placeholder {{ $logoPreviewUrl !== '' ? 'hidden' : '' }}">
                                    {{ __('management_setting.no_logo_uploaded') }}
                                </div>
                            </div>
                        </div>

                        <div class="settings-field">
                            <label for="systemContactInput">{{ __('management_setting.system_contact') }}</label>
                            <input
                                id="systemContactInput"
                                type="text"
                                name="system_contact"
                                value="{{ old('system_contact', $systemSettings['system_contact'] ?? '') }}"
                                placeholder="{{ __('management_setting.enter_system_contact') }}"
                            >
                        </div>

                        <div class="settings-field">
                            <label for="systemManagerInput">{{ __('management_setting.system_manager') }}</label>
                            <input
                                id="systemManagerInput"
                                type="text"
                                name="system_manager"
                                value="{{ old('system_manager', $systemSettings['system_manager'] ?? '') }}"
                                placeholder="{{ __('management_setting.enter_manager_name') }}"
                            >
                        </div>

                        <div class="settings-actions full-width">
                            <button type="submit" class="btn btn-primary">{{ __('management_setting.save_website_settings') }}</button>
                        </div>
                    </form>
                </section>
            @endif

            <section class="settings-panel {{ $activeTab === 'landing' || !$canAccessWebsiteSettings ? 'is-active' : '' }}" data-settings-panel="landing" aria-labelledby="landingSettingsTitle">
                <div class="settings-panel-intro">
                    <div>
                        <h3 id="landingSettingsTitle">{{ __('management_setting.landing_page_settings') }}</h3>
                        <p>{{ __('management_setting.landing_intro') }}</p>
                    </div>
                    <span class="settings-panel-tag">{{ __('management_setting.visible_to_guests') }}</span>
                </div>

                <form id="landingSettingsForm" method="POST" action="{{ route('management.setting.landing.save') }}" enctype="multipart/form-data" class="settings-form settings-form--landing">
                    @csrf

                    <div class="settings-field full-width">
                        <label for="landingFamilyName">{{ __('management_setting.family_name') }}</label>
                        <input
                            id="landingFamilyName"
                            type="text"
                            name="family_name"
                            value="{{ old('family_name', $landing['family_name'] ?? '') }}"
                            placeholder="{{ __('management_setting.enter_family_name') }}"
                            required
                        >
                    </div>

                    <div class="settings-field full-width">
                        <label for="landingDescription">{{ __('management_setting.description') }}</label>
                        <textarea
                            id="landingDescription"
                            name="description"
                            rows="4"
                            placeholder="{{ __('management_setting.describe_family_page') }}"
                        >{{ old('description', $landing['description'] ?? '') }}</textarea>
                    </div>

                    <div class="settings-field full-width">
                        <label for="landingHeadName">{{ __('management_setting.head_of_family_name') }}</label>
                        <input
                            id="landingHeadName"
                            type="text"
                            name="head_of_family_name"
                            value="{{ old('head_of_family_name', $landing['head_of_family_name'] ?? '') }}"
                            placeholder="{{ __('management_setting.enter_family_head_name') }}"
                            required
                        >
                    </div>

                    <div class="settings-field full-width">
                        <label for="landingHeadMessage">{{ __('management_setting.head_of_family_message') }}</label>
                        <textarea
                            id="landingHeadMessage"
                            name="head_of_family_message"
                            rows="4"
                            placeholder="{{ __('management_setting.write_welcome_message') }}"
                        >{{ old('head_of_family_message', $landing['head_of_family_message'] ?? '') }}</textarea>
                    </div>

                    <div class="landing-hero-photo full-width">
                        <div class="landing-photo-card landing-photo-card--hero">
                            <div class="landing-photo-card__preview">
                                <img
                                    id="head_of_family_photoPreview"
                                    src="{{ $resolvePhotoUrl($landing['head_of_family_photo'] ?? '') }}"
                                    alt="{{ __('management_setting.head_of_family_photo') }}"
                                    class="{{ $resolvePhotoUrl($landing['head_of_family_photo'] ?? '') === '' ? 'hidden' : '' }}"
                                >
                                <div id="head_of_family_photoPlaceholder" class="logo-placeholder {{ $resolvePhotoUrl($landing['head_of_family_photo'] ?? '') !== '' ? 'hidden' : '' }}">
                                    {{ __('management_setting.no_photo_uploaded') }}
                                </div>
                            </div>
                            <div class="landing-photo-card__body">
                                <label for="head_of_family_photo">{{ __('management_setting.head_of_family_photo') }}</label>
                                <input
                                    id="head_of_family_photo"
                                    type="file"
                                    name="head_of_family_photo"
                                    accept="image/*"
                                    data-photo-preview-target="head_of_family_photoPreview"
                                    data-photo-placeholder="head_of_family_photoPlaceholder"
                                >
                                <small>{{ $landingPhotoFields['head_of_family_photo']['help'] }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="full-width section-divider">
                        <h4>{{ __('management_setting.approval_board') }}</h4>
                        <p>{{ __('management_setting.approval_board_description') }}</p>
                    </div>

                    <div class="landing-approval-grid full-width">
                        @foreach ([
                            'created_by' => __('management_setting.created_by'),
                            'designed_by' => __('management_setting.designed_by'),
                            'approved_by' => __('management_setting.approved_by'),
                            'acknowledged_by' => __('management_setting.acknowledged_by'),
                        ] as $prefix => $label)
                            @php
                                $nameField = $prefix . '_name';
                                $photoField = $prefix . '_photo';
                                $previewUrl = $resolvePhotoUrl($landing[$photoField] ?? '');
                            @endphp
                            <article class="landing-photo-card">
                                <div class="landing-photo-card__preview">
                                    <img
                                        id="{{ $photoField }}Preview"
                                        src="{{ $previewUrl }}"
                                        alt="{{ $label }}"
                                        class="{{ $previewUrl === '' ? 'hidden' : '' }}"
                                    >
                                    <div id="{{ $photoField }}Placeholder" class="logo-placeholder {{ $previewUrl !== '' ? 'hidden' : '' }}">
                                        {{ __('management_setting.no_photo_uploaded') }}
                                    </div>
                                </div>
                                <div class="landing-photo-card__body">
                                    <label for="{{ $prefix }}Name">{{ $label }}</label>
                                    <input
                                        id="{{ $prefix }}Name"
                                        type="text"
                                        name="{{ $nameField }}"
                                        value="{{ old($nameField, $landing[$nameField] ?? '') }}"
                                        placeholder="{{ __('management_setting.enter_name', ['label' => strtolower($label)]) }}"
                                        required
                                    >
                                    <input
                                        id="{{ $photoField }}"
                                        type="file"
                                        name="{{ $photoField }}"
                                        accept="image/*"
                                        data-photo-preview-target="{{ $photoField }}Preview"
                                        data-photo-placeholder="{{ $photoField }}Placeholder"
                                    >
                                    <small>{{ $landingPhotoFields[$photoField]['help'] }}</small>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="settings-actions full-width">
                        <button type="submit" class="btn btn-primary">{{ __('management_setting.save_landing_page_settings') }}</button>
                    </div>
                </form>
            </section>
        </div>
    </section>
</div>
    <div id="photoCropModal" class="photo-crop-modal hidden" role="dialog" aria-modal="true" aria-labelledby="photoCropTitle">
        <div class="photo-crop-backdrop"></div>
        <div class="photo-crop-card">
        <h4 id="photoCropTitle">{{ __('management_setting.crop_website_logo') }}</h4>
        <p id="photoCropDescription">{{ __('management_setting.crop_logo_description') }}</p>
            <div class="photo-crop-stage-wrap">
                <canvas id="photoCropCanvas" class="photo-crop-canvas" width="320" height="320"></canvas>
            </div>
            <div class="photo-crop-actions">
                <button id="photoCropCancelBtn" type="button" class="btn btn-ghost">{{ __('management_setting.cancel') }}</button>
                <button id="photoCropApplyBtn" type="button" class="btn btn-primary">{{ __('management_setting.apply') }}</button>
            </div>
        </div>
    </div>
    <div id="settingsPhotoCropModal" class="photo-crop-modal hidden" role="dialog" aria-modal="true" aria-labelledby="settingsPhotoCropTitle">
        <div class="photo-crop-backdrop"></div>
        <div class="photo-crop-card">
            <h4 id="settingsPhotoCropTitle">{{ __('management_setting.crop_photo') }}</h4>
            <p id="settingsPhotoCropDescription">{{ __('management_setting.crop_photo_description') }}</p>
            <div class="photo-crop-stage-wrap">
                <canvas id="settingsPhotoCropCanvas" class="photo-crop-canvas" width="320" height="320"></canvas>
                <div id="settingsPhotoCropFrame" class="photo-crop-frame" aria-hidden="true">
                    <span class="photo-crop-frame-handle photo-crop-frame-handle--nw" data-crop-handle="nw"></span>
                    <span class="photo-crop-frame-handle photo-crop-frame-handle--ne" data-crop-handle="ne"></span>
                    <span class="photo-crop-frame-handle photo-crop-frame-handle--sw" data-crop-handle="sw"></span>
                    <span class="photo-crop-frame-handle photo-crop-frame-handle--se" data-crop-handle="se"></span>
                </div>
            </div>
            <div class="photo-crop-actions">
                <button id="settingsPhotoCropCancelBtn" type="button" class="btn btn-ghost">{{ __('management_setting.cancel') }}</button>
                <button id="settingsPhotoCropApplyBtn" type="button" class="btn btn-primary">{{ __('management_setting.apply') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabButtons = Array.prototype.slice.call(document.querySelectorAll('[data-settings-tab]'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('[data-settings-panel]'));
    var cropModal = document.getElementById('settingsPhotoCropModal');
    var cropCanvas = document.getElementById('settingsPhotoCropCanvas');
    var cropFrame = document.getElementById('settingsPhotoCropFrame');
    var cropApplyBtn = document.getElementById('settingsPhotoCropApplyBtn');
    var cropCancelBtn = document.getElementById('settingsPhotoCropCancelBtn');
    var cropTitle = document.getElementById('settingsPhotoCropTitle');
    var cropDescription = document.getElementById('settingsPhotoCropDescription');
    var settingsText = {
        cropWebsiteLogo: @json(__('management_setting.crop_website_logo')),
        cropPhoto: @json(__('management_setting.crop_photo')),
        cropLogoDescription: @json(__('management_setting.crop_logo_description')),
        cropPhotoDescription: @json(__('management_setting.crop_photo_description')),
        photoLabel: @json(__('management_setting.photo'))
    };
    var cropCloseTimer = 0;
    var cropRequestToken = 0;
    var cropState = {
        input: null,
        preview: null,
        placeholder: null,
        previewSrc: '',
        previewHidden: true,
        placeholderHidden: false,
        file: null,
        image: null,
        imageUrl: '',
        scaleBase: 1,
        zoom: 1,
        offsetX: 0,
        offsetY: 0,
        dragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOffsetX: 0,
        dragOffsetY: 0,
        pinching: false,
        pinchStartDistance: 0,
        pinchStartZoom: 1,
        pinchCenterX: 0,
        pinchCenterY: 0,
        cropRect: {
            x: 50,
            y: 50,
            size: 220
        },
        cropAction: '',
        cropActionHandle: '',
        cropActionPointerId: null,
        cropActionStartX: 0,
        cropActionStartY: 0,
        cropActionStartRect: {
            x: 50,
            y: 50,
            size: 220
        }
    };

    function activateTab(tabName) {
        tabButtons.forEach(function (button) {
            button.classList.toggle('is-active', button.getAttribute('data-settings-tab') === tabName);
        });

        panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-settings-panel') === tabName);
        });
    }

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var tabName = button.getAttribute('data-settings-tab');
            if (!tabName) {
                return;
            }
            activateTab(tabName);
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', tabName);
                window.history.replaceState({}, '', url.toString());
            } catch (error) {
                // Ignore URL update failures.
            }
        });
    });

    function getPreviewElements(input) {
        var previewId = input.getAttribute('data-photo-preview-target');
        var placeholderId = input.getAttribute('data-photo-placeholder');

        return {
            preview: previewId ? document.getElementById(previewId) : null,
            placeholder: placeholderId ? document.getElementById(placeholderId) : null
        };
    }

    function getInputTitle(input) {
        var label = '';
        var labelElement = input.closest('.settings-field, .landing-photo-card__body') ? input.closest('.settings-field, .landing-photo-card__body').querySelector('label') : null;
        if (labelElement) {
            label = (labelElement.textContent || '').trim();
        }

        if (!label) {
            label = settingsText.photoLabel;
        }

        if (input.id === 'systemLogoInput') {
            return settingsText.cropWebsiteLogo;
        }

        return settingsText.cropPhoto + ' ' + label;
    }

    function getInputDescription(input) {
        if (input.id === 'systemLogoInput') {
            return settingsText.cropLogoDescription;
        }

        return settingsText.cropPhotoDescription;
    }

    function restorePreviewState() {
        if (!cropState.preview || !cropState.placeholder) {
            return;
        }

        if (cropState.previewSrc) {
            cropState.preview.src = cropState.previewSrc;
        } else {
            cropState.preview.removeAttribute('src');
        }

        cropState.preview.classList.toggle('hidden', cropState.previewHidden);
        cropState.placeholder.classList.toggle('hidden', cropState.placeholderHidden);
    }

    function updatePreviewWithFile(file) {
        if (!cropState.preview || !cropState.placeholder) {
            return;
        }

        var objectUrl = URL.createObjectURL(file);
        cropState.preview.src = objectUrl;
        cropState.preview.classList.remove('hidden');
        cropState.placeholder.classList.add('hidden');
        cropState.preview.onload = function () {
            URL.revokeObjectURL(objectUrl);
        };
    }

    function setInputFile(input, file) {
        if (!input || typeof DataTransfer === 'undefined') {
            return;
        }

        var dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
    }

    function closeCropModal(restoreState) {
        if (!cropModal) {
            return;
        }

        if (cropCloseTimer) {
            window.clearTimeout(cropCloseTimer);
        }

        cropRequestToken += 1;
        cropModal.classList.add('is-closing');
        cropModal.classList.remove('is-open');
        cropState.dragging = false;

        if (cropCanvas) {
            cropCanvas.classList.remove('is-dragging');
            cropCanvas.style.cursor = 'grab';
        }

        if (cropState.imageUrl) {
            URL.revokeObjectURL(cropState.imageUrl);
        }

        cropState.image = null;
        cropState.imageUrl = '';

        if (restoreState) {
            restorePreviewState();
            if (cropState.input) {
                cropState.input.value = '';
            }
        }

        cropState.input = null;
        cropState.preview = null;
        cropState.placeholder = null;
        cropState.previewSrc = '';
        cropState.previewHidden = true;
        cropState.placeholderHidden = false;
        cropState.file = null;
        cropState.scaleBase = 1;
        cropState.zoom = 1;
        cropState.offsetX = 0;
        cropState.offsetY = 0;
        cropState.pinching = false;
        cropState.pinchStartDistance = 0;
        cropState.pinchStartZoom = 1;
        cropState.pinchCenterX = 0;
        cropState.pinchCenterY = 0;
        cropState.cropAction = '';
        cropState.cropActionHandle = '';
        cropState.cropActionPointerId = null;
        cropState.cropActionStartX = 0;
        cropState.cropActionStartY = 0;
        cropState.cropActionStartRect = {
            x: 50,
            y: 50,
            size: 220
        };
        cropState.cropRect = {
            x: 50,
            y: 50,
            size: 220
        };

        cropCloseTimer = window.setTimeout(function () {
            cropModal.classList.add('hidden');
            cropModal.classList.remove('is-closing');
        }, 200);
    }

    function clampNumber(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function getCropRect() {
        if (!cropState.cropRect) {
            cropState.cropRect = { x: 50, y: 50, size: 220 };
        }

        return cropState.cropRect;
    }

    function clampCropRect() {
        if (!cropCanvas) {
            return;
        }

        var rect = getCropRect();
        var canvasSize = cropCanvas.width;
        var minSize = 140;
        var maxSize = canvasSize - 20;

        rect.size = clampNumber(Math.round(rect.size), minSize, maxSize);
        rect.x = clampNumber(Math.round(rect.x), 0, canvasSize - rect.size);
        rect.y = clampNumber(Math.round(rect.y), 0, canvasSize - rect.size);
    }

    function clampImageToCropRect() {
        if (!cropCanvas || !cropState.image) {
            return;
        }

        var rect = getCropRect();
        var canvasSize = cropCanvas.width;
        var scaledWidth = cropState.image.width * cropState.scaleBase * cropState.zoom;
        var scaledHeight = cropState.image.height * cropState.scaleBase * cropState.zoom;
        var minX = rect.x + rect.size - scaledWidth;
        var maxX = rect.x;
        var minY = rect.y + rect.size - scaledHeight;
        var maxY = rect.y;

        if (scaledWidth <= rect.size) {
            cropState.offsetX = rect.x + (rect.size - scaledWidth) / 2;
        } else {
            cropState.offsetX = clampNumber(cropState.offsetX, minX, maxX);
        }

        if (scaledHeight <= rect.size) {
            cropState.offsetY = rect.y + (rect.size - scaledHeight) / 2;
        } else {
            cropState.offsetY = clampNumber(cropState.offsetY, minY, maxY);
        }

        cropState.offsetX = clampNumber(cropState.offsetX, canvasSize - scaledWidth, canvasSize);
        cropState.offsetY = clampNumber(cropState.offsetY, canvasSize - scaledHeight, canvasSize);
    }

    function drawCropCanvas() {
        if (!cropCanvas || !cropState.image) {
            return;
        }

        var ctx = cropCanvas.getContext('2d');
        var canvasSize = cropCanvas.width;
        var scaledWidth = cropState.image.width * cropState.scaleBase * cropState.zoom;
        var scaledHeight = cropState.image.height * cropState.scaleBase * cropState.zoom;
        var rect = getCropRect();

        ctx.clearRect(0, 0, canvasSize, canvasSize);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvasSize, canvasSize);
        ctx.drawImage(cropState.image, cropState.offsetX, cropState.offsetY, scaledWidth, scaledHeight);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
        ctx.fillRect(0, 0, canvasSize, rect.y);
        ctx.fillRect(0, rect.y, rect.x, rect.size);
        ctx.fillRect(rect.x + rect.size, rect.y, canvasSize - (rect.x + rect.size), rect.size);
        ctx.fillRect(0, rect.y + rect.size, canvasSize, canvasSize - (rect.y + rect.size));
    }

    function renderCropFrame() {
        if (!cropFrame) {
            return;
        }

        var rect = getCropRect();
        cropFrame.style.left = rect.x + 'px';
        cropFrame.style.top = rect.y + 'px';
        cropFrame.style.width = rect.size + 'px';
        cropFrame.style.height = rect.size + 'px';
    }

    function renderCropCanvas() {
        clampCropRect();
        clampImageToCropRect();
        drawCropCanvas();
        renderCropFrame();
    }

    function getCanvasPointFromClient(clientX, clientY) {
        var rect = cropCanvas.getBoundingClientRect();

        return {
            x: (clientX - rect.left) * (cropCanvas.width / rect.width),
            y: (clientY - rect.top) * (cropCanvas.height / rect.height)
        };
    }

    function getTouchDistance(touchA, touchB) {
        var deltaX = touchA.clientX - touchB.clientX;
        var deltaY = touchA.clientY - touchB.clientY;
        return Math.sqrt(deltaX * deltaX + deltaY * deltaY);
    }

    function beginCropFrameAction(actionType, handleName, event) {
        if (!cropState.image) {
            return;
        }

        cropState.cropAction = actionType;
        cropState.cropActionHandle = handleName || '';
        cropState.cropActionPointerId = typeof event.pointerId === 'number' ? event.pointerId : null;
        cropState.cropActionStartX = event.clientX;
        cropState.cropActionStartY = event.clientY;
        cropState.cropActionStartRect = {
            x: getCropRect().x,
            y: getCropRect().y,
            size: getCropRect().size
        };

        if (event.currentTarget && typeof event.currentTarget.setPointerCapture === 'function' && cropState.cropActionPointerId !== null) {
            try {
                event.currentTarget.setPointerCapture(cropState.cropActionPointerId);
            } catch (error) {
                // Ignore pointer capture failures.
            }
        }

        event.preventDefault();
    }

    function updateCropFrameAction(event) {
        if (!cropState.image || !cropState.cropAction) {
            return;
        }

        if (cropState.cropActionPointerId !== null && typeof event.pointerId === 'number' && event.pointerId !== cropState.cropActionPointerId) {
            return;
        }

        var dx = event.clientX - cropState.cropActionStartX;
        var dy = event.clientY - cropState.cropActionStartY;
        var nextRect = {
            x: cropState.cropActionStartRect.x,
            y: cropState.cropActionStartRect.y,
            size: cropState.cropActionStartRect.size
        };

        if (cropState.cropAction === 'move') {
            nextRect.x = cropState.cropActionStartRect.x + dx;
            nextRect.y = cropState.cropActionStartRect.y + dy;
        } else if (cropState.cropAction === 'resize') {
            var handle = cropState.cropActionHandle || 'se';
            var delta = 0;

            if (handle.indexOf('e') !== -1) {
                delta += dx;
            } else {
                delta -= dx;
            }
            if (handle.indexOf('s') !== -1) {
                delta += dy;
            } else {
                delta -= dy;
            }

            delta = delta / 2;
            nextRect.size = cropState.cropActionStartRect.size + delta;
            if (handle.indexOf('w') !== -1) {
                nextRect.x = cropState.cropActionStartRect.x + (cropState.cropActionStartRect.size - nextRect.size);
            }
            if (handle.indexOf('n') !== -1) {
                nextRect.y = cropState.cropActionStartRect.y + (cropState.cropActionStartRect.size - nextRect.size);
            }
        }

        cropState.cropRect = nextRect;
        renderCropCanvas();
        event.preventDefault();
    }

    function endCropFrameAction(event) {
        if (cropState.cropActionPointerId !== null && typeof event.pointerId === 'number' && event.pointerId !== cropState.cropActionPointerId) {
            return;
        }

        cropState.cropAction = '';
        cropState.cropActionHandle = '';
        cropState.cropActionPointerId = null;
    }

    function openCropModal(input, file) {
        if (!cropModal || !cropCanvas || !file) {
            return;
        }

        var previewElements = getPreviewElements(input);
        cropState.input = input;
        cropState.preview = previewElements.preview;
        cropState.placeholder = previewElements.placeholder;
        cropState.previewSrc = cropState.preview ? (cropState.preview.getAttribute('src') || '') : '';
        cropState.previewHidden = cropState.preview ? cropState.preview.classList.contains('hidden') : true;
        cropState.placeholderHidden = cropState.placeholder ? cropState.placeholder.classList.contains('hidden') : false;
        cropState.file = file;

        cropTitle.textContent = getInputTitle(input);
        cropDescription.textContent = getInputDescription(input);

        var requestToken = ++cropRequestToken;
        cropState.image = new Image();
        cropState.imageUrl = URL.createObjectURL(file);
        cropState.image.onload = function () {
            if (requestToken !== cropRequestToken) {
                URL.revokeObjectURL(cropState.imageUrl);
                return;
            }

            var size = cropCanvas.width;
            cropState.scaleBase = Math.max(size / cropState.image.width, size / cropState.image.height);
            cropState.zoom = 1;
            cropState.offsetX = (size - cropState.image.width * cropState.scaleBase) / 2;
            cropState.offsetY = (size - cropState.image.height * cropState.scaleBase) / 2;
            cropState.cropRect = {
                size: 220,
                x: Math.round((size - 220) / 2),
                y: Math.round((size - 220) / 2)
            };
            cropState.pinching = false;
            cropState.pinchStartDistance = 0;
            cropState.pinchStartZoom = 1;
            cropState.pinchCenterX = 0;
            cropState.pinchCenterY = 0;
            renderCropCanvas();
            cropModal.classList.remove('hidden', 'is-closing');
            cropModal.classList.add('is-open');
            cropCanvas.style.cursor = 'grab';
        };
        cropState.image.src = cropState.imageUrl;
    }

    function applyCropModal() {
        if (!cropCanvas || !cropState.image || !cropState.input) {
            return;
        }

        var outputSize = 512;
        var exportCanvas = document.createElement('canvas');
        exportCanvas.width = outputSize;
        exportCanvas.height = outputSize;

        var ctx = exportCanvas.getContext('2d');
        var rect = getCropRect();
        var sourceScale = outputSize / rect.size;
        var drawWidth = cropState.image.width * cropState.scaleBase * cropState.zoom * sourceScale;
        var drawHeight = cropState.image.height * cropState.scaleBase * cropState.zoom * sourceScale;
        var drawX = (cropState.offsetX - rect.x) * sourceScale;
        var drawY = (cropState.offsetY - rect.y) * sourceScale;

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, outputSize, outputSize);
        ctx.drawImage(cropState.image, drawX, drawY, drawWidth, drawHeight);

        exportCanvas.toBlob(function (blob) {
            if (!blob) {
                closeCropModal(false);
                return;
            }

            var croppedFile = new File([blob], (cropState.file && cropState.file.name ? cropState.file.name.replace(/\.[^.]+$/, '') : 'cropped-photo') + '.png', {
                type: 'image/png'
            });

            setInputFile(cropState.input, croppedFile);
            updatePreviewWithFile(croppedFile);
            closeCropModal(false);
        }, 'image/png');
    }

    document.querySelectorAll('input[type="file"][data-photo-preview-target]').forEach(function (input) {
        if (input.id === 'systemLogoInput') {
            return;
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0] ? input.files[0] : null;

            if (!file) {
                var elements = getPreviewElements(input);
                if (elements.preview) {
                    elements.preview.removeAttribute('src');
                    elements.preview.classList.add('hidden');
                }
                if (elements.placeholder) {
                    elements.placeholder.classList.remove('hidden');
                }
                return;
            }

            openCropModal(input, file);
        });
    });

    if (cropCanvas) {
        cropCanvas.addEventListener('mousedown', function (event) {
            if (!cropState.image) {
                return;
            }
            cropState.dragging = true;
            cropState.dragStartX = event.clientX;
            cropState.dragStartY = event.clientY;
            cropState.dragOffsetX = cropState.offsetX;
            cropState.dragOffsetY = cropState.offsetY;
            cropCanvas.classList.add('is-dragging');
        });

        window.addEventListener('mousemove', function (event) {
            if (!cropState.dragging || !cropState.image) {
                return;
            }
            cropState.offsetX = cropState.dragOffsetX + (event.clientX - cropState.dragStartX);
            cropState.offsetY = cropState.dragOffsetY + (event.clientY - cropState.dragStartY);
            renderCropCanvas();
        });

        window.addEventListener('mouseup', function () {
            if (!cropState.dragging) {
                return;
            }
            cropState.dragging = false;
            cropCanvas.classList.remove('is-dragging');
            cropCanvas.style.cursor = 'grab';
        });

        cropCanvas.addEventListener('touchstart', function (event) {
            if (!cropState.image || !event.touches || !event.touches[0]) {
                return;
            }

            if (event.touches.length >= 2) {
                var pinchA = event.touches[0];
                var pinchB = event.touches[1];
                var centerX = (pinchA.clientX + pinchB.clientX) / 2;
                var centerY = (pinchA.clientY + pinchB.clientY) / 2;
                var centerPoint = getCanvasPointFromClient(centerX, centerY);

                cropState.pinching = true;
                cropState.dragging = false;
                cropState.pinchStartDistance = getTouchDistance(pinchA, pinchB);
                cropState.pinchStartZoom = cropState.zoom;
                cropState.pinchCenterX = centerPoint.x;
                cropState.pinchCenterY = centerPoint.y;
                event.preventDefault();
                return;
            }

            cropState.dragging = true;
            cropState.dragStartX = event.touches[0].clientX;
            cropState.dragStartY = event.touches[0].clientY;
            cropState.dragOffsetX = cropState.offsetX;
            cropState.dragOffsetY = cropState.offsetY;
            cropCanvas.classList.add('is-dragging');
            event.preventDefault();
        }, { passive: false });

        cropCanvas.addEventListener('touchmove', function (event) {
            if (!cropState.image || !event.touches || !event.touches[0]) {
                return;
            }

            if (cropState.pinching && event.touches.length >= 2) {
                var moveA = event.touches[0];
                var moveB = event.touches[1];
                var currentDistance = getTouchDistance(moveA, moveB);
                var nextZoom = clampNumber(
                    cropState.pinchStartZoom * (currentDistance / Math.max(cropState.pinchStartDistance, 1)),
                    1,
                    3
                );
                var oldScale = cropState.scaleBase * cropState.zoom;
                var nextScale = cropState.scaleBase * nextZoom;
                var imageX = (cropState.pinchCenterX - cropState.offsetX) / oldScale;
                var imageY = (cropState.pinchCenterY - cropState.offsetY) / oldScale;

                cropState.zoom = nextZoom;
                cropState.offsetX = cropState.pinchCenterX - imageX * nextScale;
                cropState.offsetY = cropState.pinchCenterY - imageY * nextScale;
                renderCropCanvas();
                event.preventDefault();
                return;
            }

            if (!cropState.dragging) {
                return;
            }

            cropState.offsetX = cropState.dragOffsetX + (event.touches[0].clientX - cropState.dragStartX);
            cropState.offsetY = cropState.dragOffsetY + (event.touches[0].clientY - cropState.dragStartY);
            renderCropCanvas();
            event.preventDefault();
        }, { passive: false });

        cropCanvas.addEventListener('touchend', function () {
            cropState.dragging = false;
            cropState.pinching = false;
            cropCanvas.classList.remove('is-dragging');
        });

        cropCanvas.addEventListener('touchcancel', function () {
            cropState.dragging = false;
            cropState.pinching = false;
            cropCanvas.classList.remove('is-dragging');
        });

        cropCanvas.addEventListener('wheel', function (event) {
            if (!cropState.image) {
                return;
            }

            event.preventDefault();
            var zoomDelta = event.ctrlKey ? (-event.deltaY * 0.002) : (event.deltaY > 0 ? -0.1 : 0.1);
            var nextZoom = clampNumber(cropState.zoom + zoomDelta, 1, 3);
            var wheelPoint = getCanvasPointFromClient(event.clientX, event.clientY);
            var oldScale = cropState.scaleBase * cropState.zoom;
            var nextScale = cropState.scaleBase * nextZoom;
            var imageX = (wheelPoint.x - cropState.offsetX) / oldScale;
            var imageY = (wheelPoint.y - cropState.offsetY) / oldScale;

            cropState.zoom = nextZoom;
            cropState.offsetX = wheelPoint.x - imageX * nextScale;
            cropState.offsetY = wheelPoint.y - imageY * nextScale;
            renderCropCanvas();
        }, { passive: false });
    }

    if (cropFrame) {
        cropFrame.addEventListener('pointerdown', function (event) {
            var handle = event.target && typeof event.target.getAttribute === 'function'
                ? event.target.getAttribute('data-crop-handle')
                : '';

            if (handle) {
                beginCropFrameAction('resize', handle, event);
                return;
            }

            beginCropFrameAction('move', '', event);
        });
    }

    window.addEventListener('pointermove', updateCropFrameAction);
    window.addEventListener('pointerup', endCropFrameAction);
    window.addEventListener('pointercancel', endCropFrameAction);

    if (cropApplyBtn) {
        cropApplyBtn.addEventListener('click', applyCropModal);
    }

    if (cropCancelBtn) {
        cropCancelBtn.addEventListener('click', function () {
            closeCropModal(true);
        });
    }

    if (cropModal) {
        cropModal.addEventListener('click', function (event) {
            if (event.target && event.target.classList && event.target.classList.contains('photo-crop-backdrop')) {
                closeCropModal(true);
            }
        });
    }
});
</script>
@endsection
