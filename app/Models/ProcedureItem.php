<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureItem extends Model
{
    use HasFactory;

    // Atributos
    protected $fillable = [
        'procedure_id',
        'item_name',
        'price',
    ];

     /* Relaciones */
    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
}
