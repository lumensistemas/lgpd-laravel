<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Events;

use Illuminate\Foundation\Events\Dispatchable;
use LumenSistemas\Lgpd\Models\DataSubjectRequest;

class DataSubjectRequestCreated
{
    use Dispatchable;

    public function __construct(
        public readonly DataSubjectRequest $request,
    ) {}
}
