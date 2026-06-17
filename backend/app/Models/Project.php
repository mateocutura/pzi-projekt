<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'mentor_id',
        'title',
        'description',
        'vision',
        'github_url',
        'status',
        'rejection_reason',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectSubmission::class);
    }


    public function getProgressPercentageAttribute(): int
    {
        $approved = $this->submissions()->where('status', 'approved')->count();

        return (int) ($approved / 10 * 100);
    }
}
