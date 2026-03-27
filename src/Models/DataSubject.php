<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use LumenSistemas\Encrypt\Casts\AsBlindIndex;
use Override;

/**
 * Class DataSubject.
 *
 * Stores the canonical identification of the data subject, which is the person
 * to whom the personal data refers. While the same person may be identified in
 * multiple models (e.g., as a customer, employee, etc.), the DataSubject model
 * serves as a centralized reference point for all personal data associated with
 * that individual.
 *
 * @property string $id
 * @property string $document_hash
 * @property null|CarbonImmutable $deleted_at
 * @property null|CarbonImmutable $created_at
 * @property null|CarbonImmutable $updated_at
 */
class DataSubject extends Model
{
    use HasUuids;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = ['document_hash'];

    /**
     * Construct a new DataSubject model instance.
     *
     * Initialize the model and set the table name from configuration.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Config::string('lgpd.tables.data_subjects', 'data_subjects');
    }

    /**
     * Get the consents associated with this data subject.
     *
     * @return HasMany<Consent, $this>
     */
    public function consents(): HasMany
    {
        /** @var class-string<Consent> $model */
        $model = Config::string('lgpd.models.consent', Consent::class);

        return $this->hasMany($model);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'document_hash' => AsBlindIndex::class,
            'deleted_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
