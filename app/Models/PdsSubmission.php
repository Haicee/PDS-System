<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdsSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'unit',
        'email',
        'type',
        'status',
        'submitted',
    ];

    protected $casts = [
        'submitted' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
