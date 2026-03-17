<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_request_id',
        'item_name',
        'spec',
        'qty',
        'estimated_price',
        'item_type',
        'asset_id',
        'consumable_id',
    ];

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function consumable()
    {
        return $this->belongsTo(Consumable::class);
    }
}