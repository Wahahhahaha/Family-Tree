<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'location',
        'status',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(EventResponse::class, 'event_id', 'id');
    }
}
