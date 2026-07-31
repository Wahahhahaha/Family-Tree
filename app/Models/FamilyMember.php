<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'family_member';

    protected $primaryKey = 'memberid';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'pending_email',
        'email_verification_token',
        'email_verification_token_expires_at',
        'phonenumber',
        'pending_phonenumber',
        'phone_verification_otp_hash',
        'phone_verification_otp_expires_at',
        'gender',
        'birthdate',
        'birthplace',
        'address',
        'bloodtype',
        'job',
        'education_status',
        'life_status',
        'marital_status',
        'deaddate',
        'grave_location_url',
        'picture',
        'userid',
        'roleid',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'deaddate' => 'date',
        'email_verification_token_expires_at' => 'datetime',
        'phone_verification_otp_expires_at' => 'datetime',
    ];

    public function relationships()
    {
        return $this->hasMany(Relationship::class, 'memberid', 'memberid');
    }

    public function partners()
    {
        return $this->hasMany(Relationship::class, 'memberid', 'memberid')->where('relationtype', 'partner');
    }

    public function children()
    {
        return $this->hasMany(Relationship::class, 'memberid', 'memberid')->where('relationtype', 'child');
    }
}
