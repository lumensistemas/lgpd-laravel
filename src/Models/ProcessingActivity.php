<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Enums\LegalBasis;
use Override;

/**
 * Class ProcessingActivity.
 *
 * Records of personal data processing activities as required by Art. 37
 * of the LGPD. Controllers and processors must maintain a record of the
 * processing operations they carry out.
 *
 * @property string $id
 * @property null|string $data_subject_id
 * @property string $activity
 * @property LegalBasis $legal_basis
 * @property DataSensitivity $sensitivity
 * @property string $purpose
 * @property null|array<int, string> $data_categories
 * @property null|string $retention_period
 * @property CarbonImmutable $processed_at
 * @property null|CarbonImmutable $created_at
 * @property null|CarbonImmutable $updated_at
 */
class ProcessingActivity extends Model
{
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'data_subject_id',
        'activity',
        'legal_basis',
        'sensitivity',
        'purpose',
        'data_categories',
        'retention_period',
        'processed_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = strval(Config::string('lgpd.tables.processing_activities', 'processing_activities'));
    }

    /**
     * @return BelongsTo<DataSubject, $this>
     */
    public function dataSubject(): BelongsTo
    {
        /** @var class-string<DataSubject> $model */
        $model = Config::string('lgpd.models.data_subject', DataSubject::class);

        return $this->belongsTo($model);
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
            'sensitivity' => DataSensitivity::class,
            'data_categories' => 'array',
            'processed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
