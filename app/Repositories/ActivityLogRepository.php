<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogRepository
{
    /**
     * Get paginated activity logs with user relation.
     */
    public function getPaginated(int $perPage = 20): LengthAwarePaginator
    {
        // Model ActivityLog sudah diatur belongsTo(User::class, 'user_id')
        return ActivityLog::with(['user.familyMember', 'user.employer'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Record a new activity log.
     */
    public function create(array $data): ActivityLog
    {
        return ActivityLog::create($data);
    }
}
