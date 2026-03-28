<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Concerns;

/**
 * Trait Anonymizable.
 *
 * Provides anonymization capabilities for Eloquent models that
 * implement HoldsPersonalData. Uses the model's dataClassification()
 * to determine which fields to anonymize.
 *
 * This trait only mutates attributes in memory — it does NOT persist
 * the changes. The consuming app is responsible for authorization,
 * logging, and calling save() when appropriate.
 *
 * @phpstan-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @phpstan-require-implements \LumenSistemas\Lgpd\Contracts\HoldsPersonalData
 */
trait Anonymizable
{
    /**
     * Anonymize all classified personal data fields on this model.
     *
     * Replaces each classified column's value with a redacted placeholder
     * in memory. Does NOT persist — call save() explicitly after any
     * required authorization, logging, or validation.
     *
     * @return $this
     */
    public function anonymize(): static
    {
        $classification = $this->dataClassification();

        foreach (array_keys($classification) as $column) {
            $this->setAttribute($column, $this->anonymizedValue($column));
        }

        return $this;
    }

    /**
     * Check if this model has been anonymized.
     *
     * A model is considered anonymized when all classified columns
     * contain the redacted placeholder value.
     */
    public function isAnonymized(): bool
    {
        $classification = $this->dataClassification();

        if ($classification === []) {
            return false;
        }

        return array_all(array_keys($classification), fn ($column): bool => $this->getAttribute($column) === $this->anonymizedValue($column));
    }

    /**
     * Return masked values for display without modifying the model.
     *
     * Returns an array of column => masked value for classified columns.
     * Pass a list of columns to mask only a subset.
     *
     * @param null|list<string> $columns Subset of classified columns to mask. When null, all are masked.
     *
     * @return array<string, string>
     */
    public function masked(?array $columns = null): array
    {
        $classification = $this->dataClassification();

        if ($columns !== null) {
            $classification = array_intersect_key(
                $classification,
                array_flip($columns),
            );
        }

        $result = [];

        foreach (array_keys($classification) as $column) {
            $value = $this->getAttribute($column);
            $result[$column] = is_string($value) ? $this->maskedValue($column, $value) : '***';
        }

        return $result;
    }

    /**
     * Get the anonymized placeholder value for a given column.
     *
     * Override this method to customize the anonymization strategy
     * per column (e.g. hashing, random values, or partial masking).
     */
    protected function anonymizedValue(string $column): string
    {
        return '**REDACTED**';
    }

    /**
     * Get the masked display value for a given column.
     *
     * Override this method to customize the masking strategy per column.
     * The default keeps the first and last characters visible.
     */
    protected function maskedValue(string $column, string $value): string
    {
        $length = mb_strlen($value);

        if ($length <= 2) {
            return str_repeat('*', $length);
        }

        return mb_substr($value, 0, 1).str_repeat('*', $length - 2).mb_substr($value, -1);
    }
}
