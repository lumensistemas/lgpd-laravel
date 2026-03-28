<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Events;

use Illuminate\Foundation\Events\Dispatchable;
use LumenSistemas\Lgpd\Models\Consent;

class ConsentGranted
{
    use Dispatchable;

    public function __construct(
        public readonly Consent $consent,
    ) {}
}
