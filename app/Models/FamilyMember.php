<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyMember extends Model
{
    protected $table = 'family_member';
    protected $primaryKey = 'memberid';
    public $timestamps = false;

    protected $fillable = [
        'userid', 'name', 'gender', 'birthdate', 'birthplace',
        'bloodtype', 'life_status', 'marital_status', 'picture', 'burial_location', 'burial_latitude', 'burial_longitude',
        'email', 'phonenumber', 'job', 'address', 'education_status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class, 'memberid', 'memberid');
    }

    public function relatedRelationships(): HasMany
    {
        return $this->hasMany(Relationship::class, 'relatedmemberid', 'memberid');
    }
}
