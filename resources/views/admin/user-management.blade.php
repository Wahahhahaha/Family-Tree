@extends('layouts.app')

@section('title', __('management_users.title'))
@section('body-class', 'page-family-tree')

@section('content')
<div class="wrapper">

    <section class="management-card">
        <div class="management-head">
            <div>
                <h2><?php echo e(__('management_users.heading')); ?></h2>
                <p><?php echo e(__('management_users.description')); ?></p>
            </div>
            <div class="management-tools management-tools-user">
                <div class="management-controls">
                    <span id="userTableCount" class="table-count"><?php echo e(__('management_users.total_users', ['count' => $users->total()])); ?></span>
                    <div class="management-filters">
                        <input
                            id="userSearchInput"
                            class="search management-search"
                            type="search"
                            placeholder="<?php echo e(__('management_users.search_placeholder')); ?>"
                            value="<?php echo e($userSearchKeyword ?? ''); ?>"
                            autocomplete="off"
                        >
                        <select id="userRoleFilter" class="search management-role-filter">
                            <option value=""><?php echo e(__('management_users.all_roles')); ?></option>
                            <option value="superadmin" <?php echo e(($selectedUserRoleFilter ?? '') === 'superadmin' ? 'selected' : ''); ?>><?php echo e(__('management_users.role_superadmin')); ?></option>
                            <option value="admin" <?php echo e(($selectedUserRoleFilter ?? '') === 'admin' ? 'selected' : ''); ?>><?php echo e(__('management_users.role_admin')); ?></option>
                            <option value="familymember" <?php echo e(($selectedUserRoleFilter ?? '') === 'familymember' ? 'selected' : ''); ?>><?php echo e(__('management_users.role_family_member')); ?></option>
                        </select>
                    </div>
                </div>

