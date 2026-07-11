<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Godown extends Model
{
    protected $fillable = ['shop_id', 'name', 'code', 'address', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
