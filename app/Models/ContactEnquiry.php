<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactEnquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'service',
        'message',
        'status',
        'reason',
        'contacted_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];
}
