<?php if (!empty($activityLogs) && count($activityLogs) > 0): ?>
    <?php foreach ($activityLogs as $log): ?>
        <?php
            $actor = (array) ($log['actor'] ?? []);
            $username = trim((string) ($actor['username'] ?? ''));
            if ($username === '') {
                $username = trim((string) ($actor['name'] ?? ''));
            }
            if ($username === '') {
                $username = '-';
            }

            $time = trim((string) ($log['created_at'] ?? '-'));
            $action = trim((string) ($log['action'] ?? '-'));
            $ipAddress = trim((string) ($log['ip_address'] ?? '-'));
            $longitude = $log['longitude'] ?? null;
            $latitude = $log['latitude'] ?? null;
            $details = $log['context'] ?? [];
            if (is_array($details) || is_object($details)) {
                $details = json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            $details = trim((string) $details);
            if ($details === '') {
                $details = '-';
            }
            if (mb_strlen($details) > 120) {
                $details = mb_substr($details, 0, 117) . '...';
            }

            $formatCoordinate = function ($value) {
                if ($value === null || $value === '') {
                    return '-';
                }

                if (!is_numeric($value)) {
                    return '-';
                }

                return rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');
            };
        ?>
        <tr>
            <td><?php echo e($time); ?></td>
            <td><?php echo e($username); ?></td>
            <td><?php echo e($action); ?></td>
            <td><?php echo e($ipAddress); ?></td>
            <td><?php echo e($formatCoordinate($longitude)); ?></td>
            <td><?php echo e($formatCoordinate($latitude)); ?></td>
            <td title="<?php echo e(is_string($details) ? $details : ''); ?>"><?php echo e($details); ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" style="text-align:center; padding: 20px;">{{ __('management_activity_log.no_activity_logs_found') }}</td>
    </tr>
<?php endif; ?>
