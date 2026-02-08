<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_evaluation_id',
        'brand_slug',
        'procedure_date',
        'total_amount',
        'notes',
    ];

    /* Relaciones */
    
    public function medicalEvaluation()
    {
        return $this->belongsTo(MedicalEvaluation::class);
    }

    public function items()
    {
        return $this->hasMany(ProcedureItem::class);
    }
}
