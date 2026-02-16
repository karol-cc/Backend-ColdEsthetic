<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeforeAfter extends Model
{
    protected $table = "before_afters";
    
    protected $fillable = [
        'title',
        'description',
        'before_image',
        'after_image',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
