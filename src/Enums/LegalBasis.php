<?php

declare(strict_types=1);

namespace LumenSistemas\Lgpd\Enums;

use function LumenSistemas\Lgpd\trans_string;

/**
 * Enum LegalBasis.
 *
 * Defines the legal bases for personal data processing according to
 * Art. 7 of the LGPD (Lei Geral de Proteção de Dados).
 */
enum LegalBasis: string
{
    case CONSENT = 'consent';
    case LEGAL_OBLIGATION = 'legal_obligation';
    case PUBLIC_ADMINISTRATION = 'public_administration';
    case RESEARCH = 'research';
    case CONTRACT = 'contract';
    case LEGAL_PROCEEDINGS = 'legal_proceedings';
    case LIFE_PROTECTION = 'life_protection';
    case HEALTH = 'health';
    case LEGITIMATE_INTEREST = 'legitimate_interest';
    case CREDIT_PROTECTION = 'credit_protection';

    /**
     * Get the human-readable label for the legal basis.
     */
    public function label(): string
    {
        return trans_string('lgpd::enums.legal_basis.'.$this->value);
    }

    /**
     * Get the human-readable description for the legal basis.
     */
    public function description(): string
    {
        return trans_string('lgpd::enums.legal_basis.'.$this->value.'_description');
    }
}
