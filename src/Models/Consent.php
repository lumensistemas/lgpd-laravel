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
use LumenSistemas\Lgpd\Enums\LegalBasis;
use LumenSistemas\Lgpd\Events\ConsentGranted;
use LumenSistemas\Lgpd\Events\ConsentRevoked;
use Override;

/**
 * Class Consent.
 *
 * Tracks consent grants and revocations for data subjects as required
 * by Art. 8 of the LGPD. Consent must be free, informed, and unambiguous,
 * and the data subject may revoke it at any time.
 *
 * @property string $id
 * @property string $data_subject_id
 * @property string $purpose
 * @property LegalBasis $legal_basis
 * @property CarbonImmutable $granted_at
 * @property null|CarbonImmutable $revoked_at
 * @property null|CarbonImmutable $expires_at
 * @property null|string $ip_address
 * @property null|string $user_agent
 * @property null|array<string, mixed> $metadata
 * @property null|CarbonImmutable $created_at
 * @property null|CarbonImmutable $updated_at
 * @property null|CarbonImmutable $deleted_at
 */
class Consent extends Model
{
    use HasUuids;
    use SoftDeletes;

    /** @var array<string, class-string> */
    protected $dispatchesEvents = [
        'created' => ConsentGranted::class,
    ];

    /** @var list<string> */
    protected $fillable = [
        'data_subject_id',
        'purpose',
        'legal_basis',
        'granted_at',
        'revoked_at',
        'expires_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * Construct a new Consent model instance.
     *
     * Initialize the model and set the table name from configuration.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Config::string('lgpd.tables.consents', 'consents');
    }

    /**
     * Get the data subject associated with this consent.
     *
     * @return BelongsTo<DataSubject, $this>
     */
    public function dataSubject(): BelongsTo
    {
        /** @var class-string<DataSubject> $model */
        $model = Config::string('lgpd.models.data_subject', DataSubject::class);

        return $this->belongsTo($model);
    }

    #[Override]
    protected static function booted(): void
    {
        static::updated(function (self $consent): void {
            if ($consent->revoked_at !== null && $consent->wasChanged('revoked_at')) {
                ConsentRevoked::dispatch($consent);
            }
        });
    }

    /**
     * Scope to consents that are currently active (granted, not revoked, not expired).
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('revoked_at')
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at');
                $q->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to consents that have been revoked.
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function revoked(Builder $query): void
    {
        $query->whereNotNull('revoked_at');
    }

    /**
     * Scope to consents that have expired.
     *
     * @param Builder<self> $query
     */
    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
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
            'legal_basis' => LegalBasis::class,
            'granted_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
