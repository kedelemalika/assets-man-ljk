<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BastItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bast_id',
        'asset_id',
        'condition_notes',
        'remarks',
    ];

    public function bast()
    {
        return $this->belongsTo(Bast::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}