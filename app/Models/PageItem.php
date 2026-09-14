<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageItem extends Model
{
    protected $fillable = [
        'page',
        'key',
        'value',
        'value_en',
        'type',
        'order',
        'is_active',
    ];
}
