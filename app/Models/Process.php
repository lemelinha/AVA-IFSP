<?php

namespace App\Models;

use App\Enums\ProcessStatus;
use Database\Factories\ProcessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $public_id
 * @property string $user_id
 * @property string $coordinator_id
 * @property ProcessStatus $status
 * @property Carbon|null $started_at
 * @property Carbon|null $concluded_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['public_id', 'user_id', 'coordinator_id', 'status', 'started_at', 'concluded_at'])]
class Process extends Model
{
    /** @use HasFactory<ProcessFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The student in Regime de Ensino a Distância.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The coordinator who created the RED process.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * The activities of the RED process.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProcessStatus::class,
            'started_at' => 'datetime',
            'concluded_at' => 'datetime',
        ];
    }
}
