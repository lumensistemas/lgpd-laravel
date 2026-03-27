<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Enums;

use function LumenSistemas\Lgpd\trans_string;

/**
 * Enum DataSensitivity.
 *
 * Defines the levels of data sensitivity for categorizing personal data
 * according to the LGPD (Lei Geral de Proteção de Dados) in Brazil.
 */
enum DataSensitivity: string
{
    case PUBLIC = 'public';
    case INTERNAL = 'internal';
    case PERSONAL = 'personal';
    case SENSITIVE = 'sensitive';

    /**
     * Get the human-readable label for the data sensitivity level.
     */
    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => trans_string('lgpd::enums.data_sensitivity.public'),
            self::INTERNAL => trans_string('lgpd::enums.data_sensitivity.internal'),
            self::PERSONAL => trans_string('lgpd::enums.data_sensitivity.personal'),
            self::SENSITIVE => trans_string('lgpd::enums.data_sensitivity.sensitive'),
        };
    }

    /**
     * Get the human-readable description for the data sensitivity level.
     */
    public function description(): string
    {
        return match ($this) {
            self::PUBLIC => trans_string('lgpd::enums.data_sensitivity.public_description'),
            self::INTERNAL => trans_string('lgpd::enums.data_sensitivity.internal_description'),
            self::PERSONAL => trans_string('lgpd::enums.data_sensitivity.personal_description'),
            self::SENSITIVE => trans_string('lgpd::enums.data_sensitivity.sensitive_description'),
        };
    }
}
