<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Enums;

use function LumenSistemas\Lgpd\trans_string;

/**
 * Enum RequestStatus.
 *
 * Defines the lifecycle status of a Data Subject Request (DSR)
 * as the organization processes the request.
 */
enum RequestStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case DENIED = 'denied';

    /**
     * Get the human-readable label for the request status.
     */
    public function label(): string
    {
        return trans_string('lgpd::enums.request_status.'.$this->value);
    }

    /**
     * Get the human-readable description for the request status.
     */
    public function description(): string
    {
        return trans_string(sprintf('lgpd::enums.request_status.%s_description', $this->value));
    }
}
