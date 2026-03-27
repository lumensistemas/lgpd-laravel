<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Enums\LegalBasis;
use Override;

/**
 * Class ProcessingActivity.
 *
 * Compliance registry of personal data processing activities as required
 * by Art. 37 of the LGPD. Describes the types of processing operations
 * the organization carries out, not individual occurrences.
 *
 * @property string $id
 * @property string $activity
 * @property LegalBasis $legal_basis
 * @property DataSensitivity $sensitivity
 * @property string $purpose
 * @property null|array<int, string> $data_categories
 * @property null|string $retention_period
 * @property CarbonImmutable $processed_at
 * @property null|CarbonImmutable $deleted_at
 * @property null|CarbonImmutable $created_at
 * @property null|CarbonImmutable $updated_at
 */
class ProcessingActivity extends Model
{
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'activity',
        'legal_basis',
        'sensitivity',
        'purpose',
        'data_categories',
        'retention_period',
        'processed_at',
    ];

    /**
     * Construct a new ProcessingActivity model instance.
     *
     * Initialize the model and set the table name from configuration.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = strval(Config::string('lgpd.tables.processing_activities', 'processing_activities'));
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'legal_basis' => LegalBasis::class,
            'sensitivity' => DataSensitivity::class,
            'data_categories' => 'array',
            'processed_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
