<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Enums;

use function LumenSistemas\Lgpd\trans_string;

/**
 * Enum DataSubjectRight.
 *
 * Defines the rights of data subjects according to
 * Art. 18 of the LGPD (Lei Geral de Proteção de Dados).
 */
enum DataSubjectRight: string
{
    case ACCESS = 'access';
    case CORRECTION = 'correction';
    case ANONYMIZATION = 'anonymization';
    case PORTABILITY = 'portability';
    case DELETION = 'deletion';
    case SHARING_INFO = 'sharing_info';
    case CONSENT_INFO = 'consent_info';
    case CONSENT_REVOCATION = 'consent_revocation';
    case OPPOSITION = 'opposition';

    /**
     * Get the human-readable label for the data subject right.
     */
    public function label(): string
    {
        return trans_string('lgpd::enums.data_subject_right.'.$this->value);
    }

    /**
     * Get the human-readable description for the data subject right.
     */
    public function description(): string
    {
        return trans_string('lgpd::enums.data_subject_right.'.$this->value.'_description');
    }
}
