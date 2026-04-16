<div class="wrapper">

    <?php echo view('all.navbar', compact('systemSettings')); ?>


    <section class="management-card">
        <div class="management-head">
            <div>
                <h2>User Management</h2>
                <p>List of application users and related profile information.</p>
            </div>
            <div class="management-tools">
                <span id="userTableCount" class="table-count">Total: <?php echo e($users->total()); ?> users</span>

                <a href="/management/users/export" class="btn btn-export">Export .xlsx</a>
                <button id="openImportUserModal" type="button" class="btn btn-ghost">Import</button>
                <button id="openAddUserModal" type="button" class="btn btn-primary">Add User</button>
            </div>
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
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php echo view('admin.partials.user-table-rows', ['users' => $users]); ?>
                </tbody>
            </table>
        </div>

        <div id="userPagination">
            <?php echo view('admin.partials.user-pagination', ['users' => $users]); ?>
        </div>
    </section>
</div>

<div id="addUserModal" class="modal-backdrop<?php echo e($errors->any() ? ' open' : ''); ?>" aria-hidden="<?php echo e($errors->any() ? 'false' : 'true'); ?>">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Add New User</h3>
            <button id="closeAddUserModal" type="button" class="modal-close" aria-label="Close modal">&times;</button>
        </div>
        <p class="modal-subtitle">Create a new user account for the system.</p>

        <form id="addUserForm" method="POST" action="/management/users/store" class="modal-form">
            <?php echo csrf_field(); ?>

            <div id="addUserAjaxErrors" class="alert-error hidden"></div>

            <div class="modal-field">
                <label for="newLevel">Level</label>
                <select id="newLevel" name="levelid" required>
                    <option value="">Select level</option>
                    <?php foreach ($levels as $level): ?>
                        <option
                            value="<?php echo e($level->levelid); ?>"
                            data-level-group="<?php echo e(in_array((int) $level->levelid, [2, 4], true) ? 'family' : 'employer'); ?>"
                            <?php echo e((string) old('levelid') === (string) $level->levelid ? 'selected' : ''); ?>
                        >
                            <?php echo e($level->levelname); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="newRoleField" class="modal-field">
                <label for="newRole">Role</label>
                <select id="newRole" name="roleid" required disabled>
                    <option value="">Select role</option>
                    <?php foreach ($roles as $role): ?>
                        <option
                            value="<?php echo e($role->roleid); ?>"
                            data-role-group="<?php echo e(in_array((int) $role->roleid, [3, 4], true) ? 'family' : 'employer'); ?>"
                            <?php echo e((string) old('roleid') === (string) $role->roleid ? 'selected' : ''); ?>
                        >
                            <?php echo e($role->rolename); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="dynamicFields" class="modal-fieldset hidden">
                <div class="modal-field">
                    <label for="newName">Name</label>
                    <input id="newName" name="name" type="text" placeholder="Enter full name" value="<?php echo e(old('name')); ?>" required>
                </div>

                <div class="modal-field">
                    <label for="newUsername">Username</label>
                    <input id="newUsername" name="username" type="text" placeholder="Enter username" value="<?php echo e(old('username')); ?>" required>
                </div>

                <div id="contactFields" class="modal-fieldset">
                    <div class="modal-field">
                        <label for="newEmail">Email</label>
                        <input id="newEmail" name="email" type="email" placeholder="Enter email" value="<?php echo e(old('email')); ?>">
                    </div>

                    <div class="modal-field">
                        <label for="newPhone">Phone Number</label>
                        <input id="newPhone" name="phonenumber" type="text" placeholder="Enter phone number" value="<?php echo e(old('phonenumber')); ?>">
                    </div>
                </div>

                <div id="familyFields" class="modal-fieldset hidden">
                    <div class="modal-field">
                        <label for="familyGender">Gender</label>
                        <select id="familyGender" name="gender">
                            <option value="">Select gender</option>
                            <option value="male" <?php echo e(old('gender') === 'male' ? 'selected' : ''); ?>>Male</option>
                            <option value="female" <?php echo e(old('gender') === 'female' ? 'selected' : ''); ?>>Female</option>
                        </select>
                    </div>

                    <div class="modal-field">
                        <label for="familyAddress">Address</label>
                        <input id="familyAddress" name="address" type="text" placeholder="Enter address" value="<?php echo e(old('address')); ?>">
                    </div>

                    <div class="modal-field">
                        <label for="familyMaritalStatus">Marital Status</label>
                        <select id="familyMaritalStatus" name="marital_status">
                            <option value="">Select marital status</option>
                            <option value="single" <?php echo e(old('marital_status') === 'single' ? 'selected' : ''); ?>>Single</option>
                            <option value="married" <?php echo e(old('marital_status') === 'married' ? 'selected' : ''); ?>>Married</option>
                            <option value="divorced" <?php echo e(old('marital_status') === 'divorced' ? 'selected' : ''); ?>>Divorced</option>
                            <option value="widowed" <?php echo e(old('marital_status') === 'widowed' ? 'selected' : ''); ?>>Widowed</option>
                        </select>
                    </div>

                    <div class="modal-field">
                        <label for="familyBirthdate">Birthdate</label>
                        <input id="familyBirthdate" name="birthdate" type="date" value="<?php echo e(old('birthdate')); ?>">
                    </div>

                    <div class="modal-field">
                        <label for="familyAge">Age</label>
                        <input id="familyAge" type="text" value="" placeholder="Auto calculated from birthdate" readonly>
                    </div>

                    <div class="modal-field">
                        <label for="familyBirthplace">Birthplace</label>
                        <input id="familyBirthplace" name="birthplace" type="text" placeholder="Enter birthplace" value="<?php echo e(old('birthplace')); ?>">
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button id="cancelAddUserModal" type="button" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<div id="importUserModal" class="modal-backdrop<?php echo e((session('openImportModal') || $errors->getBag('userImport')->any()) ? ' open' : ''); ?>" aria-hidden="<?php echo e((session('openImportModal') || $errors->getBag('userImport')->any()) ? 'false' : 'true'); ?>">
    <div class="modal-card">
        <div class="modal-head">
            <h3>Import Users</h3>
            <button id="closeImportUserModal" type="button" class="modal-close" aria-label="Close modal">&times;</button>
        </div>
        <p class="modal-subtitle">Upload file Excel `.xlsx` untuk menambahkan data user secara massal.</p>

        <form method="POST" action="/management/users/import" enctype="multipart/form-data" class="modal-form">
            <?php echo csrf_field(); ?>

            <div class="modal-field modal-field-full">
                <label for="importUserFile">Excel File (.xlsx)</label>
                <input id="importUserFile" name="import_file" type="file" accept=".xlsx" required>
            </div>

            <div class="modal-actions">
                <button id="cancelImportUserModal" type="button" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>
