@extends('layouts.app')

@section('title', __('letters.index_title'))

<?php $pageClass = 'page-family-tree page-letters'; ?>

@section('styles')
<style>
    .letters-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
    .letter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; margin-top: 20px; }
    .envelope { 
        background: #fff; border-radius: 12px; padding: 25px; text-align: center; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; 
        cursor: pointer; transition: all 0.3s; position: relative;
    }
    .envelope:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
    .envelope.unread { border-left: 5px solid #e53e3e; }
    .envelope i { font-size: 3rem; color: #3182ce; margin-bottom: 15px; display: block; }
    .letter-subject { font-weight: 800; color: #2d3748; font-size: 1.1rem; margin-bottom: 5px; }
    .letter-from { font-size: 0.85rem; color: #718096; }
    .unread-dot { position: absolute; top: 15px; right: 15px; width: 12px; height: 12px; background: #e53e3e; border-radius: 50%; }
    .letter-access-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        background: #ebf8ff;
        color: #2b6cb0;
    }
    .letter-access-tag.locked {
        background: #fff5f5;
        color: #c53030;
    }
    main { padding-top: 20px !important; }

    /* Tabs Styling */
    .tabs-group { display: flex; gap: 10px; margin-top: 30px; border-bottom: 2px solid #f7fafc; padding-bottom: 10px; }
    .tab-btn { padding: 10px 25px; border-radius: 10px; font-weight: 700; cursor: pointer; border: none; background: transparent; color: #718096; transition: all 0.3s; }
    .tab-btn.active { background: #ebf8ff; color: #3182ce; }
</style>
@endsection

@section('content')
<div class="letters-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-family: 'Sora', sans-serif; font-weight: 800; color: #1a365d;">{{ __('letters.index_title') }}</h1>
        <a href="/letters/create" style="text-decoration: none;"><button class="btn btn-primary">{{ __('letters.write_letter') }}</button></a>
    </div>

    <div class="tabs-group">
        <button onclick="showTab('inbox')" id="tab-btn-inbox" class="tab-btn active">{{ __('letters.inbox') }}</button>
        <button onclick="showTab('sent')" id="tab-btn-sent" class="tab-btn">{{ __('letters.sent_letters') }}</button>
    </div>

    <div id="tab-inbox" class="tab-content">
        <div class="letter-grid">
            @forelse($inbox as $l)
                <a href="/letters/{{ $l->id }}" style="text-decoration: none; color: inherit;">
                    <div class="envelope {{ !$l->read_at ? 'unread' : '' }}">
                        @if(!$l->read_at)<div class="unread-dot"></div>@endif
                        <i data-lucide="mail"></i>
                        <div class="letter-subject">{{ $l->subject }}</div>
                        <div class="letter-from">{{ __('letters.from', ['name' => $l->sender_name]) }}</div>
                        @if(($l->unlock_type ?? 'immediate') !== 'immediate')
                            <div class="letter-access-tag locked">
                                <i data-lucide="lock" style="width: 12px; height: 12px;"></i>
                                @if(($l->unlock_type ?? '') === 'age')
                                    {{ __('letters.unlock_at_age', ['age' => (int) ($l->unlock_value ?? 0)]) }}
                                @else
                                    {{ __('letters.unlock_after_years', ['years' => (int) ($l->unlock_value ?? 0)]) }}
                                @endif
                            </div>
                        @else
                            <div class="letter-access-tag">
                                <i data-lucide="unlock" style="width: 12px; height: 12px;"></i>
                                {{ __('letters.open_now') }}
                            </div>
                        @endif
                        <div style="font-size: 0.7rem; color: #a0aec0; margin-top: 10px;">{{ date('d M Y', strtotime($l->created_at)) }}</div>
                    </div>
                </a>
            @empty
                <p style="grid-column: 1/-1; text-align: center; color: #a0aec0; padding: 40px;">{{ __('letters.no_letters_in_inbox') }}</p>
            @endforelse
        </div>
    </div>

    <div id="tab-sent" class="tab-content" style="display: none;">
        <div class="letter-grid">
            @forelse($sent as $l)
                <a href="/letters/{{ $l->id }}" style="text-decoration: none; color: inherit;">
                    <div class="envelope">
                        <i data-lucide="send"></i>
                        <div class="letter-subject">{{ $l->subject }}</div>
                        <div class="letter-from">{{ __('letters.to', ['name' => $l->receiver_name]) }}</div>
                        @if(($l->unlock_type ?? 'immediate') !== 'immediate')
                            <div class="letter-access-tag locked">
                                <i data-lucide="lock" style="width: 12px; height: 12px;"></i>
                                @if(($l->unlock_type ?? '') === 'age')
                                    {{ __('letters.unlock_at_age', ['age' => (int) ($l->unlock_value ?? 0)]) }}
                                @else
                                    {{ __('letters.unlock_after_years', ['years' => (int) ($l->unlock_value ?? 0)]) }}
                                @endif
                            </div>
                        @else
                            <div class="letter-access-tag">
                                <i data-lucide="unlock" style="width: 12px; height: 12px;"></i>
                                {{ __('letters.open_now') }}
                            </div>
                        @endif
                        <div style="font-size: 0.7rem; color: #a0aec0; margin-top: 10px;">{{ date('d M Y', strtotime($l->created_at)) }}</div>
                        @if($l->read_at)
                            <div style="font-size: 0.65rem; color: #38a169; margin-top: 5px;">{{ __('letters.read_on', ['date' => date('d M Y', strtotime($l->read_at))]) }}</div>
                        @else
                            <div style="font-size: 0.65rem; color: #a0aec0; margin-top: 5px;">{{ __('letters.unread') }}</div>
                        @endif
                    </div>
                </a>
            @empty
                <p style="grid-column: 1/-1; text-align: center; color: #a0aec0; padding: 40px;">{{ __('letters.you_havent_sent') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showTab(type) {
    document.getElementById('tab-inbox').style.display = type === 'inbox' ? 'block' : 'none';
    document.getElementById('tab-sent').style.display = type === 'sent' ? 'block' : 'none';
    document.getElementById('tab-btn-inbox').classList.toggle('active', type === 'inbox');
    document.getElementById('tab-btn-sent').classList.toggle('active', type === 'sent');
}
</script>
@endsection
