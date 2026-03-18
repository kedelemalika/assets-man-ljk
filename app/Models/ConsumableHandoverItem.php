<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableHandoverItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumable_handover_id',
        'consumable_id',
        'item_name',
        'qty',
        'remarks',
    ];

    public function handover()
    {
        return $this->belongsTo(ConsumableHandover::class, 'consumable_handover_id');
    }

    public function consumable()
    {
        return $this->belongsTo(Consumable::class);
    }
}
