<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bast extends Model
{
    use HasFactory;

    protected $fillable = [
        'bast_number',
        'bast_date',
        'handover_by',
        'handover_position',
        'received_by',
        'received_position',
        'handover_location',
        'receiver_location',
        'handover_city',
        'receiver_city',
        'department',
        'notes',
        'status',
        'finalized_at',
        'created_by',
    ];

    protected $casts = [
        'bast_date' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(BastItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}