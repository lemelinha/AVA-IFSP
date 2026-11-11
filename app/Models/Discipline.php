<?php

namespace App\Models;

use Database\Factories\DisciplineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Disciplina ofertada dentro de um curso.
 *
 * @property string $id
 * @property string $course_id
 * @property string $class_code
 * @property string $label
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['course_id', 'class_code', 'label'])]
class Discipline extends Model
{
    /** @use HasFactory<DisciplineFactory> */
    use HasFactory, HasUuids;

    protected $table = 'classes';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The course that the discipline belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The professors responsible for the discipline.
     */
    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'professor_class', 'class_id', 'professor_id');
    }

    /**
     * The RED activities of the discipline.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
