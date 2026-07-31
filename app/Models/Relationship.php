<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relationship extends Model
{
    use SoftDeletes;

    protected $table = 'relationship';
    protected $primaryKey = 'relationid';

    public $timestamps = false;

    protected $fillable = [
        'memberid',
        'relatedmemberid',
        'relationtype',
        'child_parenting_mode',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'memberid', 'memberid');
    }

    public function relatedMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'relatedmemberid', 'memberid');
    }
}
