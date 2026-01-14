<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'patient_id',
        'brand_slug',
        'total_amount',
        'procedure_date',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(ProcedureItem::class);
    }

    public function medicalEvaluation()
    {
        return $this->hasOne(MedicalEvaluation::class);
    }
}

