<?php if ($activityLogs->count() === 0): ?>
    <tr>
        <td colspan="7">No activity has been recorded yet.</td>
    </tr>
<?php else: ?>
    <?php foreach ($activityLogs as $log): ?>
        <?php
            $timestamp = (string) ($log['timestamp'] ?? '-');
            $action = (string) ($log['action'] ?? '-');
            $actor = $log['actor'] ?? [];
            $actorName = (string) ($actor['username'] ?? 'guest');
            $actorId = isset($actor['userid']) ? (int) $actor['userid'] : null;
            $ip = (string) ($log['ip'] ?? '-');
            $longitude = isset($log['longitude']) ? (string) $log['longitude'] : '-';
            $latitude = isset($log['latitude']) ? (string) $log['latitude'] : '-';
            $context = $log['context'] ?? [];
            $newMemberName = trim((string) ($context['new_member_name'] ?? ''));
            $newMemberGender = strtolower((string) ($context['new_member_gender'] ?? ''));
            $targetName = trim((string) ($context['target_name'] ?? ''));
            $targetGender = strtolower((string) ($context['target_gender'] ?? ''));
            $relationType = (string) ($context['relation_type'] ?? '');
            $relationDeleted = (string) ($context['relation_deleted'] ?? '');
            $relationRestored = (string) ($context['relation_restored'] ?? '');
            $targetRelationLabel = trim((string) ($context['target_relation_label'] ?? ''));
            $targetUsername = trim((string) ($context['target_username'] ?? ''));
            $newUsername = trim((string) ($context['username'] ?? ''));

            $genderToPartnerLabel = function (string $gender): string {
                return $gender === 'female'
                    ? 'Wife'
                    : ($gender === 'male' ? 'Husband' : 'Partner');
            };

            $memberLabelByRelationAndGender = function (string $relation, string $gender) use ($genderToPartnerLabel): string {
                if ($relation === 'partner') {
                    return $genderToPartnerLabel($gender);
                }
                if ($relation === 'child') {
                    return 'Child';
                }
                if ($relation === 'user') {
                    return 'User';
                }
                return 'Member';
            };

            $actionLabel = $action;
            if ($action === 'login') {
                $actionLabel = 'Login';
            } elseif ($action === 'logout') {
                $actionLabel = 'Logout';
            } elseif ($action === 'family.edit_profile' || $action === 'account.update_admin_profile') {
                $actionLabel = 'Edit Profile';
            } elseif ($action === 'family.add_relationship') {
                if ($relationType === 'partner') {
                    $actionLabel = $newMemberGender === 'female'
                        ? 'Add Wife'
                        : ($newMemberGender === 'male' ? 'Add Husband' : 'Add Partner');
                } elseif ($relationType === 'child') {
                    $actionLabel = 'Add Child';
                }
            } elseif ($action === 'family.delete_relationship') {
                if (in_array($relationDeleted, ['partner', 'child'], true)) {
                    $actionLabel = 'Delete ' . $memberLabelByRelationAndGender($relationDeleted, $targetGender) . ' Permanently';
                }
            } elseif (in_array($action, ['family.restore_relationship', 'family.restore_member'], true)) {
                if (in_array($relationRestored, ['partner', 'child'], true)) {
                    $actionLabel = 'Restore ' . $memberLabelByRelationAndGender($relationRestored, $targetGender);
                }
            } elseif (in_array($action, ['family.force_delete_relationship', 'family.force_delete_member'], true)) {
                if (in_array($relationDeleted, ['partner', 'child'], true)) {
                    $actionLabel = 'Delete ' . $memberLabelByRelationAndGender($relationDeleted, $targetGender) . ' Permanently';
                }
            } elseif ($action === 'family.update_life_status') {
                $relationText = $targetRelationLabel !== '' ? $targetRelationLabel : 'Member';
                $actionLabel = 'Updated ' . $relationText . ' Life Status';
            } else {
                $actionLabel = match ($action) {
                    'management.create_user' => 'Add User',
                    'management.reset_password' => 'Reset Password User',
                    'management.delete_user' => 'Delete User Permanently',
                    'management.restore_user' => 'Restore User',
                    'management.force_delete_user' => 'Delete User Permanently',
                    'superadmin.update_setting' => 'Updated System Settings',
                    default => $action,
                };
            }

            $detailText = '-';

            if ($action === 'login') {
                $targetUsername = (string) ($context['target_username'] ?? $actorName);
                $detailText = 'User: ' . $targetUsername;
            } elseif ($action === 'logout') {
                $detailText = 'User: ' . $actorName;
            } elseif ($action === 'family.edit_profile' || $action === 'account.update_admin_profile') {
                $detailText = 'Updated by: ' . $actorName;
            } elseif ($action === 'family.add_relationship') {
                if ($relationType === 'partner') {
                    $partnerLabel = $newMemberGender === 'female'
                        ? 'Wife'
                        : ($newMemberGender === 'male' ? 'Husband' : 'Partner');
                    $detailText = 'Added ' . $partnerLabel . ($newMemberName !== '' ? ' ' . $newMemberName : '');
                } elseif ($relationType === 'child') {
                    $childLabel = $newMemberGender === 'female'
                        ? 'Daughter'
                        : ($newMemberGender === 'male' ? 'Son' : 'Child');
                    $detailText = 'Added ' . $childLabel . ($newMemberName !== '' ? ' ' . $newMemberName : '');
                }
            } elseif ($action === 'family.delete_relationship') {
                if ($targetName !== '') {
                    $detailText = 'Deleted: ' . $targetName;
                }
            } elseif (in_array($action, ['family.restore_relationship', 'family.restore_member'], true)) {
                if ($targetName !== '') {
                    $detailText = 'Restored: ' . $targetName;
                }
            } elseif (in_array($action, ['family.force_delete_relationship', 'family.force_delete_member'], true)) {
                if ($targetName !== '') {
                    $detailText = 'Permanently deleted: ' . $targetName;
                }
            } elseif ($action === 'family.update_life_status') {
                $lifeStatus = (string) ($context['life_status'] ?? '');
                $targetText = $targetName !== '' ? $targetName : ($targetRelationLabel !== '' ? $targetRelationLabel : 'Member');
                if ($lifeStatus !== '') {
                    $detailText = $targetText . ' status: ' . ucfirst($lifeStatus);
                } else {
                    $detailText = $targetText;
                }
            } elseif ($action === 'management.create_user') {
                if ($newUsername !== '') {
                    $detailText = 'Username: ' . $newUsername;
                }
            } elseif ($action === 'management.reset_password') {
                if ($targetUsername !== '') {
                    $detailText = 'Reset: ' . $targetUsername;
                }
            } elseif ($action === 'management.delete_user') {
                if ($targetUsername !== '') {
                    $detailText = 'Deleted: ' . $targetUsername;
                }
            } elseif ($action === 'management.restore_user') {
                if ($targetUsername !== '') {
                    $detailText = 'Restored: ' . $targetUsername;
                }
            } elseif ($action === 'management.force_delete_user') {
                if ($targetUsername !== '') {
                    $detailText = 'Permanently deleted: ' . $targetUsername;
                }
            }

            if ($detailText === '-' && !empty($context)) {
                $detailText = json_encode($context, JSON_UNESCAPED_SLASHES);
            }
        ?>
        <tr>
            <td><?php echo e($timestamp); ?></td>
            <td>
                <?php echo e($actorName); ?>
                <?php if (!is_null($actorId)): ?>
                    (ID: <?php echo e($actorId); ?>)
                <?php endif; ?>
            </td>
            <td><?php echo e($actionLabel); ?></td>
            <td><?php echo e($ip); ?></td>
            <td><?php echo e($longitude); ?></td>
            <td><?php echo e($latitude); ?></td>
            <td><?php echo e($detailText); ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
