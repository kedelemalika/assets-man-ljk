<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'handover_date',
        'item_request_id',
        'handover_by',
        'received_by',
        'department',
        'notes',
        'status',
        'finalized_at',
        'created_by',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ConsumableHandoverItem::class);
    }

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class, 'item_request_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
