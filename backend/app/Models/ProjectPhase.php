<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectPhase extends Model
{
    protected $fillable = [
        'step_number',
        'name',
        'description',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectSubmission::class, 'phase_id');
    }
}