<!--                 <div class="management-actions">
                    <button id="openImportUserModal" type="button" class="btn btn-ghost"><?php echo e(__('management_users.import')); ?></button>
                    <a href="/management/users/export" class="btn btn-export"><?php echo e(__('management_users.export_xlsx')); ?></a>
                </div>
 -->            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="alert-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php if ($errors->any()): ?>
            <div class="alert-error">
                <?php foreach ($errors->all() as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($errors->getBag('userImport')->any()): ?>
            <div class="alert-error">
                <?php foreach ($errors->getBag('userImport')->all() as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="table-wrap">
            <table id="userDataTable" class="data-table">
                <thead>
                    <tr>
                        <th class="bulk-select-col" aria-label="<?php echo e(__('management_users.select_users')); ?>"></th>
                        <th><?php echo e(__('management_users.username')); ?></th>
                        <th><?php echo e(__('management_users.name')); ?></th>
                        <th><?php echo e(__('management_users.level')); ?></th>
                        <th><?php echo e(__('management_users.role')); ?></th>
                        <th><?php echo e(__('management_users.email')); ?></th>
                        <th><?php echo e(__('management_users.phone')); ?></th>
                        <th><?php echo e(__('management_users.action')); ?></th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    @include('admin.partials.user-table-rows', ['users' => $users])
                </tbody>
            </table>
        </div>

        <div id="userPagination">
            @include('admin.partials.user-pagination', ['users' => $users])
        </div>
    </section>

    <div id="bulkUserActions" class="bulk-user-actions-card hidden" aria-live="polite">
        <label class="bulk-select-all-control" for="bulkSelectAllUsers">
            <input id="bulkSelectAllUsers" type="checkbox">
            <span><?php echo e(__('management_users.select_all')); ?></span>
        </label>
            <span id="bulkSelectedCount" class="table-count">0 <?php echo e(__('management_users.selected')); ?></span>
        <div class="bulk-user-actions-right">
            <form id="bulkDeleteForm" method="POST" action="/management/users/bulk-delete" class="bulk-delete-form">
                <?php echo csrf_field(); ?>
                <div id="bulkDeleteHiddenInputs"></div>
                <button id="bulkDeleteBtn" type="submit" class="btn btn-danger-soft" disabled><?php echo e(__('management_users.delete')); ?></button>
            </form>
            <button id="cancelBulkDeleteBtn" type="button" class="btn btn-ghost"><?php echo e(__('management_users.cancel')); ?></button>
        </div>
    </div>
</div>

<div id="importUserModal" class="modal-backdrop<?php echo e((session('openImportModal') || $errors->getBag('userImport')->any()) ? ' open' : ''); ?>" aria-hidden="<?php echo e((session('openImportModal') || $errors->getBag('userImport')->any()) ? 'false' : 'true'); ?>">
    <div class="modal-card">
        <div class="modal-head">
            <h3><?php echo e(__('management_users.import_users')); ?></h3>
            <button id="closeImportUserModal" type="button" class="modal-close" aria-label="<?php echo e(__('management_users.close_modal')); ?>">&times;</button>
        </div>
        <p class="modal-subtitle"><?php echo e(__('management_users.import_subtitle')); ?></p>

        <form method="POST" action="/management/users/import" enctype="multipart/form-data" class="modal-form">
            <?php echo csrf_field(); ?>

            <div class="modal-field modal-field-full">
                <label for="importUserFile"><?php echo e(__('management_users.excel_file')); ?></label>
                <input id="importUserFile" name="import_file" type="file" accept=".xlsx" required>
            </div>

            <div class="modal-actions">
                <button id="cancelImportUserModal" type="button" class="btn btn-ghost"><?php echo e(__('management_users.cancel')); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo e(__('management_users.import')); ?></button>
            </div>
        </form>
    </div>
</div>

<div id="editUserModal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <h3><?php echo e(__('management_users.edit_user')); ?></h3>
            <button id="closeEditUserModal" type="button" class="modal-close" aria-label="<?php echo e(__('management_users.close_modal')); ?>">&times;</button>
        </div>
        <p class="modal-subtitle"><?php echo e(__('management_users.update_user_profile_information')); ?></p>

        <form id="editUserForm" method="POST" action="" class="modal-form">
            <?php echo csrf_field(); ?>

            <div class="modal-field">
                <label for="editUsername"><?php echo e(__('management_users.username')); ?></label>
                <input id="editUsername" name="username" type="text" required>
            </div>

            <div class="modal-field">
                <label for="editName"><?php echo e(__('management_users.name')); ?></label>
                <input id="editName" name="name" type="text" required>
            </div>

            <div class="modal-field">
                <label for="editEmail"><?php echo e(__('management_users.email')); ?></label>
                <input id="editEmail" name="email" type="email" required>
            </div>

            <div class="modal-field">
                <label for="editPhone"><?php echo e(__('management_users.phone')); ?></label>
                <input id="editPhone" name="phonenumber" type="text" required>
            </div>

            <div class="modal-field">
                <label for="editLevel"><?php echo e(__('management_users.level')); ?></label>
                <select id="editLevel" name="levelid" required>
                    <option value=""><?php echo e(__('management_users.select_level')); ?></option>
                    <?php foreach (($levels ?? []) as $level): ?>
                        <option value="<?php echo e($level->levelid); ?>"><?php echo e($level->levelname); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="editRoleField" class="modal-field">
                <label for="editRole"><?php echo e(__('management_users.role')); ?></label>
                <select id="editRole" name="roleid">
                    <option value=""><?php echo e(__('management_users.select_role')); ?></option>
                    <?php foreach (($roles ?? []) as $role): ?>
                        <option value="<?php echo e($role->roleid); ?>"><?php echo e($role->rolename); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="editFamilyFields" class="modal-fieldset hidden">
                <div class="modal-field">
                    <label for="editGender"><?php echo e(__('management_users.gender')); ?></label>
                    <select id="editGender" name="gender">
                        <option value=""><?php echo e(__('management_users.select_gender_placeholder')); ?></option>
                        <option value="male"><?php echo e(__('management_users.male')); ?></option>
                        <option value="female"><?php echo e(__('management_users.female')); ?></option>
                    </select>
                </div>
                <div class="modal-field">
                    <label for="editLifeStatus"><?php echo e(__('management_users.life_status')); ?></label>
                    <select id="editLifeStatus" name="life_status">
                        <option value=""><?php echo e(__('management_users.select_life_status')); ?></option>
                        <option value="alive"><?php echo e(__('management_users.alive')); ?></option>
                        <option value="deceased"><?php echo e(__('management_users.deceased')); ?></option>
                    </select>
                </div>
                <div class="modal-field">
                    <label for="editMaritalStatus"><?php echo e(__('management_users.marital_status')); ?></label>
                    <input id="editMaritalStatus" name="marital_status" type="text">
                </div>
                <div class="modal-field">
                    <label for="editBirthdate"><?php echo e(__('management_users.birthdate')); ?></label>
                    <input id="editBirthdate" name="birthdate" type="date">
                </div>
                <div class="modal-field">
                    <label for="editBirthplace"><?php echo e(__('management_users.birthplace')); ?></label>
                    <input id="editBirthplace" name="birthplace" type="text">
                </div>
                <div class="modal-field">
                    <label for="editAddress"><?php echo e(__('management_users.address')); ?></label>
                    <input id="editAddress" name="address" type="text">
                </div>
                <div class="modal-field">
                    <label for="editJob"><?php echo e(__('management_users.job')); ?></label>
                    <input id="editJob" name="job" type="text">
                </div>
                <div class="modal-field">
                    <label for="editEducationStatus"><?php echo e(__('management_users.education_status')); ?></label>
                    <input id="editEducationStatus" name="education_status" type="text">
                </div>
            </div>

            <div class="modal-actions">
                <button id="cancelEditUserModal" type="button" class="btn btn-ghost"><?php echo e(__('management_users.cancel')); ?></button>
                <button id="deleteUserBtn" type="button" class="btn btn-danger-soft hidden"><?php echo e(__('management_users.delete')); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo e(__('management_users.save_changes')); ?></button>
            </div>
        </form>

        <form id="deleteUserForm" method="POST" action="" class="hidden">
            <?php echo csrf_field(); ?>
        </form>
    </div>
</div>

<div id="resetPasswordConfirmModal" class="message-modal" role="dialog" aria-modal="true" aria-labelledby="resetPasswordConfirmTitle" aria-hidden="true" style="display:none;">
    <div class="message-modal-backdrop"></div>
    <div class="message-modal-card">
        <h4 id="resetPasswordConfirmTitle"><?php echo e(__('management_users.reset_password_modal_title')); ?></h4>
        <div class="message-modal-body">
            <p id="resetPasswordConfirmText" class="message-modal-text is-error"><?php echo e(__('management_users.reset_password_modal_text')); ?></p>
            <p id="resetPasswordConfirmError" class="message-modal-text is-error" style="display:none;"></p>
        </div>
        <div class="modal-actions" style="display:flex; gap:12px; justify-content:flex-end; margin-top: 20px;">
            <button id="resetPasswordConfirmCancelBtn" type="button" class="btn btn-ghost"><?php echo e(__('management_users.cancel')); ?></button>
            <button id="resetPasswordConfirmBtn" type="button" class="btn btn-primary"><?php echo e(__('management_users.reset_password')); ?></button>
        </div>
    </div>
</div>

<div id="resetPasswordSuccessModal" class="message-modal" role="dialog" aria-modal="true" aria-labelledby="resetPasswordSuccessTitle" aria-hidden="true" style="display:none;">
    <div class="message-modal-backdrop"></div>
    <div class="message-modal-card">
        <h4 id="resetPasswordSuccessTitle"><?php echo e(__('management_users.password_reset_successfully')); ?></h4>
        <div class="message-modal-body">
            <p id="resetPasswordSuccessText" class="message-modal-text"></p>
        </div>
        <div class="modal-actions" style="display:flex; gap:12px; justify-content:center; margin-top: 20px;">
            <button id="resetPasswordSuccessOkBtn" type="button" class="btn btn-primary"><?php echo e(__('management_users.ok')); ?></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editUserModal = document.getElementById('editUserModal');
    var editUserForm = document.getElementById('editUserForm');
    var closeEditUserModal = document.getElementById('closeEditUserModal');
    var cancelEditUserModal = document.getElementById('cancelEditUserModal');
    var editUsername = document.getElementById('editUsername');
    var editName = document.getElementById('editName');
    var editEmail = document.getElementById('editEmail');
    var editPhone = document.getElementById('editPhone');
    var editLevel = document.getElementById('editLevel');
    var editRoleField = document.getElementById('editRoleField');
    var editRole = document.getElementById('editRole');
    var editFamilyFields = document.getElementById('editFamilyFields');
    var editGender = document.getElementById('editGender');
    var editLifeStatus = document.getElementById('editLifeStatus');
    var editMaritalStatus = document.getElementById('editMaritalStatus');
    var editBirthdate = document.getElementById('editBirthdate');
    var editBirthplace = document.getElementById('editBirthplace');
    var editAddress = document.getElementById('editAddress');
    var editJob = document.getElementById('editJob');
    var editEducationStatus = document.getElementById('editEducationStatus');
    var deleteUserBtn = document.getElementById('deleteUserBtn');
    var deleteUserForm = document.getElementById('deleteUserForm');
    var resetPasswordConfirmModal = document.getElementById('resetPasswordConfirmModal');
    var resetPasswordConfirmTitle = document.getElementById('resetPasswordConfirmTitle');
    var resetPasswordConfirmText = document.getElementById('resetPasswordConfirmText');
    var resetPasswordConfirmError = document.getElementById('resetPasswordConfirmError');
    var resetPasswordConfirmBtn = document.getElementById('resetPasswordConfirmBtn');
    var resetPasswordConfirmCancelBtn = document.getElementById('resetPasswordConfirmCancelBtn');
    var resetPasswordSuccessModal = document.getElementById('resetPasswordSuccessModal');
    var resetPasswordSuccessTitle = document.getElementById('resetPasswordSuccessTitle');
    var resetPasswordSuccessText = document.getElementById('resetPasswordSuccessText');
    var resetPasswordSuccessOkBtn = document.getElementById('resetPasswordSuccessOkBtn');
    var pendingResetAction = '';
    var pendingResetUsername = '';
    var currentUserId = <?php echo e((int) session('authenticated_user.userid')); ?>;
    var currentRoleId = <?php echo e((int) session('authenticated_user.roleid')); ?>;
    var deleteUserBtnLabel = deleteUserBtn ? deleteUserBtn.textContent : <?php echo json_encode(__('management_users.delete'), JSON_UNESCAPED_UNICODE); ?>;

    if (!editUserModal || !editUserForm) {
        return;
    }

    function syncDeleteButtonState(userId) {
        if (!deleteUserBtn || !deleteUserForm) {
            return;
        }

        var canDelete = [1, 2].indexOf(currentRoleId) !== -1 && userId > 0 && userId !== currentUserId;
        deleteUserBtn.classList.toggle('hidden', !canDelete);
        deleteUserBtn.disabled = !canDelete;
        deleteUserBtn.textContent = deleteUserBtnLabel;

        if (canDelete) {
            deleteUserForm.setAttribute('action', '/management/users/' + userId + '/delete');
        } else {
            deleteUserForm.setAttribute('action', '');
        }
    }

    function openEditModalFromButton(button) {
        var userId = parseInt(button.getAttribute('data-userid') || '0', 10);
        if (!userId) {
            return;
        }

        editUserForm.setAttribute('action', '/management/users/' + userId + '/update');
        if (editUsername) editUsername.value = button.getAttribute('data-username') || '';
        if (editName) editName.value = button.getAttribute('data-fullname') || '';
        if (editEmail) editEmail.value = button.getAttribute('data-email') || '';
        if (editPhone) editPhone.value = button.getAttribute('data-phone') || '';
        if (editLevel) editLevel.value = button.getAttribute('data-levelid') || '';
        if (editRole) editRole.value = button.getAttribute('data-roleid') || '';
        if (editGender) editGender.value = button.getAttribute('data-gender') || '';
        if (editLifeStatus) editLifeStatus.value = String(button.getAttribute('data-life-status') || '').toLowerCase();
        if (editMaritalStatus) editMaritalStatus.value = button.getAttribute('data-marital-status') || '';
        if (editBirthdate) editBirthdate.value = button.getAttribute('data-birthdate') || '';
        if (editBirthplace) editBirthplace.value = button.getAttribute('data-birthplace') || '';
        if (editAddress) editAddress.value = button.getAttribute('data-address') || '';
        if (editJob) editJob.value = button.getAttribute('data-job') || '';
        if (editEducationStatus) editEducationStatus.value = button.getAttribute('data-education-status') || '';

        var levelId = parseInt(button.getAttribute('data-levelid') || '0', 10);
        var isFamilySource = levelId === 2 || levelId === 4;
        if (editFamilyFields) {
            editFamilyFields.classList.toggle('hidden', !isFamilySource);
        }
        [editGender, editLifeStatus, editMaritalStatus, editBirthdate, editBirthplace, editAddress].forEach(function (field) {
            if (!field) return;
            if (isFamilySource) {
                field.setAttribute('required', 'required');
            } else {
                field.removeAttribute('required');
            }
        });

        syncDeleteButtonState(userId);
        editUserModal.classList.add('open');
        editUserModal.setAttribute('aria-hidden', 'false');
    }

    window.openEditUserModalFromButton = openEditModalFromButton;
    window.__openEditUserModal = openEditModalFromButton;

    function openResetPasswordModalFromButton(button) {
        var resetAction = String(button.getAttribute('data-reset-action') || '').trim();
        var username = String(button.getAttribute('data-username') || '').trim();

        if (!resetAction) {
            return false;
        }

        pendingResetAction = resetAction;
        pendingResetUsername = username;

        if (resetPasswordConfirmTitle) {
            resetPasswordConfirmTitle.textContent = <?php echo json_encode(__('management_users.reset_password_modal_title'), JSON_UNESCAPED_UNICODE); ?>;
        }
        if (resetPasswordConfirmText) {
            resetPasswordConfirmText.textContent = username
                ? <?php echo json_encode(__('management_users.confirm_reset_for_user'), JSON_UNESCAPED_UNICODE); ?>.replace(':username', username)
                : <?php echo json_encode(__('management_users.reset_this_user_password'), JSON_UNESCAPED_UNICODE); ?>;
        }
        if (resetPasswordConfirmError) {
            resetPasswordConfirmError.textContent = '';
            resetPasswordConfirmError.style.display = 'none';
        }
        if (resetPasswordConfirmBtn) {
            resetPasswordConfirmBtn.disabled = false;
            resetPasswordConfirmBtn.textContent = <?php echo json_encode(__('management_users.reset_password'), JSON_UNESCAPED_UNICODE); ?>;
        }
        if (resetPasswordConfirmModal) {
            resetPasswordConfirmModal.classList.add('is-open');
            resetPasswordConfirmModal.setAttribute('aria-hidden', 'false');
            resetPasswordConfirmModal.style.display = 'flex';
        }

        return false;
    }

    window.openResetUserModalFromButton = openResetPasswordModalFromButton;
    window.__openResetUserModal = openResetPasswordModalFromButton;

    function closeResetPasswordModal() {
        pendingResetAction = '';
        pendingResetUsername = '';
        if (resetPasswordConfirmError) {
            resetPasswordConfirmError.textContent = '';
            resetPasswordConfirmError.style.display = 'none';
        }
        if (resetPasswordConfirmBtn) {
            resetPasswordConfirmBtn.disabled = false;
            resetPasswordConfirmBtn.textContent = <?php echo json_encode(__('management_users.reset_password'), JSON_UNESCAPED_UNICODE); ?>;
        }
        if (resetPasswordConfirmModal) {
            resetPasswordConfirmModal.classList.remove('is-open');
            resetPasswordConfirmModal.setAttribute('aria-hidden', 'true');
            resetPasswordConfirmModal.style.display = 'none';
        }
    }

    function openResetPasswordSuccessModal(message) {
        if (resetPasswordSuccessTitle) {
            resetPasswordSuccessTitle.textContent = message || <?php echo json_encode(__('management_users.password_reset_successfully'), JSON_UNESCAPED_UNICODE); ?>;
        }
        if (resetPasswordSuccessText) {
            resetPasswordSuccessText.textContent = '';
        }
        if (resetPasswordSuccessModal) {
            resetPasswordSuccessModal.classList.add('is-open');
            resetPasswordSuccessModal.setAttribute('aria-hidden', 'false');
            resetPasswordSuccessModal.style.display = 'flex';
        }
    }

    function closeResetPasswordSuccessModal() {
        if (resetPasswordSuccessModal) {
            resetPasswordSuccessModal.classList.remove('is-open');
            resetPasswordSuccessModal.setAttribute('aria-hidden', 'true');
            resetPasswordSuccessModal.style.display = 'none';
        }
    }

    function closeEditModal() {
        editUserModal.classList.remove('open');
        editUserModal.setAttribute('aria-hidden', 'true');
        if (deleteUserBtn) {
            deleteUserBtn.disabled = false;
            deleteUserBtn.textContent = deleteUserBtnLabel;
        }
        if (deleteUserForm) {
            deleteUserForm.setAttribute('action', '');
        }
    }

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('.js-open-edit-user-modal');
        if (btn) {
            event.preventDefault();
            openEditModalFromButton(btn);
            return;
        }

        var resetBtn = event.target.closest('.js-open-reset-user-modal');
        if (resetBtn) {
            event.preventDefault();
            openResetPasswordModalFromButton(resetBtn);
            return;
        }

        if (event.target === closeEditUserModal || event.target === cancelEditUserModal || event.target === editUserModal) {
            event.preventDefault();
            closeEditModal();
        }

        if (event.target === resetPasswordConfirmCancelBtn || event.target === resetPasswordConfirmModal) {
            event.preventDefault();
            closeResetPasswordModal();
        }

        if (event.target === resetPasswordSuccessOkBtn || event.target === resetPasswordSuccessModal) {
            event.preventDefault();
            closeResetPasswordSuccessModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeEditModal();
            closeResetPasswordModal();
            closeResetPasswordSuccessModal();
        }
    });

    if (resetPasswordConfirmModal) {
        resetPasswordConfirmModal.addEventListener('click', function (event) {
            if (event.target === resetPasswordConfirmModal || event.target.classList.contains('message-modal-backdrop')) {
                closeResetPasswordModal();
            }
        });
    }

    if (resetPasswordSuccessModal) {
        resetPasswordSuccessModal.addEventListener('click', function (event) {
            if (event.target === resetPasswordSuccessModal || event.target.classList.contains('message-modal-backdrop')) {
                closeResetPasswordSuccessModal();
            }
        });
    }

    if (resetPasswordConfirmCancelBtn) {
        resetPasswordConfirmCancelBtn.addEventListener('click', function () {
            closeResetPasswordModal();
        });
    }

    if (resetPasswordSuccessOkBtn) {
        resetPasswordSuccessOkBtn.addEventListener('click', function () {
            closeResetPasswordSuccessModal();
        });
    }

    if (deleteUserBtn && deleteUserForm) {
        deleteUserBtn.addEventListener('click', function () {
            var action = deleteUserForm.getAttribute('action') || '';
            if (!action) {
                return;
            }

            deleteUserBtn.disabled = true;
            deleteUserBtn.textContent = 'Deleting...';

            fetch(action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: new FormData(deleteUserForm)
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: null };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    closeEditModal();
                    if (window.refreshUserTable) {
                        window.refreshUserTable();
                    } else {
                        window.location.reload();
                    }
                    return;
                }

                var message = (result.data && result.data.message) ? result.data.message : 'Failed to delete user.';
                alert(message);
                deleteUserBtn.disabled = false;
                deleteUserBtn.textContent = deleteUserBtnLabel;
            })
            .catch(function (error) {
                console.error('Error:', error);
                alert('An error occurred while deleting the user.');
                deleteUserBtn.disabled = false;
                deleteUserBtn.textContent = deleteUserBtnLabel;
            });
        });
    }

    if (resetPasswordConfirmBtn) {
        resetPasswordConfirmBtn.addEventListener('click', function () {
            if (!pendingResetAction) {
                return;
            }

            resetPasswordConfirmBtn.disabled = true;
            resetPasswordConfirmBtn.textContent = 'Resetting...';

            fetch(pendingResetAction, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (result.ok && result.data && result.data.success) {
                    closeResetPasswordModal();
                    openResetPasswordSuccessModal(result.data.message || <?php echo json_encode(__('management_users.password_reset_successfully'), JSON_UNESCAPED_UNICODE); ?>);
                    return;
                }

                var message = (result.data && result.data.message) ? result.data.message : <?php echo json_encode(__('management_users.failed_to_reset_password'), JSON_UNESCAPED_UNICODE); ?>;
                if (resetPasswordConfirmError) {
                    resetPasswordConfirmError.textContent = message;
                    resetPasswordConfirmError.style.display = 'block';
                }
                resetPasswordConfirmBtn.disabled = false;
                resetPasswordConfirmBtn.textContent = <?php echo json_encode(__('management_users.reset_password'), JSON_UNESCAPED_UNICODE); ?>;
            })
            .catch(function (error) {
                console.error('Error:', error);
                if (resetPasswordConfirmError) {
                    resetPasswordConfirmError.textContent = <?php echo json_encode(__('management_users.reset_password_error'), JSON_UNESCAPED_UNICODE); ?>;
                    resetPasswordConfirmError.style.display = 'block';
                }
                resetPasswordConfirmBtn.disabled = false;
                resetPasswordConfirmBtn.textContent = <?php echo json_encode(__('management_users.reset_password'), JSON_UNESCAPED_UNICODE); ?>;
            });
        });
    }
});
</script>
@endsection

<script>
window.toggleLifeStatus = function(btn) {
    const memberId = btn.getAttribute('data-memberid');
    if (!memberId) {
        alert('This user is not linked to a family member record.');
        return;
    }
    const currentStatus = btn.getAttribute('data-status');
    const newStatus = currentStatus === 'alive' ? 'Deceased' : 'Alive';
    const confirmMsg = 'Change status to ' + newStatus + '?';
    
    if (confirm(confirmMsg)) {
        fetch('/management/users/life-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                memberid: memberId,
                life_status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh table or update icon
                if (window.refreshUserTable) {
                    window.refreshUserTable();
                } else {
                    location.reload();
                }
            } else {
                alert('Failed to update status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
};
</script>


