<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employer extends Model
{
    protected $table = 'employer';
    protected $primaryKey = 'employerid';
    public $timestamps = false;

    protected $fillable = [
        'userid', 'roleid', 'name', 'email', 'phonenumber'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'roleid', 'roleid');
    }
}
