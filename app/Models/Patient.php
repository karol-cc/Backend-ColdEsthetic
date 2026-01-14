<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referrer_name',
        'first_name',
        'last_name',
        'cellphone',
        'age',
        'weight',
        'height',
        'bmi',
        'medical_background',
        'biological_sex',
    ];

    // 🔗 Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function medicalEvaluations()
    {
        return $this->hasMany(MedicalEvaluation::class);
    }
}
