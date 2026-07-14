<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];
    protected $touches = ['contact'];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
