<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Contracts;

use LumenSistemas\Lgpd\Enums\DataSensitivity;

/**
 * Interface HasDataClassification.
 *
 * Implement this interface on any Eloquent model to declare the
 * data sensitivity level of each column that contains personal data.
 */
interface HasDataClassification
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
}
