<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LumenSistemas\Lgpd\Enums\DataSensitivity;
use LumenSistemas\Lgpd\Models\DataSubject;

/**
 * Interface HoldsPersonalData.
 *
 * Implement this interface on any Eloquent model that holds personal data.
 * It declares the data sensitivity level of each column and provides
 * a relationship to the DataSubject.
 */
interface HoldsPersonalData
{
    /**
     * Return a map of column names to their data sensitivity level.
     *
     * Only columns that contain personal or sensitive data need to be listed.
     * Columns not listed are assumed to be non-personal (PUBLIC/INTERNAL).
     *
     * Example:
     *   return [
     *       'name'    => DataSensitivity::PERSONAL,
     *       'email'   => DataSensitivity::PERSONAL,
     *       'cpf'     => DataSensitivity::SENSITIVE,
     *       'address' => DataSensitivity::PERSONAL,
     *   ];
     *
     * @return array<string, DataSensitivity>
     */
    public function dataClassification(): array;

    /**
     * Get the data subject associated with this model.
     *
     * @return BelongsTo<DataSubject, Model>
     */
    public function dataSubject(): BelongsTo;
}
