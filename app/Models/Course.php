<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $course_code
 * @property string $label
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['course_code', 'label'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The disciplines offered inside the course.
     */
    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class);
    }

    /**
     * The users (professors and students) linked to the course.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user');
    }
}
