<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRequestType extends Model
{
    protected $fillable = [
        'title',
        'title_en',
        'description',
        'description_en',
    ];

    public function projectRequests()
    {
        return $this->hasMany(ProjectRequest::class, 'project_request_type_id');
    }
}
