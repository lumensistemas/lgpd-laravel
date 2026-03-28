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
     * Return the highest sensitivity level from a list of values.
     *
     * @param non-empty-array<DataSensitivity> $levels
     */
    public static function highest(array $levels): self
    {
        $cases = self::cases();
        $order = array_flip(array_map(fn (self $case): string => $case->value, $cases));
        $max = 0;

        foreach ($levels as $level) {
            $index = $order[$level->value] ?? 0;

            if ($index > $max) {
                $max = $index;
            }
        }

        return $cases[$max];
    }

    /**
     * Get the human-readable label for the data sensitivity level.
     */
    public function label(): string
    {
        return trans_string('lgpd::enums.data_sensitivity.'.$this->value);
    }

    /**
     * Get the human-readable description for the data sensitivity level.
     */
    public function description(): string
    {
        return trans_string('lgpd::enums.data_sensitivity.'.$this->value.'_description');
    }
}
