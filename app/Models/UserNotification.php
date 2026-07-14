<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['data' => 'array', 'read_at' => 'datetime', 'sent_at' => 'datetime', 'failed_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
