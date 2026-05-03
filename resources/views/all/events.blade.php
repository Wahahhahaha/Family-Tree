@extends('layouts.app')

@section('title', __('events.title'))

<?php
    $pageClass = 'page-family-tree page-events';
?>

@section('styles')
<style>
    .events-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
    .event-card { background: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #edf2f7; }
    .event-header { display: flex; gap: 24px; margin-bottom: 20px; }
    .event-date-box { background: #ebf8ff; color: #3182ce; padding: 15px; border-radius: 16px; text-align: center; min-width: 80px; height: fit-content; }
    .event-day { font-size: 1.5rem; font-weight: 800; display: block; }
    .event-month { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
    .event-title { font-size: 1.6rem; font-weight: 800; color: #1a365d; margin: 0 0 10px 0; }
    .event-description { color: #4a5568; line-height: 1.6; margin-bottom: 20px; font-size: 1.05rem; }
    .event-meta { display: flex; gap: 20px; color: #718096; font-size: 0.9rem; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f7fafc; }
    .rsvp-section { background: #f8fafc; padding: 20px; border-radius: 16px; margin-top: 20px; }
    .rsvp-lists { display: grid; grid-template-columns: 1fr; gap: 12px; margin-top: 15px; font-size: 0.9rem; }
    .rsvp-item { display: flex; gap: 10px; line-height: 1.4; }
    .rsvp-item strong { min-width: 80px; color: #4a5568; }
    .rsvp-names { color: #718096; }
    .admin-form { background: white; padding: 30px; border-radius: 20px; margin-bottom: 40px; border: 2px dashed #e2e8f0; display: none; }
    .btn-rsvp-group { display: flex; gap: 10px; margin-bottom: 20px; }
    .btn-rsvp { flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; text-align: center; }
    .btn-going { background: #f0fff4; color: #38a169; }
    .btn-going.active { border-color: #38a169; background: #38a169; color: white; }
    .btn-maybe { background: #fffaf0; color: #dd6b20; }
    .btn-maybe.active { border-color: #dd6b20; background: #dd6b20; color: white; }
    .btn-not { background: #fff5f5; color: #e53e3e; }
    .btn-not.active { border-color: #e53e3e; background: #e53e3e; color: white; }

    .tabs-group { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #f7fafc; padding-bottom: 10px; }
    .tab-btn { padding: 10px 25px; border-radius: 10px; font-weight: 700; cursor: pointer; border: none; background: transparent; color: #718096; transition: all 0.3s; }
    .tab-btn.active { background: #ebf8ff; color: #3182ce; }
</style>
@endsection

@section('content')
<div class="events-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="font-family: 'Sora', sans-serif; font-weight: 800; color: #1a365d; margin: 0;">{{ __('events.hero_title') }}</h1>
        <div style="display: flex; gap: 10px;">
            @if(session('authenticated_user.roleid') <= 2)
                <button onclick="toggleForm()" class="btn btn-primary">{{ __('events.create_event') }}</button>
            @endif
            <a href="/calendar" style="text-decoration: none;">
                <button class="btn btn-soft" type="button">{{ __('events.view_calendar') }}</button>
            </a>
        </div>
    </div>

    @if(session('authenticated_user.roleid') <= 2)
    <div id="adminForm" class="admin-form">
        <h3 style="margin-bottom: 20px; font-weight: 800;">{{ __('events.new_event_details') }}</h3>
        <form action="/events/store" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>{{ __('events.event_title') }}</label>
                    <input type="text" name="title" required class="form-control" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div class="form-group">
                    <label>{{ __('events.date_time') }}</label>
                    <input type="datetime-local" name="event_date" required min="{{ date('Y-m-d\TH:i') }}" class="form-control" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>{{ __('events.location') }}</label>
                <input type="text" name="location" class="form-control" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd;">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label>{{ __('events.description') }}</label>
                <textarea name="description" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; min-height: 100px;"></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">{{ __('events.publish_event') }}</button>
                <button type="button" onclick="toggleForm()" class="btn btn-ghost">{{ __('events.cancel') }}</button>
            </div>
        </form>
    </div>
    @endif

    <div class="tabs-group">
        <button onclick="showTab('upcoming')" id="tab-btn-upcoming" class="tab-btn active">{{ __('events.upcoming_events') }} ({{ $upcomingEvents->count() }})</button>
        <button onclick="showTab('past')" id="tab-btn-past" class="tab-btn">{{ __('events.past_events') }} ({{ $pastEvents->count() }})</button>
    </div>

    <div id="tab-upcoming" class="tab-content">
        @forelse($upcomingEvents as $event)
            @include('all.partials.event-card', ['event' => $event, 'isPast' => false])
        @empty
            <p style="text-align: center; color: #a0aec0; padding: 40px;">{{ __('events.no_upcoming_events') }}</p>
        @endforelse
    </div>

    <div id="tab-past" class="tab-content" style="display: none;">
        @forelse($pastEvents as $event)
            @include('all.partials.event-card', ['event' => $event, 'isPast' => true])
        @empty
            <p style="text-align: center; color: #a0aec0; padding: 40px;">{{ __('events.no_past_events') }}</p>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleForm() {
    const form = document.getElementById('adminForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function showTab(type) {
    document.getElementById('tab-upcoming').style.display = type === 'upcoming' ? 'block' : 'none';
    document.getElementById('tab-past').style.display = type === 'past' ? 'block' : 'none';
    document.getElementById('tab-btn-upcoming').classList.toggle('active', type === 'upcoming');
    document.getElementById('tab-btn-past').classList.toggle('active', type === 'past');
}

function submitRSVP(eventId, status, btn) {
    fetch('/events/rsvp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ event_id: eventId, status: status })
    }).then(res => res.json()).then(data => { if(data.success) location.reload(); });
}
</script>
@endsection
