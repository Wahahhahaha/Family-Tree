<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    protected $primaryKey = 'id';
    
    // Sinkron dengan DB: user_id, ip_adress
    protected $fillable = [
        'action', 'user_id', 'context', 'ip_adress', 'user_agent', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'userid');
    }
}
