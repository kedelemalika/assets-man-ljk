<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRequestApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_request_id',
        'approval_order',
        'assigned_approver_id',
        'assigned_role',
        'approver_id',
        'approver_name',
        'approver_role',
        'status',
        'remarks',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function assignedApprover()
    {
        return $this->belongsTo(User::class, 'assigned_approver_id');
    }
}
