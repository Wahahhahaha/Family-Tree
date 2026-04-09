<div class="wrapper">
    <?php echo view('all.navbar', compact('systemSettings')); ?>

    <?php
        $members = $familyMembers ?? collect();
        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = $members->firstWhere('userid', $currentUserId);
        $firstMember = $currentMember ?: $members->first();
        $restMembers = $members->slice(1);
        $renderTreeRoots = $treeRoots ?? [];
        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        $canEditOwnProfile = $currentLevelId === 2 && !empty($currentFamilyProfile);
        $canAddMemberFromHome = $currentRoleId === 3 || $currentLevelId === 2;
        $activePanel = old('home_panel', 'profile');
        $currentMemberHasPartner = (bool) ($currentMemberHasPartner ?? false);
        $defaultRelationType = old('relation_type', 'child');
        $defaultChildParentingMode = old('child_parenting_mode', $currentMemberHasPartner ? 'with_current_partner' : 'single_parent');
        if (!$currentMemberHasPartner && $defaultChildParentingMode === 'with_current_partner') {
            $defaultChildParentingMode = 'single_parent';
        }
        $defaultTargetMemberId = (int) ($currentMember->memberid ?? 0);
        $targetMember = $currentMember ?: $firstMember;
        $isFirstMemberMe = $firstMember && (int) ($firstMember->userid ?? 0) === $currentUserId;
        $relationMap = $relationLabels ?? [];
        $firstMemberRelation = $firstMember ? ($relationMap[(int) $firstMember->memberid] ?? 'Family Member') : 'Family Member';
        $totalMembers = $members->count();
        $aliveMembers = $members->filter(function ($member) {
            return strtolower((string) ($member->life_status ?? '')) === 'alive';
        })->count();
        $deceasedMembers = $members->filter(function ($member) {
            return strtolower((string) ($member->life_status ?? '')) === 'deceased';
        })->count();
        $flashMessages = [];
        if (session('success')) {
            $flashMessages[] = ['type' => 'success', 'text' => (string) session('success')];
        }
        if (session('error')) {
            $flashMessages[] = ['type' => 'error', 'text' => (string) session('error')];
        }
        if ($errors->any()) {
            foreach ($errors->all() as $errorText) {
                $flashMessages[] = ['type' => 'error', 'text' => (string) $errorText];
            }
        }
        $addMemberSuccessModal = strtolower((string) session('success', '')) === 'new family member has been added.';
    ?>

    <section class="stats">
        <article class="stat-card">
            <small>Total Family Members</small>
            <h2><?php echo e($totalMembers); ?></h2>
        </article>
        <article class="stat-card">
            <small>Alive</small>
            <h2><?php echo e($aliveMembers); ?></h2>
        </article>
        <article class="stat-card">
            <small>Deceased</small>
            <h2><?php echo e($deceasedMembers); ?></h2>
        </article>
        <article class="stat-card">
            <small>Last Updated</small>
            <h2 class="last-update"><?php echo e(now()->format('d M Y')); ?></h2>
        </article>
    </section>

    <?php if (!empty($flashMessages)): ?>
        <div id="flashMessageModal" class="message-modal is-open" role="dialog" aria-modal="true" aria-labelledby="flashMessageTitle">
            <div class="message-modal-backdrop"></div>
            <div class="message-modal-card <?php echo e($addMemberSuccessModal ? 'is-add-member-success' : ''); ?>">
                <h4 id="flashMessageTitle"><?php echo e($addMemberSuccessModal ? 'Member Added' : 'Message'); ?></h4>
                <?php if ($addMemberSuccessModal): ?>
                    <div class="message-modal-success-icon" aria-hidden="true">
                        <span></span>
                    </div>
                <?php endif; ?>
                <div class="message-modal-body">
                    <?php foreach ($flashMessages as $flashMessage): ?>
                        <p class="message-modal-text <?php echo e($flashMessage['type'] === 'success' ? 'is-success' : 'is-error'); ?>">
                            <?php echo e($flashMessage['text']); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <button id="flashMessageOkBtn" type="button" class="btn btn-primary">OK</button>
            </div>
        </div>
    <?php endif; ?>

    <section class="panel">
        <div class="tree-container">
            <div class="tree-head">
                <div>
                    <h3>Family Tree Structure</h3>
                    <p>Get to know your family members.</p>
                </div>
                <div class="tree-tools">
                    <input id="searchMember" class="search" type="search" placeholder="Search family member">
                    <div class="tree-zoom-controls">
                        <button id="treeZoomOutBtn" class="btn btn-ghost tree-zoom-btn" type="button" aria-label="Zoom out">-</button>
                        <span id="treeZoomValue" class="tree-zoom-value">100%</span>
                        <button id="treeZoomInBtn" class="btn btn-ghost tree-zoom-btn" type="button" aria-label="Zoom in">+</button>
                    </div>
                </div>
            </div>

            <div id="treeScrollArea" class="tree-scroll">
                <?php if ($members->isEmpty()): ?>
                    <p>No family member data found for users with level ID 2.</p>
                <?php else: ?>
                    <div id="treeZoomStage" class="tree-zoom-stage">
                        <div id="treeCanvas" class="tree">
                            <ul>
                                <?php foreach ($renderTreeRoots as $node): ?>
                                    <?php echo view('all.partials.family-tree-node', [
                                        'node' => $node,
                                        'initialMemberId' => $firstMember->memberid ?? 0,
                                        'relationMap' => $relationMap,
                                        'depth' => 0,
                                    ]); ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="detail">
            <?php if ($canEditOwnProfile || $canAddMemberFromHome): ?>
                <div class="detail-panel-switch">
                    <?php if ($canEditOwnProfile): ?>
                        <button
                            id="profilePanelBtn"
                            type="button"
                            class="btn btn-ghost panel-switch-btn <?php echo e($activePanel !== 'add-member' ? 'is-active' : ''); ?>"
                        >
                            Profile
                        </button>
                    <?php endif; ?>
                    <?php if ($canAddMemberFromHome): ?>
                        <button
                            id="addMemberPanelBtn"
                            type="button"
                            class="btn btn-ghost panel-switch-btn <?php echo e($activePanel === 'add-member' ? 'is-active' : ''); ?>"
                        >
                            Add Member
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div id="memberDetailBlock" class="<?php echo e($activePanel === 'add-member' ? 'hidden' : ''); ?>">
                <h4>Member Details</h4>
                <div class="detail-card">
                    <div id="detailPhotoWrap" class="detail-photo-wrap <?php echo e($isFirstMemberMe ? 'is-editable' : ''); ?>">
                        <img
                            id="detailPhoto"
                            class="detail-photo <?php echo e($isFirstMemberMe ? 'is-editable' : ''); ?>"
                            src="<?php echo e($firstMember->picture ?? ''); ?>"
                            alt="<?php echo e($firstMember->name ?? 'Member'); ?>"
                            data-isme="<?php echo e($isFirstMemberMe ? '1' : '0'); ?>"
                        >
                        <span class="detail-photo-overlay" aria-hidden="true">
                            <span class="detail-photo-icon"></span>
                        </span>
                    </div>
                    <h5 id="detailName" class="detail-name"><?php echo e($firstMember->name ?? '-'); ?></h5>
                    <p id="detailRole" class="detail-role"><?php echo e($firstMemberRelation); ?></p>
                    <ul class="detail-list">
                        <li><span>Age</span><strong id="detailAge"><?php echo e($firstMember->age ?? '-'); ?></strong></li>
                        <li><span>Status</span><strong id="detailStatus"><?php echo e(isset($firstMember->life_status) ? ucfirst((string) $firstMember->life_status) : '-'); ?></strong></li>
                        <li><span>Job</span><strong id="detailJob"><?php echo e($firstMember->job ?? '-'); ?></strong></li>
                        <li><span>Address</span><strong id="detailAddress"><?php echo e($firstMember->address ?? '-'); ?></strong></li>
                        <li><span>Education</span><strong id="detailEducation"><?php echo e($firstMember->education_status ?? '-'); ?></strong></li>
                    </ul>
                </div>
            </div>

            <?php if ($canEditOwnProfile || $canAddMemberFromHome): ?>
                <div class="detail-form-wrap">
                    <?php if ($canEditOwnProfile): ?>
                        <div id="profilePanel" class="detail-panel <?php echo e($activePanel === 'add-member' ? 'hidden' : ''); ?>">
                            <h5>Complete Your Profile</h5>
                            <p>Fill in job, address, and education if your data is empty.</p>
                            <div id="profileAjaxAlert" class="hidden"></div>
                            <form id="profileForm" method="POST" action="/family/profile" class="detail-form" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="home_panel" value="profile">
                                <input id="profilePictureInput" type="file" name="picture" accept="image/*" class="hidden">

                                <div class="detail-form-field">
                                    <label for="profileJob">Job</label>
                                    <input
                                        id="profileJob"
                                        type="text"
                                        name="job"
                                        value="<?php echo e(old('job', $currentFamilyProfile->job ?? '')); ?>"
                                        placeholder="Example: Engineer"
                                    >
                                </div>

                                <div class="detail-form-field">
                                    <label for="profileAddress">Address</label>
                                    <input
                                        id="profileAddress"
                                        type="text"
                                        name="address"
                                        value="<?php echo e(old('address', $currentFamilyProfile->address ?? '')); ?>"
                                        placeholder="Enter your address"
                                    >
                                </div>

                                <div class="detail-form-field">
                                    <label for="profileEducation">Education</label>
                                    <input
                                        id="profileEducation"
                                        type="text"
                                        name="education_status"
                                        value="<?php echo e(old('education_status', $currentFamilyProfile->education_status ?? '')); ?>"
                                        placeholder="Example: Bachelor Degree"
                                    >
                                </div>

                                <button type="submit" class="btn btn-primary">Save Profile</button>
                            </form>
                        </div>

                        <div id="photoCropModal" class="photo-crop-modal hidden" role="dialog" aria-modal="true" aria-labelledby="photoCropTitle">
                            <div class="photo-crop-backdrop"></div>
                            <div class="photo-crop-card">
                                <h4 id="photoCropTitle">Crop Profile Photo</h4>
                                <p>Move and zoom to adjust your photo.</p>
                                <div class="photo-crop-stage-wrap">
                                    <canvas id="photoCropCanvas" class="photo-crop-canvas" width="320" height="320"></canvas>
                                </div>
                                <div class="photo-crop-zoom">
                                    <label for="photoCropZoom">Zoom</label>
                                    <input id="photoCropZoom" type="range" min="1" max="3" step="0.01" value="1">
                                </div>
                                <div class="photo-crop-actions">
                                    <button id="photoCropCancelBtn" type="button" class="btn btn-ghost">Cancel</button>
                                    <button id="photoCropApplyBtn" type="button" class="btn btn-primary">Apply</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($canAddMemberFromHome): ?>
                        <div id="addMemberPanel" class="detail-panel <?php echo e($activePanel === 'add-member' ? '' : 'hidden'); ?>">
                           <form method="POST" action="/family/member/store" class="detail-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="home_panel" value="add-member">
                                <input type="hidden" id="relationTypeInput" name="relation_type" value="<?php echo e($defaultRelationType); ?>">
                                <input type="hidden" id="targetMemberIdInput" name="target_memberid" value="<?php echo e($defaultTargetMemberId); ?>">

                                <div class="detail-form-field">
                                    <label>Relation Type</label>
                                    <div class="relation-btn-row">
                                        <button
                                            id="addChildBtn"
                                            type="button"
                                            class="btn btn-ghost relation-btn <?php echo e($defaultRelationType === 'child' ? 'is-active' : ''); ?>"
                                            data-relation-type="child"
                                        >
                                            Add Child
                                        </button>
                                        <button
                                            id="addPartnerBtn"
                                            type="button"
                                            class="btn btn-ghost relation-btn <?php echo e($defaultRelationType === 'partner' ? 'is-active' : ''); ?>"
                                            data-relation-type="partner"
                                        >
                                            Add Partner
                                        </button>
                                    </div>
                                </div>

                                <div class="detail-form-field">
                                    <label for="targetMemberNameInput">Related To</label>
                                    <input
                                        id="targetMemberNameInput"
                                        type="text"
                                        value="<?php echo e($targetMember->name ?? '-'); ?>"
                                        readonly
                                    >
                                    <small>Member relation is always added under your account.</small>
                                </div>

                                <div id="childParentingModeField" class="detail-form-field <?php echo e($defaultRelationType === 'child' ? '' : 'hidden'); ?>">
                                    <label for="childParentingMode">Child Parent Mode</label>
                                    <select id="childParentingMode" name="child_parenting_mode">
                                        <option value="with_current_partner" <?php echo e($defaultChildParentingMode === 'with_current_partner' ? 'selected' : ''); ?> <?php echo e(!$currentMemberHasPartner ? 'disabled' : ''); ?>>With current partner</option>
                                        <option value="single_parent" <?php echo e($defaultChildParentingMode === 'single_parent' ? 'selected' : ''); ?>>Single parent</option>
                                    </select>
                                    <small>Use Single parent if the child is not from your current partner.</small>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberName">Name</label>
                                    <input id="memberName" type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="Enter full name" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberUsername">Username</label>
                                    <input id="memberUsername" type="text" name="username" value="<?php echo e(old('username')); ?>" placeholder="Enter username" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberGender">Gender</label>
                                    <select id="memberGender" name="gender" required>
                                        <option value="">Select gender</option>
                                        <option value="male" <?php echo e(old('gender') === 'male' ? 'selected' : ''); ?>>Male</option>
                                        <option value="female" <?php echo e(old('gender') === 'female' ? 'selected' : ''); ?>>Female</option>
                                    </select>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberEmail">Email</label>
                                    <input id="memberEmail" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="Enter email" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberPhone">Phone Number</label>
                                    <input id="memberPhone" type="text" name="phonenumber" value="<?php echo e(old('phonenumber')); ?>" placeholder="Enter phone number" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberBirthdate">Birthdate</label>
                                    <input id="memberBirthdate" type="date" name="birthdate" value="<?php echo e(old('birthdate')); ?>" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberBirthplace">Birthplace</label>
                                    <input id="memberBirthplace" type="text" name="birthplace" value="<?php echo e(old('birthplace')); ?>" placeholder="Enter birthplace" required>
                                </div>

                                <div class="detail-form-field">
                                    <label for="memberAddress">Address</label>
                                    <input id="memberAddress" type="text" name="address" value="<?php echo e(old('address')); ?>" placeholder="Enter address" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Add Member</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>
    </section>
</div>
