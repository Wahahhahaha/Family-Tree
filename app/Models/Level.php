<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $table = 'level';
    protected $primaryKey = 'levelid';
    public $timestamps = false;

    protected $fillable = ['levelname'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'levelid', 'levelid');
    }
}
