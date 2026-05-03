@extends('layouts.app')

@section('title', __('letters.show_title'))

<?php $pageClass = 'page-family-tree page-letters'; ?>

@section('styles')
<style>
    .letter-stage { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
    .letter-paper { 
        background: #fffef0; padding: 60px; max-width: 800px; width: 100%; border-radius: 4px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1), inset 0 0 100px rgba(0,0,0,0.02);
        position: relative; font-family: 'Lora', serif; line-height: 32px; /* Sesuai jarak garis */
        transform: rotate(-0.5deg);
    }
    /* Garis-garis kertas ditaruh di background agar tidak menimpa teks */
    .letter-paper::before {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background-image: repeating-linear-gradient(transparent, transparent 31px, #e2e8f0 31px, #e2e8f0 32px);
        z-index: 0;
        pointer-events: none;
    }
    .letter-content-wrapper { position: relative; z-index: 1; }
    .letter-header { margin-bottom: 40px; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; position: relative; z-index: 1; }
    .letter-footer { margin-top: 50px; text-align: right; font-style: italic; color: #718096; position: relative; z-index: 1; }
    .letter-lockbox {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid #fbd38d;
        border-radius: 16px;
        padding: 28px;
        text-align: center;
        color: #744210;
    }
    .letter-lockbox h2 { margin: 12px 0 8px; font-size: 1.5rem; font-weight: 800; color: #92400e; }
    main { padding-top: 20px !important; }
    
    /* Markdown Rendering Styles */
    .md-render { font-size: 1.1rem; color: #2d3748; }
    .md-render h1, .md-render h2, .md-render h3 { color: #1a365d; margin-bottom: 15px; line-height: 1.2; }
    .md-render p { margin-bottom: 32px; } /* Jaga agar paragraf tetap sejajar garis */
    .md-render blockquote { border-left: 4px solid #3182ce; padding-left: 20px; font-style: italic; color: #4a5568; margin: 20px 0; }
    .md-render strong { color: #1a365d; font-weight: 800; }
</style>
@endsection

@section('content')
<div class="wrapper">
    <div style="margin-bottom: 20px;">
        <a href="/letters" style="text-decoration: none;"><button class="btn btn-ghost" type="button"><i data-lucide="arrow-left" style="width: 18px; margin-right: 8px;"></i> {{ __('letters.back_to_inbox') }}</button></a>
    </div>

    <div class="letter-stage">
        <div class="letter-paper">
            <div class="letter-header">
                <h1 style="margin: 0; font-family: 'Sora', sans-serif; font-weight: 800;">{{ $letter->subject }}</h1>
                <div style="color: #718096; font-size: 0.9rem; margin-top: 10px;">{{ __('letters.sent_by', ['name' => $letter->sender_name, 'date' => date('d M Y, H:i', strtotime($letter->created_at))]) }}</div>
            </div>

            <div class="letter-content-wrapper">
                @if($canReadContent)
                    <div id="content-render" class="md-render">
                        <!-- Markdown will be rendered here -->
                    </div>
                @else
                    <div class="letter-lockbox">
                        <i data-lucide="lock" style="width: 34px; height: 34px;"></i>
                            <h2>{{ __('letters.letter_is_locked') }}</h2>
                        <p style="margin: 0; line-height: 1.8;">
                            {{ $unlockState['description'] }}
                        </p>
                        @if(!empty($unlockState['unlock_at']))
                            <p style="margin: 12px 0 0; font-weight: 700;">
                                {{ __('letters.available_on', ['date' => \Carbon\Carbon::parse($unlockState['unlock_at'])->format('d M Y')]) }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="letter-footer">
                {{ __('letters.sincerely_yours') }}<br>
                <strong style="font-size: 1.2rem; color: #1a365d;">{{ $letter->sender_name }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Hidden element to store raw markdown -->
<script id="markdown-raw" type="text/template">{{ $letter->content }}</script>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($canReadContent)
        const raw = document.getElementById('markdown-raw').innerHTML;
        const target = document.getElementById('content-render');
        
        // Configure marked to be more robust
        marked.setOptions({
            breaks: true,
            gfm: true
        });

        target.innerHTML = marked.parse(raw);
        @endif
    });
</script>
@endsection
