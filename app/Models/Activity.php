<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $process_id
 * @property string $discipline_id
 * @property string $professor_id
 * @property string $title
 * @property string $description
 * @property string|null $url_activity
 * @property string $activity_type
 * @property string|null $activity_text
 * @property string|null $url_answer
 * @property string|null $answer_type
 * @property string|null $answer_text
 * @property ActivityStatus $status
 * @property string|null $grade
 * @property string|null $feedback
 * @property Carbon $deadline
 * @property Carbon|null $published_at
 * @property Carbon|null $answered_at
 * @property Carbon|null $corrected_at
 * @property Carbon|null $concluded_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'process_id',
    'discipline_id',
    'professor_id',
    'title',
    'description',
    'url_activity',
    'activity_type',
    'activity_text',
    'url_answer',
    'answer_type',
    'answer_text',
    'status',
    'grade',
    'feedback',
    'deadline',
    'published_at',
    'answered_at',
    'corrected_at',
    'concluded_at',
])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The RED process that the activity belongs to.
     */
    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * The discipline of the activity.
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * The professor responsible for the activity.
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ActivityStatus::class,
            'grade' => 'decimal:2',
            'deadline' => 'date',
            'published_at' => 'datetime',
            'answered_at' => 'datetime',
            'corrected_at' => 'datetime',
            'concluded_at' => 'datetime',
        ];
    }
}
