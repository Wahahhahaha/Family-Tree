<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'roleid';
    public $timestamps = false;

    protected $fillable = ['rolename'];

    public function employers(): HasMany
    {
        return $this->hasMany(Employer::class, 'roleid', 'roleid');
    }
}
