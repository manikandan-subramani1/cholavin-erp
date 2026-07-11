<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    public static function values(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }

    public static function setValue(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
    }
}
