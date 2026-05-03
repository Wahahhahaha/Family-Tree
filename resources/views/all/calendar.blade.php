@extends('layouts.app')

@section('title', __('calendar.title'))

<?php
    $pageClass = 'page-family-tree page-calendar';
?>

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    .calendar-container {
        background: white;
        border-radius: 24px;
        box-shadow: 0 16px 44px rgba(16, 53, 77, 0.11);
    }

    .fc-header-toolbar {
        margin-bottom: 20px !important;
    }

    .fc-button-primary {
        background-color: #3182ce !important;
        border-color: #3182ce !important;
    }

    .fc-event-birthday {
        background-color: #f6ad55 !important;
        border-color: #f6ad55 !important;
        color: white !important;
        padding: 2px 5px;
        border-radius: 4px;
    }

    .fc-event-anniversary {
        background-color: #a0aec0 !important;
        border-color: #a0aec0 !important;
        color: white !important;
        padding: 2px 5px;
        border-radius: 4px;
    }

    .fc-event-family-event {
        background-color: #3182ce !important;
        border-color: #3182ce !important;
        color: white !important;
        padding: 2px 5px;
        border-radius: 4px;
    }

    .calendar-title {
        font-family: 'Sora', sans-serif;
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a365d;
    }

    .wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
</style>
@endsection

@section('content')
<div class="wrapper">
    <div class="calendar-container" style="max-width: 900px; margin: 40px auto; padding: 40px;">
        <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 0;">
            <h1 class="calendar-title" style="margin: 0; font-size: 1.5rem;">{{ __('calendar.title') }}</h1>
            <a href="/" style="text-decoration: none;">
                <button class="btn btn-soft" type="button" style="padding: 8px 20px;">{{ __('calendar.back_to_tree') }}</button>
            </a>
        </div>

        <div id='calendar'></div>
    </div>
</div>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var rawEvents = <?php echo json_encode($events); ?>;

        var events = [];
        var currentYear = new Date().getFullYear();

        rawEvents.forEach(function(ev) {
            if (ev.type === 'event') {
                events.push({
                    title: '📅 ' + ev.title,
                    start: ev.date,
                    className: 'fc-event-family-event',
                    allDay: false
                });
            } else {
                var date = new Date(ev.date);
                for(var y = currentYear - 1; y <= currentYear + 1; y++) {
                    var newDate = new Date(y, date.getMonth(), date.getDate());
                    events.push({
                        title: (ev.type === 'birthday' ? '🎂 ' : '🕯️ ') + ev.title,
                        start: newDate.toISOString().split('T')[0],
                        className: ev.type === 'birthday' ? 'fc-event-birthday' : 'fc-event-anniversary',
                        allDay: true
                    });
                }
            }
        });

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek,listMonth'
            },
            events: events,
            eventClick: function(info) {
            }
        });
        calendar.render();
    });
</script>
@endsection
