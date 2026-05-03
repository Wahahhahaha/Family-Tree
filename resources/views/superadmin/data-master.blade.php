@extends('layouts.app')

@section('title', 'Data Master')
@section('body-class', 'page-family-tree')

@section('content')
<div class="wrapper">
    <section class="management-card">
        <div class="management-head">
            <div>
                <h2>Data Master</h2>
                <p>Kelola data pendukung sistem.</p>
            </div>
            <div class="management-tools">
                <div class="management-controls">
                    <div class="management-filters">
                        <a href="#" class="tab-item {{ $activeTab == 'social' ? 'active' : '' }}" onclick="switchTab('social')">Social Media</a>
                        <a href="#" class="tab-item {{ $activeTab == 'levels' ? 'active' : '' }}" onclick="switchTab('levels')">Levels</a>
                        <a href="#" class="tab-item {{ $activeTab == 'roles' ? 'active' : '' }}" onclick="switchTab('roles')">Roles</a>
                        <a href="#" class="tab-item {{ $activeTab == 'fields' ? 'active' : '' }}" onclick="switchTab('fields')">Custom Fields</a>
                    </div>
                    
                    <div class="management-search-wrap" style="margin-left: 10px;">
                        <form action="" method="GET" id="searchForm">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <input
                                id="masterSearchInput"
                                name="keyword"
                                class="search management-search"
                                type="search"
                                placeholder="Search here..."
                                value="{{ $keyword ?? '' }}"
                                autocomplete="off"
                            >
                        </form>
                    </div>
                </div>
                <div class="management-actions">
                    <button type="button" class="btn btn-primary" onclick="openAddModal()">+ Add New</button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    @if($activeTab == 'social')
                        <tr><th>ID</th><th>Social Media Name</th><th>Icon / Slug</th><th>Action</th></tr>
                    @elseif($activeTab == 'levels')
                        <tr><th>ID</th><th>Level Name</th><th>Action</th></tr>
                    @elseif($activeTab == 'roles')
                        <tr><th>ID</th><th>Role Name</th><th>Action</th></tr>
                    @elseif($activeTab == 'fields')
                        <tr><th>ID</th><th>Field Name</th><th>Type</th><th>Required</th><th>Action</th></tr>
                    @endif
                </thead>
                <tbody id="masterTableBody">
                    @if($activeTab == 'social')
                        @include('superadmin.partials.social-media-table-rows', ['socials' => $socials])
                    @elseif($activeTab == 'levels')
                        @include('superadmin.partials.level-table-rows', ['levels' => $levels])
                    @elseif($activeTab == 'roles')
                        @include('superadmin.partials.role-table-rows', ['roles' => $roles])
                    @elseif($activeTab == 'fields')
                        @include('superadmin.partials.field-table-rows', ['fields' => $fields])
                    @endif
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap" style="margin-top: 20px;">
            @if($activeTab == 'social') {{ $socials->links() }}
            @elseif($activeTab == 'levels') {{ $levels->links() }}
            @elseif($activeTab == 'roles') {{ $roles->links() }}
            @elseif($activeTab == 'fields') {{ $fields->links() }}
            @endif
        </div>
    </section>
</div>

