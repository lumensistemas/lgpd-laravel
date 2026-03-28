<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use LumenSistemas\Lgpd\Models\DataSubject;

/**
 * Trait HasPersonalData.
 *
 * Use this trait on any Eloquent model that contains personal data
 * (e.g., User, Customer, Employee) to link it to a DataSubject.
 *
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @phpstan-require-implements \LumenSistemas\Lgpd\Contracts\HoldsPersonalData
 */
trait HasPersonalData
{
    /**
     * @return BelongsTo<DataSubject, $this>
     */
    public function dataSubject(): BelongsTo
    {
        /** @var class-string<DataSubject> $model */
        $model = Config::string('lgpd.models.data_subject', DataSubject::class);

        return $this->belongsTo($model);
    }
}
