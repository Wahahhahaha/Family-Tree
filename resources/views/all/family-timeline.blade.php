@extends('layouts.app')

@section('title', 'Family Timeline')

@section('styles')
<style>
    body.page-family-timeline {
        background:
            radial-gradient(circle at top left, rgba(31, 154, 214, 0.12), transparent 28%),
            radial-gradient(circle at top right, rgba(24, 179, 130, 0.1), transparent 26%),
            #f5f8fb;
    }

    body.page-family-timeline .timeline-shell {
        width: min(1480px, calc(100% - 30px));
        margin: 0 auto;
        padding: 22px 0 52px;
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr);
        gap: 22px;
    }

    body.page-family-timeline .timeline-sidebar,
    body.page-family-timeline .timeline-main,
    body.page-family-timeline .timeline-card,
    body.page-family-timeline .timeline-hero,
    body.page-family-timeline .timeline-filters,
    body.page-family-timeline .timeline-form-card,
    body.page-family-timeline .timeline-empty {
        background: #ffffff;
        border: 1px solid #e3edf5;
        border-radius: 24px;
        box-shadow: 0 18px 44px rgba(16, 53, 77, 0.1);
    }

    body.page-family-timeline .timeline-sidebar {
        padding: 20px;
        display: grid;
        gap: 16px;
        align-self: start;
        position: sticky;
        top: 16px;
    }

    body.page-family-timeline .timeline-hero {
        padding: 20px;
        background:
            linear-gradient(160deg, rgba(31, 154, 214, 0.12), rgba(24, 179, 130, 0.08)),
            #ffffff;
    }

    body.page-family-timeline .timeline-hero .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(35, 153, 210, 0.1);
        color: #146f9c;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    body.page-family-timeline .timeline-hero h1,
    body.page-family-timeline .timeline-main h2 {
        margin: 12px 0 0;
        font-family: "Sora", sans-serif;
        color: #102d41;
        line-height: 1.1;
    }

    body.page-family-timeline .timeline-hero p {
        margin: 10px 0 0;
        color: #4e6a7e;
        line-height: 1.7;
    }

    body.page-family-timeline .timeline-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    body.page-family-timeline .timeline-stat {
        padding: 14px;
        border-radius: 18px;
        background: #f8fbff;
        border: 1px solid #dce9f3;
    }

    body.page-family-timeline .timeline-stat strong {
        display: block;
        font-size: 22px;
        color: #10344a;
        font-family: "Sora", sans-serif;
    }

    body.page-family-timeline .timeline-stat span {
        display: block;
        margin-top: 4px;
        color: #627b8d;
        font-size: 12px;
        font-weight: 700;
    }

    body.page-family-timeline .timeline-filters,
    body.page-family-timeline .timeline-form-card {
        padding: 18px;
    }

    body.page-family-timeline .timeline-filters h3,
    body.page-family-timeline .timeline-form-card h3 {
        margin: 0 0 12px;
        font-size: 18px;
        font-family: "Sora", sans-serif;
        color: #10344a;
    }

    body.page-family-timeline .timeline-filter-grid,
    body.page-family-timeline .timeline-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    body.page-family-timeline .timeline-form-grid .full-width,
    body.page-family-timeline .timeline-filter-grid .full-width {
        grid-column: 1 / -1;
    }

    body.page-family-timeline .timeline-field label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 800;
        color: #35556d;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    body.page-family-timeline .timeline-field input,
    body.page-family-timeline .timeline-field select,
    body.page-family-timeline .timeline-field textarea {
        width: 100%;
        border: 1px solid #d6e2ec;
        border-radius: 14px;
        background: #f8fbff;
        padding: 11px 12px;
        font-family: inherit;
        font-size: 14px;
        color: #102133;
        outline: none;
    }

    body.page-family-timeline .timeline-field textarea {
        min-height: 104px;
        resize: vertical;
    }

    body.page-family-timeline .timeline-actions {
        margin-top: 12px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    body.page-family-timeline .timeline-actions .btn {
        border-radius: 14px;
    }

    body.page-family-timeline .timeline-main {
        padding: 22px;
        min-width: 0;
    }

    body.page-family-timeline .timeline-main-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    body.page-family-timeline .timeline-main-head p {
        margin: 8px 0 0;
        color: #5a7487;
        line-height: 1.7;
    }

    body.page-family-timeline .timeline-list {
        position: relative;
        display: grid;
        gap: 14px;
        margin-top: 10px;
        padding-left: 24px;
    }

    body.page-family-timeline .timeline-list::before {
        content: "";
        position: absolute;
        left: 10px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: linear-gradient(180deg, rgba(31, 154, 214, 0.35), rgba(24, 179, 130, 0.35));
    }

    body.page-family-timeline .timeline-item {
        position: relative;
        padding-left: 22px;
    }

    body.page-family-timeline .timeline-item::before {
        content: "";
        position: absolute;
        left: -2px;
        top: 22px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1f9ad6, #18b382);
        box-shadow: 0 0 0 5px rgba(31, 154, 214, 0.12);
    }

    body.page-family-timeline .timeline-card {
        padding: 18px;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.98));
    }

    body.page-family-timeline .timeline-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    body.page-family-timeline .timeline-card-title {
        margin: 0;
        font-size: 18px;
        font-family: "Sora", sans-serif;
        color: #11334a;
    }

    body.page-family-timeline .timeline-meta {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        color: #5a7487;
        font-size: 12px;
        font-weight: 700;
    }

    body.page-family-timeline .timeline-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #edf7fd;
        color: #146f9c;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    body.page-family-timeline .timeline-desc {
        margin: 12px 0 0;
        color: #35556d;
        line-height: 1.7;
        white-space: pre-wrap;
    }

    body.page-family-timeline .timeline-footer {
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    body.page-family-timeline .timeline-footer .timeline-attachment {
        color: #0f6b9a;
        font-weight: 800;
        text-decoration: none;
    }

    body.page-family-timeline .timeline-footer .timeline-attachment:hover {
        text-decoration: underline;
    }

    body.page-family-timeline .timeline-empty {
        padding: 30px;
        text-align: center;
        color: #60798c;
    }

    body.page-family-timeline .timeline-pagination {
        margin-top: 18px;
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 1080px) {
        body.page-family-timeline .timeline-shell {
            grid-template-columns: 1fr;
        }

        body.page-family-timeline .timeline-sidebar {
            position: static;
        }
    }

    @media (max-width: 720px) {
        body.page-family-timeline .timeline-shell {
            width: min(100%, calc(100% - 16px));
            padding-top: 12px;
        }

        body.page-family-timeline .timeline-stats,
        body.page-family-timeline .timeline-filter-grid,
        body.page-family-timeline .timeline-form-grid {
            grid-template-columns: 1fr;
        }

        body.page-family-timeline .timeline-list {
            padding-left: 18px;
        }

        body.page-family-timeline .timeline-item::before {
            left: -1px;
        }
    }
</style>
@endsection

@section('content')
<?php
    $currentMemberName = trim((string) ($selectedMemberForForm->display_name ?? $selectedMemberForForm->name ?? ''));
    $currentMemberDisplay = $currentMemberName !== ''
        ? $currentMemberName
        : 'Selected member';
    $selectedTimelineMemberId = (int) old('family_member_id', (int) ($selectedMemberForForm->memberid ?? 0));
?>
<div class="timeline-shell">
    <aside class="timeline-sidebar">
        <section class="timeline-hero">
            <span class="eyebrow">Family Timeline</span>
            <h1>Life events, in chronological order</h1>
            <p>Every family member can record important moments from school, work, marriage, health, moves, achievements, and other milestones. The timeline stays visible only inside this family.</p>
            <div class="timeline-stats">
                <div class="timeline-stat">
                    <strong>{{ (int) $timelineEntries->total() }}</strong>
                    <span>Total entries</span>
                </div>
                <div class="timeline-stat">
                    <strong>{{ count($familyMembers) }}</strong>
                    <span>Family members</span>
                </div>
                <div class="timeline-stat">
                    <strong>{{ $currentLevelId === 2 ? 'Own' : 'All' }}</strong>
                    <span>Access scope</span>
                </div>
            </div>
        </section>

        <section class="timeline-filters">
            <h3>Filters</h3>
            <form method="GET" action="/timeline" class="timeline-filter-grid">
                <div class="timeline-field full-width">
                    <label for="timelineFilterMember">Family Member</label>
                    <select id="timelineFilterMember" name="member_id">
                        <option value="0">All members</option>
                        @foreach ($familyMembers as $member)
                            <option value="{{ (int) $member->memberid }}" @selected((int) ($timelineFilters['member_id'] ?? 0) === (int) $member->memberid)>
                                {{ $member->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="timeline-field">
                    <label for="timelineFilterCategory">Category</label>
                    <select id="timelineFilterCategory" name="category">
                        <option value="">All categories</option>
                        @foreach ($timelineCategories as $categoryKey => $categoryLabel)
                            <option value="{{ $categoryKey }}" @selected((string) ($timelineFilters['category'] ?? '') === $categoryKey)>{{ $categoryLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="timeline-field">
                    <label for="timelineFilterYear">Year</label>
                    <input id="timelineFilterYear" type="number" name="year" min="1900" max="{{ date('Y') }}" value="{{ (int) ($timelineFilters['year'] ?? 0) ?: '' }}" placeholder="Optional">
                </div>
                <div class="timeline-actions full-width">
                    <button class="btn btn-primary" type="submit">Apply Filters</button>
                    <a class="btn btn-soft" href="/timeline" style="display:inline-flex;align-items:center;justify-content:center;">Reset</a>
                </div>
            </form>
        </section>

        <section class="timeline-form-card">
            <h3>{{ (int) $editTimelineId > 0 ? 'Edit Timeline Entry' : 'Add Timeline Entry' }}</h3>
            @if ($errors->any())
                <div style="margin-bottom:12px;padding:12px 14px;border-radius:14px;background:#fff5f5;border:1px solid #f2c8cb;color:#9b2c2c;">
                    {{ $errors->first() }}
                </div>
            @endif
            <form id="timelineForm" method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="timeline-form-grid">
                @csrf
                <input type="hidden" id="timelineIdInput" name="timeline_id" value="{{ (int) $editTimelineId > 0 ? (int) $editTimelineId : '' }}">
                @if ($currentLevelId === 2 && $currentMemberId > 0 && !$isAdminOrSuperadmin)
                    <input type="hidden" name="family_member_id" value="{{ $currentMemberId }}">
                @else
                    <div class="timeline-field full-width">
                        <label for="timelineMemberSelect">Family Member</label>
                        <select id="timelineMemberSelect" name="family_member_id" required>
                            <option value="">Select member</option>
                            @foreach ($familyMembers as $member)
                                <option value="{{ (int) $member->memberid }}" @selected($selectedTimelineMemberId === (int) $member->memberid)>
                                    {{ $member->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="timeline-field full-width">
                    <label for="timelineTitle">Title</label>
                    <input id="timelineTitle" name="title" type="text" maxlength="255" value="{{ old('title', $formValues['title'] ?? '') }}" placeholder="Enter timeline title" required>
                </div>
                <div class="timeline-field full-width">
                    <label for="timelineDescription">Description</label>
                    <textarea id="timelineDescription" name="description" maxlength="3000" placeholder="Write a short story or note...">{{ old('description', $formValues['description'] ?? '') }}</textarea>
                </div>
                <div class="timeline-field">
                    <label for="timelineEventDate">Event Date</label>
                    <input id="timelineEventDate" name="event_date" type="date" value="{{ old('event_date', $formValues['event_date'] ?? '') }}">
                </div>
                <div class="timeline-field">
                    <label for="timelineEventYear">Event Year</label>
                    <input id="timelineEventYear" name="event_year" type="number" min="1900" max="{{ date('Y') }}" value="{{ old('event_year', $formValues['event_year'] ?? '') }}" placeholder="Optional">
                </div>
                <div class="timeline-field">
                    <label for="timelineCategory">Category</label>
                    <select id="timelineCategory" name="category" required>
                        <option value="">Select category</option>
                        @foreach ($timelineCategories as $categoryKey => $categoryLabel)
                            <option value="{{ $categoryKey }}" @selected((string) old('category', $formValues['category'] ?? '') === $categoryKey)>{{ $categoryLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="timeline-field">
                    <label for="timelineLocation">Location</label>
                    <input id="timelineLocation" name="location" type="text" maxlength="255" value="{{ old('location', $formValues['location'] ?? '') }}" placeholder="Optional location">
                </div>
                <div class="timeline-field full-width">
                    <label for="timelineAttachment">Attachment Photo</label>
                    <input id="timelineAttachment" name="attachment" type="file" accept="image/jpeg,image/png,image/webp">
                    <small style="display:block;margin-top:6px;color:#6b8193;font-size:12px;">Optional. JPG, JPEG, PNG, or WebP only.</small>
                </div>
                <div class="timeline-actions full-width">
                    <button id="timelineSubmitBtn" class="btn btn-primary" type="submit">{{ (int) $editTimelineId > 0 ? 'Update Timeline' : 'Save Timeline' }}</button>
                    <button id="timelineCancelBtn" class="btn btn-soft" type="button" style="{{ (int) $editTimelineId > 0 ? '' : 'display:none;' }}">Cancel Edit</button>
                </div>
            </form>
        </section>
    </aside>

    <main class="timeline-main">
        <div class="timeline-main-head">
            <div>
                <span class="eyebrow">Chronological Feed</span>
                <h2>Family timeline entries</h2>
                <p>{{ (int) ($timelineFilters['member_id'] ?? 0) > 0 ? 'Showing events for the selected family member.' : 'Browse life events from across this family.' }}</p>
            </div>
        </div>

        @if ($timelineEntries->count() > 0)
            <section class="timeline-list">
                @foreach ($timelineEntries as $entry)
                    @php
                        $entryData = [
                            'id' => (int) $entry->id,
                            'family_member_id' => (int) ($entry->family_member_id ?? 0),
                            'title' => (string) ($entry->title ?? ''),
                            'description' => (string) ($entry->description ?? ''),
                            'event_date' => (string) ($entry->event_date ?? ''),
                            'event_year' => (string) ($entry->event_year ?? ''),
                            'category' => (string) ($entry->category ?? ''),
                            'location' => (string) ($entry->location ?? ''),
                            'attachment_path' => (string) ($entry->attachment_path ?? ''),
                            'attachment_url' => (string) ($entry->attachment_url ?? ''),
                            'member_name' => (string) ($entry->family_member_name ?? $entry->member_username ?? 'Member'),
                            'display_date' => (string) ($entry->display_date ?? ''),
                        ];
                    @endphp
                    <article class="timeline-item">
                        <div class="timeline-card">
                            <div class="timeline-card-head">
                                <div>
                                    <h3 class="timeline-card-title">{{ $entry->title }}</h3>
                                    <div class="timeline-meta">
                                        <span class="timeline-badge">{{ $entry->category_label }}</span>
                                        <span>{{ $entry->display_date }}</span>
                                        <span>{{ $entry->family_member_name ?: $entry->member_username }}</span>
                                    </div>
                                </div>
                                @if (!empty($entry->can_manage))
                                    <div class="timeline-actions" style="margin-top:0;">
                                        <button type="button" class="btn btn-soft timeline-edit-btn" data-timeline='@json($entryData)'>Edit</button>
                                        <form method="POST" action="/timeline/{{ (int) $entry->id }}/delete" onsubmit="return confirm('Delete this timeline entry?');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger-soft">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            @if (trim((string) ($entry->description ?? '')) !== '')
                                <p class="timeline-desc">{{ $entry->description }}</p>
                            @endif
                            <div class="timeline-footer">
                                <div class="timeline-meta">
                                    @if (!empty($entry->location))
                                        <span>Location: {{ $entry->location }}</span>
                                    @endif
                                </div>
                                @if (!empty($entry->attachment_url))
                                    <a class="timeline-attachment" href="{{ $entry->attachment_url }}" target="_blank" rel="noopener noreferrer">View attachment</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="timeline-pagination">
                {{ $timelineEntries->links() }}
            </div>
        @else
            <div class="timeline-empty">
                No timeline entries yet. Use the form on the left to add the first family milestone.
            </div>
        @endif
    </main>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var form = document.getElementById('timelineForm');
        var submitBtn = document.getElementById('timelineSubmitBtn');
        var cancelBtn = document.getElementById('timelineCancelBtn');
        var timelineIdInput = document.getElementById('timelineIdInput');
        var titleInput = document.getElementById('timelineTitle');
        var descriptionInput = document.getElementById('timelineDescription');
        var eventDateInput = document.getElementById('timelineEventDate');
        var eventYearInput = document.getElementById('timelineEventYear');
        var categoryInput = document.getElementById('timelineCategory');
        var locationInput = document.getElementById('timelineLocation');
        var memberSelect = document.getElementById('timelineMemberSelect');
        var defaultAction = '/timeline/store';

        function resetTimelineForm() {
            if (!form) {
                return;
            }

            form.action = defaultAction;
            if (timelineIdInput) timelineIdInput.value = '';
            if (titleInput) titleInput.value = '';
            if (descriptionInput) descriptionInput.value = '';
            if (eventDateInput) eventDateInput.value = '';
            if (eventYearInput) eventYearInput.value = '';
            if (categoryInput) categoryInput.value = '';
            if (locationInput) locationInput.value = '';
            if (submitBtn) submitBtn.textContent = 'Save Timeline';
            if (cancelBtn) cancelBtn.style.display = 'none';
            if (memberSelect && memberSelect.options.length > 0 && !memberSelect.value) {
                memberSelect.value = memberSelect.options[0].value || '';
            }
        }

        function fillFormFromEntry(entry) {
            if (!form || !entry) {
                return;
            }

            form.action = '/timeline/' + encodeURIComponent(entry.id) + '/update';
            if (timelineIdInput) timelineIdInput.value = entry.id || '';
            if (titleInput) titleInput.value = entry.title || '';
            if (descriptionInput) descriptionInput.value = entry.description || '';
            if (eventDateInput) eventDateInput.value = entry.event_date || '';
            if (eventYearInput) eventYearInput.value = entry.event_year || '';
            if (categoryInput) categoryInput.value = entry.category || '';
            if (locationInput) locationInput.value = entry.location || '';
            if (memberSelect && entry.family_member_id) {
                memberSelect.value = String(entry.family_member_id);
            }
            if (submitBtn) submitBtn.textContent = 'Update Timeline';
            if (cancelBtn) cancelBtn.style.display = 'inline-flex';
            if (titleInput) {
                titleInput.focus();
                titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        Array.prototype.slice.call(document.querySelectorAll('.timeline-edit-btn')).forEach(function (button) {
            button.addEventListener('click', function () {
                var raw = button.getAttribute('data-timeline') || '{}';
                try {
                    fillFormFromEntry(JSON.parse(raw));
                } catch (error) {
                    return;
                }
            });
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                resetTimelineForm();
            });
        }

        @if ((int) $editTimelineId > 0)
            if (cancelBtn) {
                cancelBtn.style.display = 'inline-flex';
            }
        @endif
    })();
</script>
@endsection
