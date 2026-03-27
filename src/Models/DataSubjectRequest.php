<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use LumenSistemas\Lgpd\Enums\DataSubjectRight;
use LumenSistemas\Lgpd\Enums\RequestStatus;
use Override;

/**
 * Class DataSubjectRequest.
 *
 * Tracks requests made by data subjects exercising their rights
 * under Art. 18 of the LGPD. The organization must respond to
 * these requests within the legally defined timeframe.
 *
 * @property string $id
 * @property string $data_subject_id
 * @property DataSubjectRight $right
 * @property RequestStatus $status
 * @property CarbonImmutable $requested_at
 * @property null|CarbonImmutable $responded_at
 * @property null|string $response_notes
 * @property null|CarbonImmutable $created_at
 * @property null|CarbonImmutable $updated_at
 * @property null|CarbonImmutable $deleted_at
 */
class DataSubjectRequest extends Model
{
    use HasUuids;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'data_subject_id',
        'right',
        'status',
        'requested_at',
        'responded_at',
        'response_notes',
    ];

    /**
     * Construct a new DataSubjectRequest model instance.
     *
     * Initialize the model and set the table name from configuration.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Config::string('lgpd.tables.data_subject_requests', 'data_subject_requests');
    }

    /**
     * Get the data subject associated with this request.
     *
     * @return BelongsTo<DataSubject, $this>
     */
    public function dataSubject(): BelongsTo
    {
        /** @var class-string<DataSubject> $model */
        $model = Config::string('lgpd.models.data_subject', DataSubject::class);

        return $this->belongsTo($model);
    }

    /**
     * Scope to requests with pending status.
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', RequestStatus::PENDING);
    }

    /**
     * Scope to requests with in_progress status.
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function inProgress(Builder $query): void
    {
        $query->where('status', RequestStatus::IN_PROGRESS);
    }

    /**
     * Scope to requests with completed status.
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->where('status', RequestStatus::COMPLETED);
    }

    /**
     * Scope to requests with denied status.
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function denied(Builder $query): void
    {
        $query->where('status', RequestStatus::DENIED);
    }

    /**
     * Scope to requests that have not been responded to within the given days.
     *
     * The LGPD requires a response within 15 days (Art. 18, §3).
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function overdue(Builder $query, int $days = 15): void
    {
        $query->where('requested_at', '<', now()->subDays($days))
            ->whereIn('status', [RequestStatus::PENDING, RequestStatus::IN_PROGRESS]);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'data_subject_id' => 'string',
            'right' => DataSubjectRight::class,
            'status' => RequestStatus::class,
            'requested_at' => 'immutable_datetime',
            'responded_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