<!-- Modal Social Media -->
<div id="socialModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h3 id="socialModalTitle">Add New Social Media</h3>
            <button type="button" class="modal-close" onclick="closeModal('socialModal')">&times;</button>
        </div>
        <form id="socialForm" method="POST" action="/management/social-media" class="modal-form">
            @csrf
            <div class="modal-field modal-field-full">
                <label for="socialname">Name</label>
                <input id="socialname" name="socialname" type="text" placeholder="e.g. Facebook" required>
            </div>
            <div class="modal-field modal-field-full">
                <label for="socialicon">Icon / Slug</label>
                <input id="socialicon" name="socialicon" type="text" placeholder="e.g. facebook">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeModal('socialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Levels/Roles -->
<div id="basicModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h3 id="basicModalTitle">Add New</h3>
            <button type="button" class="modal-close" onclick="closeModal('basicModal')">&times;</button>
        </div>
        <form id="basicForm" method="POST" action="" class="modal-form">
            @csrf
            <div class="modal-field modal-field-full">
                <label for="basicName" id="basicLabel">Name</label>
                <input id="basicName" name="name" type="text" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeModal('basicModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Custom Fields -->
<div id="fieldModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h3 id="fieldModalTitle">Add Custom Field</h3>
            <button type="button" class="modal-close" onclick="closeModal('fieldModal')">&times;</button>
        </div>
        <form id="fieldForm" method="POST" action="/management/fields" class="modal-form">
            @csrf
            <div class="modal-field">
                <label for="field_name">Field Label</label>
                <input id="field_name" name="field_name" type="text" placeholder="e.g. Hobby" required>
            </div>
            <div class="modal-field">
                <label for="field_type">Type</label>
                <select id="field_type" name="field_type" onchange="toggleOptionsField()">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Select (Dropdown)</option>
                </select>
            </div>
            <div class="modal-field modal-field-full hidden" id="optionsWrap">
                <label for="field_options">Options (comma separated)</label>
                <input id="field_options" name="field_options" type="text" placeholder="Option 1, Option 2, Option 3">
            </div>
            <div class="modal-field">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="is_required" value="1" id="field_required">
                    Required Field
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeModal('fieldModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<style>
    .tab-item { padding: 8px 16px; border-radius: 8px; text-decoration: none; color: #64748b; font-weight: 600; transition: 0.2s; }
    .tab-item.active { background: #e2f2fc; color: #1f9ad6; }
    .action-buttons { display: flex; gap: 8px; }
    code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    .pagination-wrap nav { display: flex; justify-content: center; }
    .hidden { display: none !important; }
</style>

<script>
    const activeTab = '{{ $activeTab }}';

    function openAddModal() {
        if (activeTab === 'social') {
            document.getElementById('socialModalTitle').textContent = 'Add New Social Media';
            document.getElementById('socialname').value = '';
            document.getElementById('socialicon').value = '';
            document.getElementById('socialForm').action = '/management/social-media';
            showModal('socialModal');
        } else if (activeTab === 'levels') {
            setupBasicModal('Add New Level', 'Level Name', 'levelname', '/management/levels');
        } else if (activeTab === 'roles') {
            setupBasicModal('Add New Role', 'Role Name', 'rolename', '/management/roles');
        } else if (activeTab === 'fields') {
            document.getElementById('fieldModalTitle').textContent = 'Add Custom Field';
            document.getElementById('field_name').value = '';
            document.getElementById('field_type').value = 'text';
            document.getElementById('field_options').value = '';
            document.getElementById('field_required').checked = false;
            document.getElementById('fieldForm').action = '/management/fields';
            toggleOptionsField();
            showModal('fieldModal');
        }
    }

    function setupBasicModal(title, label, inputName, action) {
        document.getElementById('basicModalTitle').textContent = title;
        document.getElementById('basicLabel').textContent = label;
        document.getElementById('basicName').name = inputName;
        document.getElementById('basicName').value = '';
        document.getElementById('basicForm').action = action;
        showModal('basicModal');
    }

    function openEditLevelModal(id, name) {
        setupBasicModal('Edit Level', 'Level Name', 'levelname', '/management/levels/' + id + '/update');
        document.getElementById('basicName').value = name;
    }

    function openEditRoleModal(id, name) {
        setupBasicModal('Edit Role', 'Role Name', 'rolename', '/management/roles/' + id + '/update');
        document.getElementById('basicName').value = name;
    }

    function openEditModal(id, name, icon) {
        document.getElementById('socialModalTitle').textContent = 'Edit Social Media';
        document.getElementById('socialname').value = name;
        document.getElementById('socialicon').value = icon;
        document.getElementById('socialForm').action = '/management/social-media/' + id + '/update';
        showModal('socialModal');
    }

    function openEditFieldModal(id, name, type, options, required) {
        document.getElementById('fieldModalTitle').textContent = 'Edit Custom Field';
        document.getElementById('field_name').value = name;
        document.getElementById('field_type').value = type;
        document.getElementById('field_options').value = options || '';
        document.getElementById('field_required').checked = parseInt(required) === 1;
        document.getElementById('fieldForm').action = '/management/fields/' + id + '/update';
        toggleOptionsField();
        showModal('fieldModal');
    }

    function toggleOptionsField() {
        const type = document.getElementById('field_type').value;
        document.getElementById('optionsWrap').classList.toggle('hidden', type !== 'select');
    }

    function showModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('open');
        modal.style.display = 'flex';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('open');
        modal.style.display = 'none';
    }

    function switchTab(tab) {
        window.location.href = '?tab=' + tab;
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
