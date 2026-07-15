<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = ['name', 'code', 'address', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['is_default', 'is_active', 'created_by']);
    }

    public function godowns()
    {
        return $this->hasMany(Godown::class);
    }

    public function linkedGodowns()
    {
        return $this->belongsToMany(Godown::class)->withTimestamps();
    }
}
