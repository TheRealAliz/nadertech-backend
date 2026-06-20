<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageItem extends Model
{
    protected $fillable = [
        'page',
        'key',
        'value',
        'type',
        'order',
        'is_active',
    ];
}
