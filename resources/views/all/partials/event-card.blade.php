<div class="event-card" style="{{ $isPast ? 'opacity: 0.7;' : '' }}">
    <div class="event-header">
        <div class="event-date-box" style="{{ $isPast ? 'background: #edf2f7; color: #a0aec0;' : '' }}">
            <span class="event-day">{{ date('d', strtotime($event->event_date)) }}</span>
            <span class="event-month">{{ date('M', strtotime($event->event_date)) }}</span>
        </div>
        <div style="flex: 1;">
            <h2 class="event-title">{{ $event->title }} @if($isPast) <span style="font-size: 0.8rem; background: #e2e8f0; padding: 4px 8px; border-radius: 6px; color: #718096; margin-left: 10px;">{{ __('events.past') }}</span> @endif</h2>
            <div class="event-meta">
                <span>📍 {{ $event->location ?: __('events.no_location') }}</span>
                <span>⏰ {{ date('H:i', strtotime($event->event_date)) }}</span>
            </div>
        </div>
    </div>

    <div class="event-description">
        {{ $event->description ?: __('events.no_description') }}
    </div>

    @if(!$isPast)
        @if(session('authenticated_user.roleid') <= 2)
            <div style="background: #f7fafc; color: #718096; padding: 15px; border-radius: 12px; text-align: center; font-weight: 600; margin-bottom: 20px; border: 1px solid #e2e8f0; font-size: 0.9rem;">
                🔒 {{ __('events.employers_cannot_join') }}
            </div>
        @else
            <div class="btn-rsvp-group">
                <button class="btn-rsvp btn-going {{ $event->my_response === 'going' ? 'active' : '' }}" onclick="submitRSVP({{ $event->id }}, 'going', this)">{{ __('events.going') }}</button>
                <button class="btn-rsvp btn-maybe {{ $event->my_response === 'maybe' ? 'active' : '' }}" onclick="submitRSVP({{ $event->id }}, 'maybe', this)">{{ __('events.maybe') }}</button>
                <button class="btn-rsvp btn-not {{ $event->my_response === 'not_going' ? 'active' : '' }}" onclick="submitRSVP({{ $event->id }}, 'not_going', this)">{{ __('events.not_going') }}</button>
            </div>
        @endif
    @endif

    <div class="rsvp-section">
        <div style="font-weight: 800; color: #2d3748; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('events.attendance_summary') }}</div>
        <div class="rsvp-lists">
            <div class="rsvp-item">
                <strong>{{ __('events.going') }} ({{ count($event->going_names) }}):</strong>
                <span class="rsvp-names">{{ implode(', ', $event->going_names) ?: __('events.none') }}</span>
            </div>
            <div class="rsvp-item">
                <strong>{{ __('events.maybe') }} ({{ count($event->maybe_names) }}):</strong>
                <span class="rsvp-names">{{ implode(', ', $event->maybe_names) ?: __('events.none') }}</span>
            </div>
            <div class="rsvp-item">
                <strong>{{ __('events.not_going') }}:</strong>
                <span class="rsvp-names">{{ implode(', ', $event->not_going_names) ?: __('events.none') }}</span>
            </div>
        </div>
    </div>
</div>
