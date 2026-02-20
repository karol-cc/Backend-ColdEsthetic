<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // Atributos
    protected $fillable = [
        'user_id',
        'referrer_name',
        'first_name',
        'last_name',
        'cellphone',
        'age',
        'biological_sex',
        'cedula',
    ];

    /* Relaciones */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalEvaluations()
    {
        return $this->hasMany(MedicalEvaluation::class);
    }
}
