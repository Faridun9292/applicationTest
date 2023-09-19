<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    const Active = 1;
    const Resolved = 2;

    protected $fillable = [
        'name',
        'email',
        'status',
        'message',
        'comment'
    ];
}
