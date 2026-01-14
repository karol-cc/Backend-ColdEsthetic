<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_id',
        'item_name',
        'price',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
}
