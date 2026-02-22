<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalEvaluation extends Model
{
    use HasFactory;

    // Constantes de estados
    const STATUS_EN_ESPERA = 'EN_ESPERA';
    const STATUS_CONFIRMADO = 'CONFIRMADO';
    const STATUS_CANCELADO = 'CANCELADO';

    // Atributos
    protected $fillable = [
        'user_id',
        'patient_id',
        'medical_background',
        'weight',
        'height',
        'bmi',
        'bmi_status',
        'status',
        'confirmed_at',
        'confirmed_by_user_id',
        'canceled_at',
        'canceled_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    // Helpers de estado
    public function isEnEspera()
    {
        return $this->status === self::STATUS_EN_ESPERA;
    }

    public function isConfirmado()
    {
        return $this->status === self::STATUS_CONFIRMADO;
    }

    public function isCancelado()
    {
        return $this->status === self::STATUS_CANCELADO;
    }

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

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'canceled_by_user_id');
    }
}
