<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalEvaluation extends Model
{
    use HasFactory;

    // Atributos
    protected $fillable = [
        'user_id',
        'patient_id',
        'medical_background',
        'weight',
        'height',
        'bmi',
        'bmi_status',
    ];

    /* Relaciones */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }
}
