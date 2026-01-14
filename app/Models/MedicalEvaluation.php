<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_id',
        'user_id',
        'patient_id',
        'evaluation_data',
        'notes',
    ];

    protected $casts = [
        'evaluation_data' => 'array', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
}
