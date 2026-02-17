<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleCalendarSetting extends Model
{
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'google_email',
        'calendar_id',
        'sync_enabled'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'sync_enabled' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
