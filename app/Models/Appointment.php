<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'patient_id',
        'referrer_name',
        'appointment_datetime',
        'duration_minutes',
        'planned_procedures',
        'notes',
        'status',
        'google_calendar_event_id',
        'procedure_id'
    ];

    protected $casts = [
        'appointment_datetime' => 'datetime',
        'planned_procedures' => 'array',
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
