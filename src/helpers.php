<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd;

use Illuminate\Support\Facades\Lang;

/**
 * Type-safe translation helper that always returns a string.
 *
 * @param array<string, string> $replace
 */
function trans_string(string $key, array $replace = [], ?string $locale = null): string
{
    $translation = Lang::get($key, $replace, $locale);

    return is_string($translation) ? $translation : $key;
}
