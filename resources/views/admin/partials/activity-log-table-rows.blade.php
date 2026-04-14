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
            $websiteNameOld = trim((string) ($context['website_name_old'] ?? ''));
            $websiteNameNew = trim((string) ($context['website_name_new'] ?? ''));
            $settingChanges = $context['changes'] ?? [];
            $profileChanges = $context['changes'] ?? [];
            $lifeStatusOld = trim((string) ($context['life_status_old'] ?? ''));
            $lifeStatusNew = trim((string) ($context['life_status_new'] ?? ''));

            $formatChangeValue = function (string $value): string {
                return $value !== '' ? '"' . $value . '"' : '"empty"';
            };

            $formatTitleValue = function (string $value): string {
                return $value !== '' ? '"' . ucfirst($value) . '"' : '"empty"';
            };

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
                $detailText = 'Successful Log In';
            } elseif ($action === 'logout') {
                $detailText = 'Logged Out';
            } elseif ($action === 'family.edit_profile' || $action === 'account.update_admin_profile') {
                if (is_array($profileChanges) && count($profileChanges) > 0) {
                    $messages = [];
                    foreach ($profileChanges as $change) {
                        if (!is_array($change)) {
                            continue;
                        }

                        $field = trim((string) ($change['field'] ?? 'profile'));
                        $oldValue = trim((string) ($change['old'] ?? ''));
                        $newValue = trim((string) ($change['new'] ?? ''));

                        if ($field === 'profile picture') {
                            if ($oldValue === '' && $newValue !== '') {
                                $messages[] = 'Added profile picture';
                            } elseif ($oldValue !== '' && $newValue === '') {
                                $messages[] = 'Removed profile picture';
                            } else {
                                $messages[] = 'Updated profile picture';
                            }
                            continue;
                        }

                        $messages[] = 'Edited profile ' . $field . ' from ' . $formatChangeValue($oldValue) . ' to ' . $formatChangeValue($newValue);
                    }

                    if (count($messages) > 0) {
                        $detailText = implode(', ', $messages);
                    } else {
                        $detailText = 'Updated by: ' . $actorName;
                    }
                } else {
                    $detailText = 'Updated by: ' . $actorName;
                }
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
                $targetText = $targetName !== '' ? $targetName : ($targetRelationLabel !== '' ? $targetRelationLabel : 'Member');
                if ($lifeStatusOld !== '' || $lifeStatusNew !== '') {
                    $detailText = 'Changed ' . $targetText . ' life status from ' . $formatTitleValue($lifeStatusOld) . ' to ' . $formatTitleValue($lifeStatusNew);
                } else {
                    $detailText = $targetText;
                }
            } elseif ($action === 'management.create_user') {
                if ($newUsername !== '') {
                    $detailText = 'Created user with username ' . $formatChangeValue($newUsername);
                }
            } elseif ($action === 'management.reset_password') {
                if ($targetUsername !== '') {
                    $detailText = 'Reset password for user ' . $formatChangeValue($targetUsername);
                }
            } elseif ($action === 'management.delete_user') {
                if ($targetUsername !== '') {
                    $detailText = 'Deleted user ' . $formatChangeValue($targetUsername);
                }
            } elseif ($action === 'management.restore_user') {
                if ($targetUsername !== '') {
                    $detailText = 'Restored user ' . $formatChangeValue($targetUsername);
                }
            } elseif ($action === 'management.force_delete_user') {
                if ($targetUsername !== '') {
                    $detailText = 'Permanently deleted user ' . $formatChangeValue($targetUsername);
                }
            } elseif ($action === 'superadmin.update_setting') {
                if (is_array($settingChanges) && count($settingChanges) > 0) {
                    $detailText = implode(', ', array_map(static fn ($change) => (string) $change, $settingChanges));
                } elseif ($websiteNameOld !== '' || $websiteNameNew !== '') {
                    $detailText = 'Changed website name from ' . $formatChangeValue($websiteNameOld) . ' to ' . $formatChangeValue($websiteNameNew);
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
