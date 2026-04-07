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
        $defaultRelationType = old('relation_type', 'child');
        $defaultTargetMemberId = (int) old('target_memberid', $firstMember->memberid ?? 0);
        $targetMember = $members->firstWhere('memberid', $defaultTargetMemberId) ?: $firstMember;
        $relationMap = $relationLabels ?? [];
        $firstMemberRelation = $firstMember ? ($relationMap[(int) $firstMember->memberid] ?? 'Family Member') : 'Family Member';
        $totalMembers = $members->count();
        $aliveMembers = $members->filter(function ($member) {
            return strtolower((string) ($member->life_status ?? '')) === 'alive';
        })->count();
        $deceasedMembers = $members->filter(function ($member) {
            return strtolower((string) ($member->life_status ?? '')) === 'deceased';
        })->count();
    ?>

    <section class="stats">
        <article class="stat-card">
            <small>Total Family Members (Level 2)</small>
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

    <section class="panel">
        <div class="tree-container">
            <div class="tree-head">
                <div>
                    <h3>Family Tree Structure</h3>
                    <p>Showing users with level ID 2.</p>
                </div>
                <input id="searchMember" class="search" type="search" placeholder="Search family member">
            </div>

            <?php if ($members->isEmpty()): ?>
                <p>No family member data found for users with level ID 2.</p>
            <?php else: ?>
                <div class="tree">
                    <ul>
                        <?php foreach ($renderTreeRoots as $node): ?>
                            <?php echo view('all.partials.family-tree-node', [
                                'node' => $node,
                                'initialMemberId' => $firstMember->memberid ?? 0,
                                'relationMap' => $relationMap,
                            ]); ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
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
                    <img
                        id="detailPhoto"
                        class="detail-photo"
                        src="<?php echo e($firstMember->picture ?? ''); ?>"
                        alt="<?php echo e($firstMember->name ?? 'Member'); ?>"
                    >
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
                            <form method="POST" action="/family/profile" class="detail-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="home_panel" value="profile">

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
                    <?php endif; ?>

                    <?php if ($canAddMemberFromHome): ?>
                        <div id="addMemberPanel" class="detail-panel <?php echo e($activePanel === 'add-member' ? '' : 'hidden'); ?>">
                            <h5>Add Family Member</h5>
                            <p>Add a new member (for example: your wife, child, or other family).</p>
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
                                    <small>Click a member card on the left to change the target.</small>
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

                                <div class="detail-form-field">
                                    <label for="memberMaritalStatus">Marital Status</label>
                                    <select id="memberMaritalStatus" name="marital_status" required>
                                        <option value="">Select marital status</option>
                                        <option value="single" <?php echo e(old('marital_status') === 'single' ? 'selected' : ''); ?>>Single</option>
                                        <option value="married" <?php echo e(old('marital_status') === 'married' ? 'selected' : ''); ?>>Married</option>
                                        <option value="divorced" <?php echo e(old('marital_status') === 'divorced' ? 'selected' : ''); ?>>Divorced</option>
                                        <option value="widowed" <?php echo e(old('marital_status') === 'widowed' ? 'selected' : ''); ?>>Widowed</option>
                                    </select>
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
