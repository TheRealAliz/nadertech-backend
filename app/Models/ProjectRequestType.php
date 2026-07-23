<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRequestType extends Model
{
    protected $fillable = [
        'title',
        'description',
    ];

    public function projectRequests()
    {
        return $this->hasMany(ProjectRequest::class, 'project_request_type_id');
    }
}